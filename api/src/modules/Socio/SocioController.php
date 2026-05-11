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

    public function __construct(SocioService $socioService, PessoaServiceInterface $pessoaService, AuthService $authService)
    {
        $this->socioService = $socioService;
        $this->pessoaService = $pessoaService;
        $this->authService = $authService;
    }

    public function registerSocio(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();

            //verificar se existe uma pessoa com o CPF fornecido, se não existir, criar uma nova pessoa, caso contrário, usar a pessoa existente para criar o sócio
            $pessoa = $this->pessoaService->obterPessoaPorCpf($data['cpf']);

            if(!$pessoa) {
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

            $socio = $this->socioService->criarSocio(
                $pessoa,
                new \DateTime($data['inicioContribuicao']),
                $data['valorMensalidade'] ?? 10.0,
                $data['status'] ?? 1,
                $data['autoStatusContribuicao'] ?? true,
                $data['idSocioTipo'] ?? 0
            );

        
            //Antes de atribuir a senha, é necessário validar o código enviado por e-mail, para evitar o roubo de contas existentes.


            // Atribuir senha para a pessoa criada/existente
            if (isset($data['senha']) && !empty($data['senha'])) {
                $this->authService->assignPasswordToPerson(
                    $pessoa->getId(),
                    $data['senha']
                );
            }

            //futuramente poderia retornar um token de autenticação para o sócio recém-criado

            $response->getBody()->write(json_encode($socio));

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
}
