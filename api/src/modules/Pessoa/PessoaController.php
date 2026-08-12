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
            $endereco = null;

            if (isset($body['endereco'])) {
                if (!is_array($body['endereco'])) {
                    $response->getBody()->write(json_encode([
                        'error' => 'O campo endereco deve ser um objeto JSON'
                    ]));
                    return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                }

                $endereco = $body['endereco'];
            } else {
                $camposEndereco = ['cep', 'estado', 'cidade', 'bairro', 'logradouro', 'numero', 'numero_endereco', 'complemento', 'ibge'];
                foreach ($camposEndereco as $campo) {
                    if (array_key_exists($campo, $body)) {
                        $endereco ??= [];
                        $endereco[$campo] = $body[$campo];
                    }
                }
            }

            // Atualizar a pessoa
            $pessoaAtualizada = $this->pessoaService->atualizarPessoa(
                (int)$userId,
                $nome,
                $sobrenome,
                $dataNascimento,
                $sexo,
                $telefone,
                $email,
                $cpf,
                $endereco
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

    public function updateProfilePhoto(Request $request, Response $response): Response
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

            // Validar que o usuário está editando seu próprio perfil
            $body = $request->getParsedBody();
            if (isset($body['id']) && (int)$body['id'] !== (int)$userId) {
                $response->getBody()->write(json_encode([
                    'error' => 'Você não tem permissão para editar este perfil'
                ]));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            // Obter os arquivos enviados
            $uploadedFiles = $request->getUploadedFiles();

            if (!isset($uploadedFiles['photo'])) {
                $response->getBody()->write(json_encode([
                    'error' => 'Nenhuma foto enviada'
                ]));

                return $response
                    ->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            $photo = $uploadedFiles['photo'];

            // Validar se o arquivo foi enviado corretamente
            if ($photo->getError() !== UPLOAD_ERR_OK) {
                $response->getBody()->write(json_encode([
                    'error' => 'Erro ao enviar a foto'
                ]));

                return $response
                    ->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // A partir daqui, o arquivo está disponível em $photo

            // Atualizar a foto do perfil
            $this->pessoaService->atualizarFotoPerfil((int)$userId, $photo);

            $response->getBody()->write(json_encode([
                'message' => 'Foto de perfil atualizada com sucesso'
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

    public function getProfilePhoto(Request $request, Response $response, array $args): Response
    {
        try {
            $idPessoa = (int)$args['id'];

            //verificar se o usuário autenticado tem permissão para acessar a foto do perfil
            $userId = $request->getAttribute('user_id');
            if ($userId !== $idPessoa) {
                $response->getBody()->write(json_encode([
                    'error' => 'Você não tem permissão para acessar esta foto de perfil'
                ]));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            // Obter a foto do perfil da pessoa
            $foto = $this->pessoaService->getProfilePhoto($idPessoa);

            if (!$foto) {
                $response->getBody()->write(json_encode([
                    'error' => 'Foto de perfil não encontrada'
                ]));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write($foto['conteudo']);

            return $response
                ->withHeader('Content-Type', $foto['mime'])
                ->withHeader('Content-Length', (string) strlen($foto['conteudo']))
                ->withHeader('Content-Disposition', 'inline')
                ->withStatus(200);
        } catch (\Exception $e) {
            $statusCode = (int)$e->getCode() ?: 500;
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));
            return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
        }
    }
}
