<?php

namespace api\Infrastructure;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Response;
use Throwable;

class ApiErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        private ApiErrorResponder $errorResponder,
        private ErrorHandlerInterface $defaultErrorHandler
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $statusCode = $exception instanceof HttpException ? (int) $exception->getCode() : 500;

        if ($statusCode < 400 || $statusCode > 599) {
            $statusCode = 500;
        }

        if ($statusCode >= 500) {
            return $this->errorResponder->respond(new Response(), $exception, $statusCode, []);
        }

        return ($this->defaultErrorHandler)(
            $request,
            $exception,
            $displayErrorDetails,
            $logErrors,
            $logErrorDetails
        );
    }
}
