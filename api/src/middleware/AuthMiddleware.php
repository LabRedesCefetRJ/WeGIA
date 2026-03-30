<?php

namespace api\middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthMiddleware
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function __invoke(Request $request, $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

            // injeta dados no request
            $request = $request->withAttribute('token', $decoded);

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