<?php

require_once '../model/MeioPagamento.php';
require_once '../dao/MeioPagamentoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SistemaLog.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SistemaLogDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ConexaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'Util.php';

class MeioPagamentoController{

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $this->pdo = ConexaoDAO::conectar();
        } else {
            $this->pdo = $pdo;
        }
    }

    public function cadastrar(){
        $descricao = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $gatewayId = filter_input(INPUT_POST, 'meio-pagamento-plataforma', FILTER_SANITIZE_NUMBER_INT);
        try{
            $this->pdo->beginTransaction();
            $meioPagamento = new MeioPagamento($descricao, $gatewayId);
            $meioPagamento->cadastrar();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), 'Cadastro de meio de pagamento.');

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/meio_pagamento.php?msg=cadastrar-falha");
                exit();
            }

            $this->pdo->commit();

            header("Location: ../view/meio_pagamento.php?msg=cadastrar-sucesso");
        }catch(Exception $e){
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/meio_pagamento.php?msg=cadastrar-falha");
        }
    }

    /**
     * Busca os meios de pagamentos registrados no banco de dados da aplicação
     */
    public function buscaTodos(){
        try{
            $this->pdo->beginTransaction();
            $meioPagamentoDao = new MeioPagamentoDAO();
            $meiosPagamento = $meioPagamentoDao->buscaTodos();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 5, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), 'Pesquisa de meios de pagamento.');

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                exit();
            }

            $this->pdo->commit();

            return $meiosPagamento;
        }catch(PDOException $e){
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
        }
    }

    /**
     * Verifica o estado de atividade do meio de pagamento no banco de dados
     */
    public function verificarStatus(string $meioPagamento, bool $status):bool{

        $meioPagamentoDao = new MeioPagamentoDAO();
        $meioPagamento = $meioPagamentoDao->buscarPorNome($meioPagamento);

        if(is_null($meioPagamento)){
            return false;
        }

        if($meioPagamento->getStatus() === 0){
            return false;
        }

        return true;
    }

    /**
     * Realiza os procedimentos necessários para remover um meio de pagamento do sistema.
     */
    public function excluirPorId(){
        $meioPagamentoId = trim($_POST['meio-pagamento-id']);

        if (!$meioPagamentoId || empty($meioPagamentoId) || $meioPagamentoId < 1) {
            //parar operação
            header("Location: ../view/meio_pagamento.php?msg=excluir-falha#mensagem-tabela");
            exit();
        }

        try{
            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamentoDao->excluirPorId($meioPagamentoId);
            header("Location: ../view/meio_pagamento.php?msg=excluir-sucesso#mensagem-tabela");
        }catch(Exception $e){
            header("Location: ../view/meio_pagamento.php?msg=excluir-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para alterar as informações de um meio de pagamento do sistema
     */
    public function editarPorId(){
        $descricao = $_POST['nome'];
        $gatewayId = $_POST['plataforma'];
        $meioPagamentoId = $_POST['id'];

        try{
            $meioPagamento = new MeioPagamento($descricao, $gatewayId);
            $meioPagamento->setId($meioPagamentoId);
            $meioPagamento->editar();
            header("Location: ../view/meio_pagamento.php?msg=editar-sucesso#mensagem-tabela");
        }catch(Exception $e){
            header("Location: ../view/meio_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }

     /**
     * Realiza os procedimentos necessários para ativar/desativar um meio de pagamento no sistema
     */
    public function alterarStatus()
    {
        $meioPagamentoId = $_POST['id'];
        $status = trim($_POST['status']);

        if (!$meioPagamentoId || empty($meioPagamentoId)) {
            http_response_code(400);
            echo json_encode(['Erro' => 'O id deve ser maior ou igual a 1.']);exit;
        }

        if (!$status || empty($status)) {
            http_response_code(400);
            echo json_encode(['Erro' => 'O status informado não é válido.']);exit;
        }

        if ($status === 'true') {
            $status = 1;
        } elseif ($status === 'false') {
            $status = 0;
        }

        try {
            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamentoDao->alterarStatusPorId($status, $meioPagamentoId);
            echo json_encode(['Sucesso']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['Erro'=>'Ocorreu um problema no servidor.']);exit;
        }
    }
}