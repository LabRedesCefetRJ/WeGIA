<?php

namespace api\modules\Saude;

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
}