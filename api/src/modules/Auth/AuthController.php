<?php

namespace api\modules\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use api\modules\Auth\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            $result = $this->authService->login(
                $data['login'] ?? '',
                $data['senha'] ?? ''
            );

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        try {
            $result = $this->authService->register(
                $data['login'] ?? '',
                $data['senha'] ?? ''
            );

            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}