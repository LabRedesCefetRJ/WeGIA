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

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 74, 3, new DateTime('now', new DateTimeZone('America/Sao_Paulo')), 'Exclusão de regras de pagamento.');

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
        $valor = $_POST['valor'];
        $regraPagamentoId = $_POST['id'];

        try {
            $regraPagamento = new RegraPagamento();
            $regraPagamento
                ->setId($regraPagamentoId)
                ->setValor($valor)
                ->editar();
            header("Location: ../view/regra_pagamento.php?msg=editar-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            header("Location: ../view/regra_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para ativar/desativar uma regra de pagamento no sistema
     */
    public function alterarStatus()
    {
        $regraPagamentoId = $_POST['id'];
        $status = trim($_POST['status']);

        if (!$regraPagamentoId || empty($regraPagamentoId)) {
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
            $regraPagamentoDao = new RegraPagamentoDAO();
            $regraPagamentoDao->alterarStatusPorId($status, $regraPagamentoId, $status);
            echo json_encode(['Sucesso']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['Erro' => 'Ocorreu um problema no servidor.']);
            exit;
        }
    }
}
