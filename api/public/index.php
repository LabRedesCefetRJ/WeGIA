<?php

use api\Container\AppContainer;
use api\contracts\services\SocioServiceInterface;
use api\contracts\services\EmailVerificationServiceInterface;
use api\modules\Socio\SocioController;
use api\modules\Socio\SocioService;
use api\modules\Socio\EmailVerificationService;
use api\modules\Socio\VerificationCodeRepository;
use api\contracts\services\PessoaServiceInterface;
use api\modules\Pessoa\PessoaService;
use api\modules\Pessoa\PessoaRepository;
use api\modules\Auth\AuthController;
use api\modules\Auth\AuthMiddleware;
use api\modules\Auth\AuthService;
use api\modules\Auth\UserRepository;
use api\modules\Socio\SocioRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';
require __DIR__ . '/../../web/classes/LoginHelper.php';

//dividir container em arquivos separados para cada módulo
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
    SocioRepository::class => function ($c) {
        return new SocioRepository($c->get(PDO::class));
    },
    VerificationCodeRepository::class => function ($c) {
        return new VerificationCodeRepository($c->get(PDO::class));
    },
    EmailVerificationService::class => function ($c) {
        return new EmailVerificationService($c->get(VerificationCodeRepository::class), 15, 'WeGIA');
    },
    EmailVerificationServiceInterface::class => function ($c) {
        return $c->get(EmailVerificationService::class);
    },
    SocioService::class => function ($c) {
        return new SocioService($c->get(SocioRepository::class), $c->get(EmailVerificationService::class), $c->get(AuthService::class));
    },
    SocioServiceInterface::class => function ($c) {
        return $c->get(SocioService::class);
    },
    PessoaRepository::class => function ($c) {
        return new PessoaRepository($c->get(PDO::class));
    },
    PessoaService::class => function ($c) {
        return new PessoaService($c->get(PessoaRepository::class));
    },
    PessoaServiceInterface::class => function ($c) {
        return $c->get(PessoaService::class);
    },
    SocioController::class => function ($c) {
        return new SocioController($c->get(SocioService::class), $c->get(PessoaServiceInterface::class), $c->get(AuthService::class), $c->get(EmailVerificationService::class));
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

//Módulo Sócio
$app->post('/socios/register', [SocioController::class, 'registerSocio']);

//aplicar middleware de autenticação e de permissão
$app->get('/socios/{cpf}', [SocioController::class, 'getSocioByCpf'])->add($container->get(AuthMiddleware::class));

$app->get('/socios/exists/{cpf}', [SocioController::class, 'checkSocioExistsByCpf']);
$app->post('/socios/verify-code', [SocioController::class, 'verifyCode']);
$app->post('/socios/alter-password', [SocioController::class, 'alterPassword']);

$app->run();
