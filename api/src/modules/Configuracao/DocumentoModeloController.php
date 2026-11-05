<?php

namespace api\modules\Configuracao;

use Psr\Http\Message\UploadedFileInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Psr7\UploadedFile;

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

    public function getModelosImportaveis(Request $request, Response $response): Response
    {
        try {
            $idPessoa = (int) ($request->getAttribute('user_id') ?? 0);
            $modelos = $this->documentoModeloService->listarModelosImportaveis($idPessoa);

            $response->getBody()->write(json_encode($modelos, JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->responderErro($response, $e);
        }
    }

    public function getCorpoModeloImportavel(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $this->validarId($args['id'] ?? null);
            $idPessoa = (int) ($request->getAttribute('user_id') ?? 0);
            $modelo = $this->documentoModeloService->obterCorpoModeloImportavel($id, $idPessoa);

            $response->getBody()->write(json_encode($modelo, JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->responderErro($response, $e);
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

    public function atualizarModelo(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $this->validarId($args['id'] ?? null);
            [$body, $uploadedFiles] = $this->obterDadosAtualizacao($request);
            $descricao = $body['descricao'] ?? null;

            if (!is_string($descricao)) {
                throw new \InvalidArgumentException('Descrição do modelo de documento é obrigatória.', 400);
            }

            $arquivo = $uploadedFiles['arquivo'] ?? null;
            if ($arquivo !== null && !($arquivo instanceof UploadedFileInterface)) {
                throw new \InvalidArgumentException('Arquivo do modelo de documento inválido.', 400);
            }

            $idPessoa = (int) ($request->getAttribute('user_id') ?? 0);
            if ($idPessoa <= 0) {
                throw new \InvalidArgumentException('Usuário autenticado não identificado.', 401);
            }

            $this->documentoModeloService->atualizarModelo($id, $descricao, $arquivo, $idPessoa);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Modelo de documento atualizado com sucesso.',
                'modelo' => ['id' => $id],
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->responderErro($response, $e);
        }
    }

    public function alterarGlobalidade(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $this->validarId($args['id'] ?? null);
            $body = $request->getParsedBody() ?? [];

            if (!is_array($body) || !array_key_exists('global', $body)) {
                throw new \InvalidArgumentException('O campo global é obrigatório.', 400);
            }

            if (!is_bool($body['global'])) {
                throw new \InvalidArgumentException('O campo global deve ser booleano.', 400);
            }
            $global = $body['global'];

            $idPessoa = (int) ($request->getAttribute('user_id') ?? 0);
            if ($idPessoa <= 0) {
                throw new \InvalidArgumentException('Usuário autenticado não identificado.', 401);
            }

            $this->documentoModeloService->alterarGlobalidade($id, $global, $idPessoa);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Globalidade do modelo alterada com sucesso.',
                'modelo' => ['id' => $id, 'global' => $global],
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->responderErro($response, $e);
        }
    }

    public function excluirModelo(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $this->validarId($args['id'] ?? null);
            $idPessoa = (int) ($request->getAttribute('user_id') ?? 0);
            if ($idPessoa <= 0) {
                throw new \InvalidArgumentException('Usuário autenticado não identificado.', 401);
            }

            $this->documentoModeloService->excluirModelo($id, $idPessoa);

            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Modelo de documento excluído com sucesso.',
            ], JSON_UNESCAPED_UNICODE));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            return $this->responderErro($response, $e);
        }
    }

    private function obterDadosAtualizacao(Request $request): array
    {
        $body = $request->getParsedBody() ?? [];
        $uploadedFiles = $request->getUploadedFiles();

        if (!is_array($body)) {
            throw new \InvalidArgumentException('Dados de atualização do modelo inválidos.', 400);
        }

        if (array_key_exists('arquivo', $body)) {
            $arquivo = $body['arquivo'];
            if (!is_array($arquivo)) {
                throw new \InvalidArgumentException('Arquivo do modelo de documento inválido.', 400);
            }

            $nome = $arquivo['nome'] ?? null;
            $conteudoBase64 = $arquivo['conteudo_base64'] ?? null;
            if (!is_string($nome) || !is_string($conteudoBase64) || $conteudoBase64 === '') {
                throw new \InvalidArgumentException('Nome e conteúdo Base64 do arquivo são obrigatórios.', 400);
            }

            $conteudo = base64_decode($conteudoBase64, true);
            if ($conteudo === false || $conteudo === '') {
                throw new \InvalidArgumentException('Conteúdo Base64 do arquivo inválido.', 400);
            }

            $stream = (new StreamFactory())->createStream($conteudo);
            $uploadedFiles['arquivo'] = new UploadedFile(
                $stream,
                $nome,
                'application/octet-stream',
                strlen($conteudo)
            );
        }

        return [$body, $uploadedFiles];
    }

    private function validarId(mixed $id): int
    {
        $idValidado = filter_var($id, FILTER_VALIDATE_INT);
        if ($idValidado === false || $idValidado === null || $idValidado < 1) {
            throw new \InvalidArgumentException('ID do modelo de documento inválido.', 400);
        }

        return $idValidado;
    }

    private function responderErro(Response $response, \Throwable $e): Response
    {
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
