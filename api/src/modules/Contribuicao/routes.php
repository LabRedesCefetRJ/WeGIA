<?php

use api\modules\Auth\AuthMiddleware;
use api\modules\Contribuicao\AmigosLajeXlsxImportService;
use api\modules\Contribuicao\ContribuicaoController;
use api\modules\Contribuicao\ContribuicaoImportRegistry;
use api\modules\Contribuicao\ContribuicaoRepository;
use api\modules\Contribuicao\ContribuicaoService;
use api\modules\Contribuicao\PaymentController;
use api\modules\Contribuicao\PaymentRepository;
use api\modules\Pessoa\PessoaRepository;
use api\modules\Socio\SocioMiddleware;
use api\modules\Socio\SocioRepository;
use api\Container\AppContainer;
use api\Infrastructure\ApiErrorResponder;
use Slim\App;

return [
    'dependencies' => [
        ContribuicaoRepository::class => function ($container) {
            return new ContribuicaoRepository($container->get(PDO::class));
        },
        ContribuicaoService::class => function ($container) {
            return new ContribuicaoService($container->get(ContribuicaoRepository::class));
        },
        AmigosLajeXlsxImportService::class => function ($container) {
            return new AmigosLajeXlsxImportService($container->get(ContribuicaoRepository::class));
        },
        ContribuicaoImportRegistry::class => function ($container) {
            return new ContribuicaoImportRegistry([
                'amigos_laje_xlsx' => $container->get(AmigosLajeXlsxImportService::class),
            ]);
        },
        ContribuicaoController::class => function ($container) {
            return new ContribuicaoController(
                $container->get(ContribuicaoService::class),
                $container->get(SocioRepository::class),
                $container->get(PessoaRepository::class),
                $container->get(PDO::class),
                $container->get(ContribuicaoImportRegistry::class),
                $container->get(ApiErrorResponder::class)
            );
        },
        PaymentRepository::class => function ($container) {
            return new PaymentRepository($container->get(PDO::class));
        },
        PaymentController::class => function ($container) {
            return new PaymentController(
                $container->get(PaymentRepository::class),
                $container->get(ApiErrorResponder::class)
            );
        },
    ],
    'routes' => function (App $app, AppContainer $container): void {
        $authMiddleware = $container->get(AuthMiddleware::class);

        $app->get('/socios/{id}/contribuicoes', [ContribuicaoController::class, 'getContribuicoesBySocio'])
            ->add($authMiddleware);
        $app->get('/socios/{id}/contribuicoes/filter', [ContribuicaoController::class, 'getContribuicoesBySocioAndStatus'])
            ->add($authMiddleware);
        $app->get('/socios/{id}/contribuicoes/resume', [ContribuicaoController::class, 'getResumoContribuicoes'])
            ->add($authMiddleware);

        // Gerar PDF do extrato de contribuições
        $app->get('/socios/{id}/contribuicoes/pdf', [ContribuicaoController::class, 'generateContribuicaoPdf'])
            ->add($authMiddleware);

        // Download do PDF de uma contribuição pelo nome do arquivo
        $app->get('/contribuicoes/{contribuicao_id}/pdf', [ContribuicaoController::class, 'downloadContribuicaoPdf'])
            ->add($authMiddleware);

        // Gerar PDF do comprovante de pagamento de uma contribuição
        $app->get('/socios/{id}/contribuicoes/{contribuicao_id}/pdf', [ContribuicaoController::class, 'generateComprovantePdf'])
            ->add($authMiddleware);

        $app->post('/contribuicoes/boleto', [ContribuicaoController::class, 'generateBoleto'])
            ->add($authMiddleware);

        $app->post('/contribuicoes/carne', [ContribuicaoController::class, 'generateCarne'])
            ->add($authMiddleware);

        $app->post('/contribuicoes/pix', [ContribuicaoController::class, 'generatePix'])
            ->add($authMiddleware);

        $app->post('/contribuicoes/credito', [ContribuicaoController::class, 'generateCredito'])
            ->add($authMiddleware);

        $app->post('/contribuicoes/recorrencia', [ContribuicaoController::class, 'generateRecorrencia'])
            ->add($authMiddleware);

        /**
         * Permite que funcionários do WeGIA gerem pagamentos manuais para sócios,
         * como quando um sócio paga em dinheiro ou cheque na sede da associação.
         */
        $app->post('/contribuicoes/manual', [ContribuicaoController::class, 'generateManualPayment'])
            ->add($container->get(SocioMiddleware::class))
            ->add($authMiddleware);

        $app->post('/contribuicoes/import', [ContribuicaoController::class, 'importarContribuicoes'])
            ->add($container->get(SocioMiddleware::class))
            ->add($authMiddleware);

        $app->get('/contribuicoes/payments_rules', [PaymentController::class, 'getAllPaymentsRules'])
            ->add($authMiddleware);

        $app->get('/contribuicoes/payment_methods', [PaymentController::class, 'getActivePaymentMethods'])
            ->add($authMiddleware);

        $app->get('/contribuicoes/payments_gateway/{payment_method}', [PaymentController::class, 'getPaymentGatewayByPaymentMethod'])
            ->add($authMiddleware);
    },
];
