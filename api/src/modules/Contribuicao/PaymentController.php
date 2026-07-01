<?php

namespace api\Modules\Contribuicao;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PaymentController {
    private PaymentRepository $paymentMethodRepository;

    public function __construct(PaymentRepository $paymentMethodRepository) {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * Returns all payment methods with their respective rules.
     *
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param array $args
     * @return \Slim\Http\Response
     */
    public function getAllPaymentsRules(Request $request, Response $response, $args) {
        try {
            $rules = $this->paymentMethodRepository->getAllPaymentRules();
            $response->getBody()->write(json_encode(['rules' => $rules]));

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao buscar regras de pagamento: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}