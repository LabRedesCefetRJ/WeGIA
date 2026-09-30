<?php

namespace api\modules\Saude;

use api\modules\Auth\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SaudeMiddleware
{
    private UserRepository $repository;
    private int $resourceId;

    public function __construct(UserRepository $repository, int $resourceId = 5)
    {
        $this->repository = $repository;
        $this->resourceId = $resourceId;
    }

    public function __invoke(Request $request, $handler): Response
    {
        $userId = $request->getAttribute('user_id');

        if (!$userId) {
            return $this->forbidden('User ID não encontrado no token');
        }

        if (!$this->repository->hasAccessToResource((int) $userId, $this->resourceId)) {
            return $this->forbidden('Usuário não possui permissão para acessar este módulo de saúde');
        }

        $accessLevel = $this->repository->getAccessLevel((int) $userId, $this->resourceId);
        $request = $request->withAttribute('resource_id', $this->resourceId);
        $request = $request->withAttribute('access_level', $accessLevel);

        return $handler->handle($request);
    }

    private function forbidden(string $msg = 'Acesso negado'): Response
    {
        $response = new \Slim\Psr7\Response();

        $response->getBody()->write(json_encode([
            'error' => $msg,
            'status' => 'forbidden'
        ]));

        return $response->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
}
