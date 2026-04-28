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
require __DIR__ . '/../../classes/LoginHelper.php';

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

$app->get('/dashboard', function (Request $request, Response $response, $args) {
    //pegar do token o id do usuário e usar para mostrar as informações do usuário logado
    $userId = $request->getAttribute('user_id');

    if (!$userId) {
        $response->getBody()->write(json_encode(['error' => 'User ID not found']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $pessoaRepository = new \api\modules\Pessoa\PessoaRepository($this->get(PDO::class));
    $user = $pessoaRepository->findById($userId);
    $userName = $user ? $user['nome'] . ' ' . $user['sobrenome'] : 'Unknown';

    $response->getBody()->write("Hello, $userName! Welcome to your dashboard.");
    return $response;
})->add($container->get(AuthMiddleware::class));

$app->post('/login', [AuthController::class, 'login']);
$app->post('/register', [AuthController::class, 'register']);
$app->post('/refresh', [AuthController::class, 'refresh']);
$app->post('/logout', [AuthController::class, 'logout']); //revisar lógica de logout, os tokens são stateless, então não tem como invalidar o token, a única forma é ter uma blacklist de tokens ou usar um campo de "token_version" no banco de dados para invalidar os tokens antigos

$app->run();
