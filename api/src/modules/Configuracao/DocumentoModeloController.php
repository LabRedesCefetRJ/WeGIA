<?php

namespace api\modules\Configuracao;

use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class DocumentoModeloController
{
    private DocumentoModeloService $documentoModeloService;

    public function __construct(DocumentoModeloService $documentoModeloService)
    {
        $this->documentoModeloService = $documentoModeloService;
    }

    public function getModelos(Request $request, Response $response, array $args = []): Response
    {
        try {
            $modelos = $this->documentoModeloService->listarModelos();

            $response->getBody()->write(json_encode($modelos, JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            $response->getBody()->write(json_encode([
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function getArquivoModelo(Request $request, Response $response, array $args = []): Response
    {
        try {
            $id = filter_var($args['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id === null || $id < 1) {
                throw new \InvalidArgumentException('ID do modelo de documento inválido.', 400);
            }

            $arquivo = $this->documentoModeloService->obterArquivoModelo($id);
            $nomeArquivo = sprintf('modelo-%d.%s', $id, $arquivo['extensao']);

            $response->getBody()->write($arquivo['conteudo']);

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', $arquivo['mime_type'])
                ->withHeader('Content-Length', (string) strlen($arquivo['conteudo']))
                ->withHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"');
        } catch (\Throwable $e) {
            $statusCode = (int) $e->getCode();
            if ($statusCode < 400 || $statusCode > 599) {
                $statusCode = 500;
            }

            $response->getBody()->write(json_encode([
                'error' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus($statusCode)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function salvarModelo(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody() ?? [];
            $descricao = trim((string) ($body['descricao'] ?? ''));
            $global = filter_var($body['global'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $global = $global === true;

            $uploadedFiles = $request->getUploadedFiles();
            $arquivo = $uploadedFiles['arquivo'] ?? null;

            if ($descricao === '') {
                throw new \InvalidArgumentException('Descrição do modelo de documento é obrigatória', 400);
            }

            if (!$arquivo instanceof UploadedFileInterface) {
                throw new \InvalidArgumentException('Arquivo do modelo de documento é obrigatório', 400);
            }

            $idPessoa = (int) ($request->getAttribute('user_id') ?? 0);
            if ($idPessoa <= 0) {
                throw new \InvalidArgumentException('Usuário autenticado não identificado.', 401);
            }

            $modeloId = $this->documentoModeloService->salvarModelo($descricao, $arquivo, $idPessoa, $global);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Modelo de documento salvo com sucesso',
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
