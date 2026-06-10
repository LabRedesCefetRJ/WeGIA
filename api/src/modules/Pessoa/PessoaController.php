<?php

namespace api\modules\Pessoa;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use DateTime;

class PessoaController
{
    private PessoaService $pessoaService;

    public function __construct(PessoaService $pessoaService)
    {
        $this->pessoaService = $pessoaService;
    }

    // Métodos para lidar com as requisições relacionadas a Pessoa

    public function updateProfile(Request $request, Response $response): Response
    {
        try {
            // Obter o ID do usuário autenticado do token
            $userId = $request->getAttribute('user_id');

            if (!$userId) {
                $response->getBody()->write(json_encode([
                    'error' => 'Usuário não autenticado'
                ]));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Obter dados do body
            $body = $request->getParsedBody();

            // Validar que o usuário está editando seu próprio perfil
            if (isset($body['id']) && (int)$body['id'] !== (int)$userId) {
                $response->getBody()->write(json_encode([
                    'error' => 'Você não tem permissão para editar este perfil'
                ]));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            // Extrair e validar campos obrigatórios
            $nome = $body['nome'] ?? null;
            $sobrenome = $body['sobrenome'] ?? null;
            $cpf = $body['cpf'] ?? null;

            if (!$nome || !$sobrenome || !$cpf) {
                $response->getBody()->write(json_encode([
                    'error' => 'Campos obrigatórios faltando: nome, sobrenome, cpf'
                ]));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Extrair campos opcionais
            $dataNascimento = null;
            if (isset($body['data_nascimento']) && !empty($body['data_nascimento'])) {
                try {
                    $dataNascimento = new DateTime($body['data_nascimento']);
                } catch (\Exception $e) {
                    $response->getBody()->write(json_encode([
                        'error' => 'Formato de data inválido. Use: YYYY-MM-DD'
                    ]));
                    return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                }
            }

            $sexo = $body['sexo'] ?? null;
            $telefone = $body['telefone'] ?? null;
            $email = $body['email'] ?? null;

            // Atualizar a pessoa
            $pessoaAtualizada = $this->pessoaService->atualizarPessoa(
                (int)$userId,
                $nome,
                $sobrenome,
                $dataNascimento,
                $sexo,
                $telefone,
                $email,
                $cpf
            );

            $response->getBody()->write(json_encode([
                'message' => 'Perfil atualizado com sucesso',
                'data' => $pessoaAtualizada
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $statusCode = (int)$e->getCode() ?: 500;
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
        }
    }
}