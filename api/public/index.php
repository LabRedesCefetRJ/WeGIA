<?php

use api\Container\AppContainer;
use api\modules\Auth\AuthController;
use api\modules\Auth\AuthMiddleware;
use api\modules\Auth\AuthService;
use api\modules\Auth\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../../config.php';

$container = new AppContainer([
    PDO::class => function () {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    },
    UserRepository::class => function ($c) {
        return new UserRepository($c->get(PDO::class));
    },
    AuthService::class => function ($c) {
        return new AuthService($c->get(UserRepository::class));
    },
    AuthController::class => function ($c) {
        return new AuthController($c->get(AuthService::class));
    },
    AuthMiddleware::class => function ($c) {
        return new AuthMiddleware($c->get(AuthService::class));
    },
]);

AppFactory::setContainer($container);

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

$app->get('/protected', function (Request $request, Response $response, $args) {
    $response->getBody()->write("This is a protected route!");
    return $response;
})->add($container->get(AuthMiddleware::class));

$app->post('/login', [AuthController::class, 'login']);
$app->post('/register', [AuthController::class, 'register']);

$app->run();
