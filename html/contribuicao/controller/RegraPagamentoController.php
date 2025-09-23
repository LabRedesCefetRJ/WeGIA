<?php

require_once '../model/RegraPagamento.php';
require_once '../dao/RegraPagamentoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SistemaLog.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SistemaLogDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ConexaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'Util.php';

class RegraPagamentoController
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $this->pdo = ConexaoDAO::conectar();
        } else {
            $this->pdo = $pdo;
        }
    }

    /**
     * Retorna as regras de contribuição presentes no sistema
     */
    public function buscaRegrasContribuicao()
    {
        $regraPagamentoDao = new RegraPagamentoDAO();
        $regrasContribuicao = $regraPagamentoDao->buscaRegrasContribuicao();
        return $regrasContribuicao;
    }

    /**
     * Retorna o conjunto de regras de pagamento presentes no sistema
     */
    public function buscaConjuntoRegrasPagamento()
    {
        try {
            $this->pdo->beginTransaction();
            $regraPagamentoDao = new RegraPagamentoDAO($this->pdo);
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamento();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_SESSION['id_pessoa'])) {
                $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 74, 5, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), 'Pesquisa de regras de pagamento.');

                $sistemaLogDao = new SistemaLogDAO($this->pdo);
                if (!$sistemaLogDao->registrar($sistemaLog)) {
                    $this->pdo->rollBack();
                    exit();
                }
            }

            $this->pdo->commit();

            return $conjuntoRegrasPagamento;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
        }
    }

    /**
     * Retorna o conjunto de regras de pagamento pertencentes a um meio de pagamento.
     */
    public function buscaConjuntoRegrasPagamentoPorNomeMeioPagamento()
    {

        $nomeMeioPagamento = trim(filter_input(INPUT_GET, 'meio-pagamento', FILTER_SANITIZE_STRING));

        try {
            $regraPagamentoDao = new RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorNomeMeioPagamento($nomeMeioPagamento);

            http_response_code(200);
            echo json_encode(['regras' => $conjuntoRegrasPagamento]);
            exit();
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'erro ao buscar conjunto de regras de pagamento no servidor.']);
            exit();
        }
    }

    /**
     * Extraí os dados do formulário e realiza os procedimentos necessários para inserir um novo
     * conjunto de regras no sistema.
     */
    public function cadastrar()
    {
        //Implementar restante da lógica do código...
        $meioPagamentoId = $_POST['meio-pagamento-plataforma'];
        $regraContribuicaoId = $_POST['regra-pagamento'];
        $valor = $_POST['valor'];
        try {
            $this->pdo->beginTransaction();
            $regraPagamento = new RegraPagamento($this->pdo);
            $regraPagamento
                ->setMeioPagamentoId($meioPagamentoId)
                ->setRegraContribuicaoId($regraContribuicaoId)
                ->setValor($valor)
                ->setStatus(0)
                ->cadastrar();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 74, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), 'Cadastro de regras de pagamento.');

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/regra_pagamento.php?msg=cadastrar-falha");
                exit();
            }

            $this->pdo->commit();
            header("Location: ../view/regra_pagamento.php?msg=cadastrar-sucesso");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/regra_pagamento.php?msg=cadastrar-falha");
        }
    }

    /**
     * Realiza os procedimentos necessários para remover uma regra de pagamento do sistema.
     */
    public function excluirPorId()
    {
        $regraPagamentoId = filter_input(INPUT_POST, 'regra-pagamento-id', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!$regraPagamentoId || empty($regraPagamentoId) || $regraPagamentoId < 1) {
                throw new InvalidArgumentException('O id informado não é válido.', 400);
            }

            $this->pdo->beginTransaction();
            $regraPagamentoDao = new RegraPagamentoDAO($this->pdo);
            $regraPagamentoDao->excluirPorId($regraPagamentoId);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 74, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), "Exclusão da regra de pagamento de id $regraPagamentoId.");

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/regra_pagamento.php?msg=cadastrar-falha");
                exit();
            }

            $this->pdo->commit();

            header("Location: ../view/regra_pagamento.php?msg=excluir-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/regra_pagamento.php?msg=excluir-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para alterar as informações de uma regra de pagamento do sistema
     */
    public function editarPorId()
    {
        $valor = filter_input(INPUT_POST, 'valor', FILTER_SANITIZE_NUMBER_FLOAT);
        $regraPagamentoId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        try {
            $this->pdo->beginTransaction();
            $regraPagamento = new RegraPagamento($this->pdo);
            $regraPagamento
                ->setId($regraPagamentoId)
                ->setValor($valor)
                ->editar();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 74, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), "Alteração da regra de pagamento de id {$regraPagamento->getId()}.");

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/regra_pagamento.php?msg=editar-falha#mensagem-tabela");
                exit();
            }

            $this->pdo->commit();

            header("Location: ../view/regra_pagamento.php?msg=editar-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/regra_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para ativar/desativar uma regra de pagamento no sistema
     */
    public function alterarStatus()
    {
        $regraPagamentoId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!$regraPagamentoId || empty($regraPagamentoId)) {
                throw new InvalidArgumentException('O id deve ser maior ou igual a 1.', 400);
            }

            if (!$status || empty($status)) {
                throw new InvalidArgumentException('O status informado não é válido.', 400);
            }

            $descricao = "A regra de pagamento de id $regraPagamentoId foi ";

            if ($status === 'true') {
                $status = 1;
                $descricao .= 'ativada.';
            } elseif ($status === 'false') {
                $status = 0;
                $descricao .= 'desativada.';
            }

            $this->pdo->beginTransaction();
            $regraPagamentoDao = new RegraPagamentoDAO($this->pdo);
            $regraPagamentoDao->alterarStatusPorId($status, $regraPagamentoId, $status);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 74, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), "$descricao");

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/regra_pagamento.php?msg=editar-falha#mensagem-tabela");
                exit();
            }

            $this->pdo->commit();

            echo json_encode(['Sucesso']);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/regra_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }
}
