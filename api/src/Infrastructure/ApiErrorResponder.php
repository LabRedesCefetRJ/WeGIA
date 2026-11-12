<?php

namespace api\Infrastructure;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Throwable;

class ApiErrorResponder
{
    private const SERVER_ERROR_MESSAGE = 'Ocorreu um erro interno. Tente novamente mais tarde.';

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function respond(
        ResponseInterface $response,
        Throwable|string $error,
        int $statusCode,
        array $clientErrorPayload
    ): ResponseInterface {
        if ($statusCode < 400 || $statusCode > 599) {
            throw new \InvalidArgumentException('Error response status must be between 400 and 599.');
        }

        if ($statusCode >= 500) {
            if ($error instanceof Throwable) {
                $this->logger->error(
                    sprintf('API request failed with HTTP status %d.', $statusCode),
                    ['exception' => $error]
                );
            } else {
                $this->logger->error(sprintf('API request failed with HTTP status %d: %s', $statusCode, $error));
            }

            $payload = [
                'success' => false,
                'error' => self::SERVER_ERROR_MESSAGE,
            ];
        } else {
            $payload = $clientErrorPayload;
        }

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return $response
            ->withBody((new StreamFactory())->createStream($body))
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json')
            ->withoutHeader('Content-Length');
    }
}
