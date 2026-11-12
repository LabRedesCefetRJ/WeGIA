<?php

use api\contracts\services\EmailVerificationServiceInterface;
use api\contracts\services\PessoaServiceInterface;
use api\contracts\services\SocioServiceInterface;
use api\Container\AppContainer;
use api\modules\Auth\AuthMiddleware;
use api\modules\Auth\AuthService;
use api\modules\Auth\UserRepository;
use api\modules\Pessoa\PessoaService;
use api\modules\Socio\EmailVerificationService;
use api\modules\Socio\SocioController;
use api\modules\Socio\SocioMiddleware;
use api\modules\Socio\SocioRepository;
use api\modules\Socio\SocioService;
use api\modules\Socio\SocioVerificationHelper;
use api\modules\Socio\VerificationCodeRepository;
use Slim\App;

return [
    'dependencies' => [
        SocioRepository::class => function ($container) {
            return new SocioRepository($container->get(PDO::class));
        },
        VerificationCodeRepository::class => function ($container) {
            return new VerificationCodeRepository($container->get(PDO::class));
        },
        EmailVerificationService::class => function ($container) {
            return new EmailVerificationService($container->get(VerificationCodeRepository::class), 15);
        },
        EmailVerificationServiceInterface::class => function ($container) {
            return $container->get(EmailVerificationService::class);
        },
        SocioService::class => function ($container) {
            return new SocioService(
                $container->get(SocioRepository::class),
                $container->get(EmailVerificationService::class),
                $container->get(AuthService::class)
            );
        },
        SocioServiceInterface::class => function ($container) {
            return $container->get(SocioService::class);
        },
        SocioVerificationHelper::class => function ($container) {
            return new SocioVerificationHelper(
                $container->get(PessoaServiceInterface::class),
                $container->get(SocioService::class),
                $container->get(EmailVerificationService::class)
            );
        },
        SocioController::class => function ($container) {
            return new SocioController(
                $container->get(SocioService::class),
                $container->get(PessoaServiceInterface::class),
                $container->get(EmailVerificationService::class),
                $container->get(SocioVerificationHelper::class)
            );
        },
        SocioMiddleware::class => function ($container) {
            return new SocioMiddleware($container->get(UserRepository::class), 4);
        },
    ],
    'routes' => function (App $app, AppContainer $container): void {
        $app->post('/socios/register', [SocioController::class, 'registerSocio']);

        $app->get('/socios/exists/{cpf}', [SocioController::class, 'checkSocioExistsByCpf']);
        $app->get('/socios/verify-code', [SocioController::class, 'sendVerificationCodeByCpf']);
        $app->get('/socios/support-contact', [SocioController::class, 'getSupportContact']);
        $app->get('/socios/{uuid}/validar_beneficios', [SocioController::class, 'validarBeneficiosPorUuid']);
        $app->post('/socios/verify-code', [SocioController::class, 'verifyCode']);
        $app->post('/socios/alter-password', [SocioController::class, 'alterPassword']);

        $app->get('/socios/parceiros', [SocioController::class, 'getSocioParceiros']);
        $app->get('/socios/parceiros/{id}/logo', [SocioController::class, 'getLogoSocioParceiro']);

        $app->post('/socios/parceiros', [SocioController::class, 'insertSocioParceiro'])
            ->add($container->get(SocioMiddleware::class))
            ->add($container->get(AuthMiddleware::class));

        $app->put('/socios/parceiros', [SocioController::class, 'updateSocioParceiro'])
            ->add($container->get(SocioMiddleware::class))
            ->add($container->get(AuthMiddleware::class));

        $app->patch('/socios/parceiros', [SocioController::class, 'alterStatusSocioParceiro'])
            ->add($container->get(SocioMiddleware::class))
            ->add($container->get(AuthMiddleware::class));

        $app->post('/socios/parceiros/logo', [SocioController::class, 'uploadLogoSocioParceiro'])
            ->add($container->get(SocioMiddleware::class))
            ->add($container->get(AuthMiddleware::class));

        $app->post('/socios/parceiros/setor', [SocioController::class, 'insertSocioParceiroSetor'])
            ->add($container->get(SocioMiddleware::class))
            ->add($container->get(AuthMiddleware::class));

        $app->get('/socios/parceiros/setor', [SocioController::class, 'getAllSocioParceiroSetor'])
            ->add($container->get(AuthMiddleware::class));

        $app->delete('/socios/parceiros/{id}', [SocioController::class, 'deleteSocioParceiro'])
            ->add($container->get(SocioMiddleware::class))
            ->add($container->get(AuthMiddleware::class));

        $app->get('/socios/{cpf}', [SocioController::class, 'getSocioByCpf'])
            ->add($container->get(AuthMiddleware::class));

        // Benefícios
        $app->get('/socios/{id}/beneficios', [SocioController::class, 'getBeneficiosBySocio'])
            ->add($container->get(AuthMiddleware::class));
    },
];
