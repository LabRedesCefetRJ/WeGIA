<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '../../../config.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$displayErrorDetails = ENV_APP === 'development' ? true : false;

/**
 * Add Error Middleware
 *
 * @param bool                  $displayErrorDetails -> Should be set to false in production
 * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool                  $logErrorDetails -> Display error details in error log
 * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger  
 *
 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);

$app->get('/wegia', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello, API Wegia!");
    return $response;
});

$app->run();
