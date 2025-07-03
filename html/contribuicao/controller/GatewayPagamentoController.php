<?php

require_once '../model/GatewayPagamento.php';
require_once '../dao/GatewayPagamentoDAO.php';

class GatewayPagamentoController
{
    /**Realiza os procedimentos necessários para inserir um Gateway de pagamento na aplicação */
    public function cadastrar()
    {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $endpoint = filter_input(INPUT_POST, 'endpoint', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        try {
            $gatewayPagamento = new GatewayPagamento($nome, $endpoint, $token);
            $gatewayPagamento->cadastrar();
            header("Location: ../view/gateway_pagamento.php?msg=cadastrar-sucesso");
        } catch (Exception $e) {
            header("Location: ../view/gateway_pagamento.php?msg=cadastrar-falha");
        }
    }

    /**
     * Realiza os procedimentos necessários para buscar os gateways de pagamento da aplicação
     */
    public function buscaTodos()
    {
        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gateways = $gatewayPagamentoDao->buscaTodos();
            return $gateways;
        } catch (PDOException $e) {
            echo 'Erro na busca de gateways de pagamento: ' . $e->getMessage();
        }
    }

    /**
     * Realiza os procedimentos necessários para remover um gateway de pagamento do sistema.
     */
    public function excluirPorId()
    {
        $gatewayId = trim($_POST['gateway-id']);

        if (!$gatewayId || empty($gatewayId) || $gatewayId < 1) {
            //parar operação
            header("Location: ../view/gateway_pagamento.php?msg=excluir-falha#mensagem-tabela");
            exit();
        }

        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoDao->excluirPorId($gatewayId);
            header("Location: ../view/gateway_pagamento.php?msg=excluir-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            header("Location: ../view/gateway_pagamento.php?msg=excluir-falha#mensagem-tabela");
        }
        //echo 'O id do gateway que será excluído é: '.$gatewayId;
    }

    /**
     * Realiza os procedimentos necessários para alterar as informações de um gateway de pagamento do sistema
     */
    public function editarPorId()
    {
        // Sanitiza o ID como número inteiro
        $gatewayId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        // Sanitiza os campos de texto removendo caracteres especiais
        $gatewayNome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $gatewayEndepoint = filter_input(INPUT_POST, 'endpoint', FILTER_SANITIZE_URL); // URL pode conter : / ? etc
        $gatewayToken = filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); // Não sanitiza (token pode ter símbolos)

        try {
            // Validação básica adicional
            if (!$gatewayId || !$gatewayNome || !$gatewayEndepoint) {
                throw new InvalidArgumentException('Falha na validação dos campos', 400);
            }

            $gatewayPagamento = new GatewayPagamento($gatewayNome, $gatewayEndepoint, $gatewayToken);
            $gatewayPagamento->setId($gatewayId);
            $gatewayPagamento->editar();
            header("Location: ../view/gateway_pagamento.php?msg=editar-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            header("Location: ../view/gateway_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para ativar/desativar um gateway de pagamento no sistema
     */
    public function alterarStatus()
    {
        $gatewayId = $_POST['id'];
        $status = trim($_POST['status']);

        if (!$gatewayId || empty($gatewayId)) {
            http_response_code(400);
            echo json_encode(['Erro' => 'O id deve ser maior ou igual a 1.']);
            exit;
        }

        if (!$status || empty($status)) {
            http_response_code(400);
            echo json_encode(['Erro' => 'O status informado não é válido.']);
            exit;
        }

        if ($status === 'true') {
            $status = 1;
        } elseif ($status === 'false') {
            $status = 0;
        }

        try {
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoDao->alterarStatusPorId($status, $gatewayId);
            echo json_encode(['Sucesso']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['Erro' => 'Ocorreu um problema no servidor.']);
            exit;
        }
    }
}
