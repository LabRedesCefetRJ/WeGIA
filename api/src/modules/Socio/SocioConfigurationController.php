<?php

namespace api\modules\Socio;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDOException;

class SocioConfigurationController
{
    private SocioConfigurationService $service;

    public function __construct(SocioConfigurationService $service)
    {
        $this->service = $service;
    }

    public function getAll(Request $request, Response $response): Response
    {
        try {
            return $this->jsonResponse($response, [
                'configurations' => $this->service->getAll(),
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($response, $e);
        }
    }

    public function getVideoDemonstration(Request $request, Response $response): Response
    {
        try {
            $configuration = $this->service->getVideoDemonstration();

            return $this->jsonResponse($response, [
                'type' => SocioConfigurationService::VIDEO_DEMONSTRATION,
                'value' => $configuration['value'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return $this->serverError($response, $e);
        }
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        if (!is_array($data) || !isset($data['type'], $data['value']) || !is_string($data['type']) || !is_string($data['value'])) {
            return $this->jsonResponse($response, ['error' => 'Os campos type e value são obrigatórios.'], 400);
        }

        try {
            $configuration = $this->service->create($data['type'], trim($data['value']));

            return $this->jsonResponse($response, ['configuration' => $configuration], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return $this->jsonResponse($response, ['error' => 'Já existe uma configuração para esse tipo.'], 409);
            }

            return $this->serverError($response, $e);
        } catch (\Throwable $e) {
            return $this->serverError($response, $e);
        }
    }

    public function update(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        if (!is_array($data) || !isset($data['type'], $data['value']) || !is_string($data['type']) || !is_string($data['value'])) {
            return $this->jsonResponse($response, ['error' => 'Os campos type e value são obrigatórios.'], 400);
        }

        try {
            $configuration = $this->service->update($data['type'], trim($data['value']));
            if ($configuration === null) {
                return $this->jsonResponse($response, ['error' => 'Configuração não encontrada.'], 404);
            }

            return $this->jsonResponse($response, ['configuration' => $configuration]);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->serverError($response, $e);
        }
    }

    public function delete(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];
        if (!is_array($data) || !isset($data['type']) || !is_string($data['type'])) {
            return $this->jsonResponse($response, ['error' => 'O campo type é obrigatório.'], 400);
        }

        try {
            if (!$this->service->delete($data['type'])) {
                return $this->jsonResponse($response, ['error' => 'Configuração não encontrada.'], 404);
            }

            return $this->jsonResponse($response, ['message' => 'Configuração removida com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            return $this->serverError($response, $e);
        }
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    private function serverError(Response $response, \Throwable $exception): Response
    {
        error_log('[SocioConfigurationController] ' . $exception->getMessage());

        return $this->jsonResponse($response, ['error' => 'Erro ao processar a configuração do sócio.'], 500);
    }
}
