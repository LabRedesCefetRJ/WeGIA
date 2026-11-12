<?php

use api\Container\AppContainer;
use api\modules\Auth\AuthMiddleware;
use api\Infrastructure\ModuleRouteLoader;
use api\Infrastructure\RepositoryConnection;
use api\middleware\CorsMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../../web/classes/LoginHelper.php';

$routeLoader = new ModuleRouteLoader(__DIR__ . '/../src/modules');
$moduleRoutes = $routeLoader->load();

$container = new AppContainer(array_merge(
    $routeLoader->getContainerEntries($moduleRoutes),
    [
        PDO::class => function () {
            return RepositoryConnection::getConnection();
        },
        CorsMiddleware::class => function () {
            $origin = defined('CORS_ORIGIN') ? CORS_ORIGIN : '*';
            return new CorsMiddleware(
                $origin,
                ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
                ['Content-Type', 'Authorization', 'X-Requested-With', 'X-Client-Type'],
                true,
                86400
            );
        },
    ]
));

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->add($container->get(CorsMiddleware::class));
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

// Handle OPTIONS requests (CORS preflight)
$app->options('/{routes:.*}', function (Request $request, Response $response) {
    return $response;
});

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
})
    /*->add($container->get(SocioMiddleware::class))*/
    ->add($container->get(AuthMiddleware::class));

$routeLoader->registerRoutes($app, $container, $moduleRoutes);

$app->run();
