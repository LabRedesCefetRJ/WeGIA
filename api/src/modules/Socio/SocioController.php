<?php

namespace api\modules\Socio;

use api\contracts\services\PessoaServiceInterface;
use api\modules\Auth\AuthService;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SocioController
{
    private SocioService $socioService;
    private PessoaServiceInterface $pessoaService;
    private AuthService $authService;
    private EmailVerificationService $emailVerificationService;

    public function __construct(SocioService $socioService, PessoaServiceInterface $pessoaService, AuthService $authService, EmailVerificationService $emailVerificationService)
    {
        $this->socioService = $socioService;
        $this->pessoaService = $pessoaService;
        $this->authService = $authService;
        $this->emailVerificationService = $emailVerificationService;
    }

    public function registerSocio(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();

            //verificar se existe uma pessoa com o CPF fornecido, se não existir, criar uma nova pessoa, caso contrário, usar a pessoa existente para criar o sócio
            $pessoa = $this->pessoaService->obterPessoaPorCpf($data['cpf']);

            if (!$pessoa) {
                $pessoa = $this->pessoaService->criarPessoa(
                    $data['nome'],
                    $data['sobrenome'],
                    isset($data['dataNascimento']) ? new \DateTime($data['dataNascimento']) : null,
                    $data['sexo'] ?? null,
                    $data['telefone'] ?? null,
                    $data['email'] ?? null,
                    $data['cpf']
                );
            }

            //Interromper se pessoa não possuir email para envio do código de verificação
            if (!$pessoa->getEmail()) {
                $response->getBody()->write(json_encode([
                    'error' => 'Pessoa deve possuir um e-mail para registro de sócio.'
                ]));

                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            $socio = $this->socioService->criarSocio(
                $pessoa,
                new \DateTime($data['inicioContribuicao']),
                $data['valorMensalidade'] ?? 10.0,
                $data['status'] ?? 1,
                $data['autoStatusContribuicao'] ?? true,
                $data['idSocioTipo'] ?? 0
            );

            // Send verification code via email
            $emailToVerify = $data['email'] ?? $pessoa->getEmail();
            $verificationResult = null;

            if (!empty($emailToVerify)) {
                $verificationResult = $this->emailVerificationService->generateAndSendCode(
                    $socio->getId(),
                    $emailToVerify
                );
            }

            $responseData = [
                'socio' => $socio,
                'email_verification' => $verificationResult ?? [
                    'success' => false,
                    'message' => 'E-mail não fornecido. Código de verificação não foi enviado.'
                ]
            ];

            $response->getBody()->write(json_encode($responseData));

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage() . ' | ' . $e->getCode()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Verify verification code
     * POST /socios/verify-code
     * 
     * Body JSON:
     * {
     *   "id_socio": 1,
     *   "code": "123456"
     * }
     */
    public function verifyCode(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();

            // Validate required data
            if (empty($data['id_socio']) || empty($data['code'])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'CPF e código são obrigatórios'
                ]));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // Verify code
            $result = $this->emailVerificationService->verifyCode(
                (int)$data['id_socio'],
                $data['code']
            );

            $statusCode = $result['success'] ? 200 : 400;

            $response->getBody()->write(json_encode($result));

            return $response->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Alter password for a socio using a verification code
     * POST /socios/alter-password
     * 
     * Body JSON:
     * {
     *   "id_socio": 1,
     *   "id_pessoa": 1,
     *   "senha": "novasenha123",
     *   "confirmacao_senha": "novasenha123",
     *   "codigo_verificacao": "123456"
     * }
     */
    public function alterPassword(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();

            // Validate required data
            if (empty($data['id_socio']) || empty($data['senha']) || empty($data['confirmacao_senha']) || 
                empty($data['codigo_verificacao'])) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'id_socio, senha, confirmacao_senha e codigo_verificacao são obrigatórios'
                ]));
                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // Call service to alter password
            $result = $this->socioService->alterPassword(
                (int)$data['id_socio'],
                $data['senha'],
                $data['confirmacao_senha'],
                $data['codigo_verificacao']
            );

            $statusCode = $result['success'] ? 200 : 400;

            $response->getBody()->write(json_encode($result));

            return $response->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function getSocioByCpf(Request $request, Response $response, array $args)
    {
        try {
            $resultado = $this->buscarSocioPorCpf($args['cpf'] ?? '');

            if (!$resultado['socio']) {
                $response->getBody()->write(json_encode([
                    'message' => $resultado['message']
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode($resultado['socio']));

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage() . ' | ' . $e->getCode()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function checkSocioExistsByCpf(Request $request, Response $response, array $args)
    {
        try {
            $resultado = $this->buscarSocioPorCpf($args['cpf'] ?? '');

            if (!$resultado['socio']) {
                $response->getBody()->write(json_encode([
                    'exists' => false,
                    'hasEmail' => $resultado['pessoa'] ? !empty($resultado['pessoa']->getEmail()) : false,
                    'message' => $resultado['message']
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $possuiEmail = !empty($resultado['pessoa']->getEmail());

            $response->getBody()->write(json_encode([
                'exists' => true,
                'hasEmail' => $possuiEmail
            ]));

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage() . ' | ' . $e->getCode()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    private function buscarSocioPorCpf(string $cpf): array
    {
        $cpf = $this->normalizarCpf($cpf);
        $pessoa = $this->pessoaService->obterPessoaPorCpf($cpf);

        if (!$pessoa) {
            return [
                'pessoa' => null,
                'socio' => null,
                'message' => 'Pessoa não localizada.'
            ];
        }

        $socio = $this->socioService->obterSocioPorPessoaId($pessoa->getId(), $pessoa);

        if (!$socio) {
            return [
                'pessoa' => $pessoa,
                'socio' => null,
                'message' => 'Sócio não localizado.'
            ];
        }

        return [
            'pessoa' => $pessoa,
            'socio' => $socio,
            'message' => null
        ];
    }

    private function normalizarCpf(string $cpf): string
    {
        return str_starts_with($cpf, 'cpf=') ? substr($cpf, 4) : $cpf;
    }
}
