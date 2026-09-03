<?php

namespace api\modules\Contribuicao;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PaymentController
{
    private PaymentRepository $paymentMethodRepository;

    public function __construct(PaymentRepository $paymentMethodRepository)
    {
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
    public function getAllPaymentsRules(Request $request, Response $response, $args)
    {
        try {

            $rows = $this->paymentMethodRepository->getAllPaymentRules();

            $paymentMethods = [];

            foreach ($rows as $row) {

                $paymentMethodKey = $this->toSnakeCase($row['payment_method']);

                if (!isset($paymentMethods[$paymentMethodKey])) {
                    $paymentMethods[$paymentMethodKey] = new PaymentMethod();
                }

                $rule = (new PaymentRules())
                    ->setDescription($this->toSnakeCase($row['rule']))
                    ->setValue((float) $row['value']);

                $paymentMethods[$paymentMethodKey]
                    ->setRules($rule);
            }

            $response->getBody()->write(json_encode([
                'payment_methods' => $paymentMethods
            ]));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'error' => 'Erro ao buscar regras de pagamento: ' . $e->getMessage()
            ]));

            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function getActivePaymentMethods(Request $request, Response $response, $args): Response
    {
        try {
            $response->getBody()->write(json_encode([
                'payment_methods' => $this->paymentMethodRepository->getActivePaymentMethods()
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao buscar meios de pagamento: ' . $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getPaymentGatewayByPaymentMethod(Request $request, Response $response, $args)
    {
        try {
            $paymentMethod = $args['payment_method'] ?? null;

            if (!$paymentMethod) {
                throw new \InvalidArgumentException("Payment method is required.");
            }

            $paymentGateway = $this->paymentMethodRepository->getPaymentGatewayByPaymentMethod($paymentMethod);

            if (!$paymentGateway) {
                throw new \RuntimeException("No payment gateway found for the specified payment method.");
            }

            $response->getBody()->write(json_encode([
                'payment_gateway' => $paymentGateway->getPublicData()
            ]));

            return $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {

            $response->getBody()->write(json_encode([
                'error' => 'Erro ao buscar token público: ' . $e->getMessage()
            ]));

            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Converte CamelCase ou UPPER_CASE para snake_case.
     */
    private function toSnakeCase(string $value): string
    {
        // If already contains underscores, normalize multiple underscores and lowercase
        if (strpos($value, '_') !== false) {
            $value = preg_replace('/_+/', '_', $value);
            return strtolower(trim($value, '_'));
        }

        // Handle CamelCase and sequences of uppercase letters
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $value);
        $value = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $value);

        return strtolower($value);
    }
}
