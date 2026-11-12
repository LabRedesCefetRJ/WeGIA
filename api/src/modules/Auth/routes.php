<?php

use api\modules\Auth\AuthController;
use api\modules\Auth\AuthMiddleware;
use api\modules\Auth\AuthService;
use api\modules\Auth\JwtBlacklistRepository;
use api\modules\Auth\UserRepository;
use api\Container\AppContainer;
use Slim\App;

return [
    'dependencies' => [
        UserRepository::class => function ($container) {
            return new UserRepository($container->get(PDO::class));
        },
        JwtBlacklistRepository::class => function ($container) {
            return new JwtBlacklistRepository($container->get(PDO::class));
        },
        AuthService::class => function ($container) {
            return new AuthService(
                $container->get(UserRepository::class),
                $container->get(JwtBlacklistRepository::class)
            );
        },
        AuthController::class => function ($container) {
            return new AuthController($container->get(AuthService::class));
        },
        AuthMiddleware::class => function ($container) {
            return new AuthMiddleware($container->get(AuthService::class));
        },
    ],
    'routes' => function (App $app, AppContainer $container): void {
        $app->post('/login', [AuthController::class, 'login']);
        $app->post('/register', [AuthController::class, 'register']);
        $app->post('/refresh', [AuthController::class, 'refresh']);
        $app->post('/logout', [AuthController::class, 'logout']);
    },
];
