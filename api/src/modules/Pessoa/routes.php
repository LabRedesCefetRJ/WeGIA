<?php

use api\contracts\services\PessoaServiceInterface;
use api\Container\AppContainer;
use api\Infrastructure\ApiErrorResponder;
use api\modules\Auth\AuthMiddleware;
use api\modules\Pessoa\PessoaController;
use api\modules\Pessoa\PessoaRepository;
use api\modules\Pessoa\PessoaService;
use Slim\App;

return [
    'dependencies' => [
        PessoaRepository::class => function ($container) {
            return new PessoaRepository($container->get(PDO::class));
        },
        PessoaService::class => function ($container) {
            return new PessoaService($container->get(PessoaRepository::class));
        },
        PessoaServiceInterface::class => function ($container) {
            return $container->get(PessoaService::class);
        },
        PessoaController::class => function ($container) {
            return new PessoaController(
                $container->get(PessoaService::class),
                $container->get(ApiErrorResponder::class)
            );
        },
    ],
    'routes' => function (App $app, AppContainer $container): void {
        $app->get('/pessoas/homonimo/{fullname}', [PessoaController::class, 'checkHomonym']);

        $app->put('/pessoas/profile', [PessoaController::class, 'updateProfile'])
            ->add($container->get(AuthMiddleware::class));

        $app->post('/pessoas/profile/photo', [PessoaController::class, 'updateProfilePhoto'])
            ->add($container->get(AuthMiddleware::class));

        $app->get('/pessoas/{id}/profile/photo', [PessoaController::class, 'getProfilePhoto'])
            ->add($container->get(AuthMiddleware::class));
    },
];
