<?php

namespace api\modules\Saude;

use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class SaudeController
{
    private SaudeService $saudeService;

    public function __construct(SaudeService $saudeService)
    {
        $this->saudeService = $saudeService;
    }

    public function getEspecialidades(Request $request, Response $response, array $args = []): Response
    {
        try {
            $especialidades = $this->saudeService->listarEspecialidades();

            $response->getBody()->write(json_encode($especialidades, JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function salvarModeloParecer(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody() ?? [];
            $descricao = trim((string) ($body['descricao'] ?? ''));
            $uploadedFiles = $request->getUploadedFiles();
            $arquivo = $uploadedFiles['arquivo'] ?? null;

            if ($descricao === '') {
                throw new \InvalidArgumentException('Descrição do modelo de parecer é obrigatória', 400);
            }

            if (!$arquivo instanceof UploadedFileInterface) {
                throw new \InvalidArgumentException('Arquivo do modelo de parecer é obrigatório', 400);
            }

            $modeloId = $this->saudeService->salvarModeloParecer($descricao, $arquivo);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Modelo de parecer salvo com sucesso',
                'modelo' => [
                    'id' => $modeloId,
                ],
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}