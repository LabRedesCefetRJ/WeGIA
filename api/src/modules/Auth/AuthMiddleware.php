<?php

namespace api\modules\Auth;

use api\modules\Auth\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request, $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = $this->authService->validateToken($token);

            $request = $request->withAttribute('user_id', $decoded->sub);

        } catch (\Exception $e) {
            return $this->unauthorized($e->getMessage());
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $msg = 'Token inválido'): Response
    {
        $response = new \Slim\Psr7\Response();

        $response->getBody()->write(json_encode([
            'error' => $msg
        ]));

        return $response->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
}