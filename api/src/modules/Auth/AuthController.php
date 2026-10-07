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

            if ($this->isWebClient($request)) {

                $this->setAuthCookies(
                    $result['access_token'],
                    $result['refresh_token']
                );

                $response->getBody()->write(json_encode([
                    'success' => true
                ]));
            } else {

                $response->getBody()->write(json_encode($result));
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response
                ->withStatus(401)
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

    public function refresh(Request $request, Response $response): Response
    {
        if ($this->isWebClient($request)) {
            $refreshToken = $_COOKIE['refresh_token'] ?? null;
        } else {
            $data = $request->getParsedBody();
            $refreshToken = $data['refresh_token'] ?? null;
        }

        if (empty($refreshToken)) {
            $response->getBody()->write(json_encode([
                'error' => 'Refresh token é obrigatório'
            ]));

            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }

        try {

            $result = $this->authService->refreshToken($refreshToken);

            if ($this->isWebClient($request)) {

                $this->setAuthCookies(
                    $result['access_token'],
                    $result['refresh_token'] ?? null
                );

                $response->getBody()->write(json_encode([
                    'success' => true
                ]));
            } else {

                $response->getBody()->write(json_encode($result));
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ]));

            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        // Tenta obter o token do cookie (Web)
        $accessToken = $_COOKIE['access_token'] ?? null;

        // Caso não exista, tenta obter do cabeçalho Authorization (Mobile/API)
        if ($accessToken === null || empty($accessToken)) {
            $authHeader = $request->getHeaderLine('Authorization');

            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $accessToken = substr($authHeader, 7);
            } else if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                $response->getBody()->write(json_encode([
                    'error' => 'Token não fornecido'
                ]));

                return $response->withStatus(401)
                    ->withHeader('Content-Type', 'application/json');
            }
        }

        //pegar do cookie se for web, pegar do body se for mobile
        if ($this->isWebClient($request)) {
            $refreshToken = $_COOKIE['refresh_token'] ?? null;
        } else {
            $data = $request->getParsedBody();
            $refreshToken = $data['refresh_token'] ?? null;
        }

        try {
            $result = $this->authService->logout($accessToken, $refreshToken);

            if ($this->isWebClient($request)) {
                $this->clearAuthCookies();
            }

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

    private function isWebClient(Request $request): bool
    {
        return strtolower($request->getHeaderLine('X-Client-Type')) === 'web';
    }

    private function setAuthCookies(string $accessToken, ?string $refreshToken = null): void
    {
        setcookie('access_token', $accessToken, [
            'expires'   => time() + 900, // 15 minutos
            'path'      => '/',
            'secure'    => true,
            'httponly'  => true,
            'samesite'  => 'Strict'
        ]);

        if ($refreshToken !== null) {
            setcookie('refresh_token', $refreshToken, [
                'expires'   => time() + (60 * 60 * 24 * 30), // 30 dias
                'path'      => '/',
                'secure'    => true,
                'httponly'  => true,
                'samesite'  => 'Strict'
            ]);
        }
    }

    private function clearAuthCookies(): void
    {
        $cookies = [
            ['name' => 'access_token', 'path' => '/'],
            ['name' => 'refresh_token', 'path' => '/'],
            ['name' => 'refresh_token', 'path' => '/refresh'],
        ];

        foreach ($cookies as $cookie) {
            setcookie($cookie['name'], '', [
                'expires'   => time() - 3600,
                'path'      => $cookie['path'],
                'secure'    => true,
                'httponly'  => true,
                'samesite'  => 'Strict'
            ]);
        }
    }
}
