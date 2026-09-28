<?php

namespace api\middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CorsMiddleware implements MiddlewareInterface
{
    private $allowedOrigin;
    private $allowedMethods;
    private $allowedHeaders;
    private $allowCredentials;
    private $maxAge;

    public function __construct(
        string $allowedOrigin = '*',
        array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With'],
        bool $allowCredentials = true,
        int $maxAge = 86400
    ) {
        $this->allowedOrigin = $allowedOrigin;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowCredentials = $allowCredentials;
        $this->maxAge = $maxAge;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            return $this->handlePreflightRequest($request);
        }

        $response = $handler->handle($request);
        return $this->addCorsHeaders($response, $request);
    }

    private function handlePreflightRequest(Request $request): Response
    {
        $response = new \Slim\Psr7\Response();
        return $this->addCorsHeaders($response, $request)->withStatus(200);
    }

    private function addCorsHeaders(Response $response, Request $request): Response
    {
        $origin = $request->getHeaderLine('Origin');

        // Check if the origin is allowed
        if ($this->isOriginAllowed($origin)) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
                ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders))
                ->withHeader('Access-Control-Max-Age', (string)$this->maxAge);

            if ($this->allowCredentials) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }

    private function isOriginAllowed(string $origin): bool
    {
        // Allow all origins if wildcard is set
        if ($this->allowedOrigin === '*') {
            return true;
        }

        // Allow specific origin
        if ($this->allowedOrigin === $origin) {
            return true;
        }

        // Allow multiple origins (comma-separated)
        $allowedOrigins = array_map('trim', explode(',', $this->allowedOrigin));
        return in_array($origin, $allowedOrigins);
    }
}
