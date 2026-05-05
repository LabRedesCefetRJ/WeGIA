<?php

namespace api\modules\Socio;

use api\contracts\services\PessoaServiceInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SocioController
{
    private SocioService $socioService;
    private PessoaServiceInterface $pessoaService;

    public function __construct(SocioService $socioService, PessoaServiceInterface $pessoaService)
    {
        $this->socioService = $socioService;
        $this->pessoaService = $pessoaService;
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
                    $data['cpf']
                );
            }

            $socio = $this->socioService->criarSocio(
                $pessoa,
                $data['email'],
                new \DateTime($data['inicioContribuicao']),
                $data['valorMensalidade'] ?? 10.0,
                $data['status'] ?? 1,
                $data['autoStatusContribuicao'] ?? true,
                $data['idSocioTipo'] ?? 0
            );

            //futuramente poderia retornar um token de autenticação para o sócio recém-criado, mas por enquanto vamos apenas retornar os dados do sócio criado

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
