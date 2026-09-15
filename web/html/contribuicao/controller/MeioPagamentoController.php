<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'MeioPagamento.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR .  'MeioPagamentoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SistemaLog.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SistemaLogDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ConexaoDAO.php';
require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class MeioPagamentoController
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = ConexaoDAO::conectar();;
    }

    /**
     * O Mercado Pago não suporta gerar boletos com vencimento futuro em lote (limite de
     * 29 dias de validade do bolbradesco), então não pode ser usado como plataforma do
     * meio de pagamento "Carne".
     */
    private function bloquearCarneMercadoPago(string $descricao, $gatewayId): void
    {
        if (strtolower(trim($descricao)) !== 'carne') {
            return;
        }

        $stmt = $this->pdo->prepare('SELECT plataforma FROM contribuicao_gatewayPagamento WHERE id = :id');
        $stmt->bindParam(':id', $gatewayId);
        $stmt->execute();

        if ($stmt->fetchColumn() === 'MercadoPago') {
            throw new InvalidArgumentException('O Mercado Pago não é compatível com o meio de pagamento "Carne". Escolha outra plataforma.', 400);
        }
    }

    public function cadastrar()
    {
        $descricao = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $gatewayId = filter_input(INPUT_POST, 'meio-pagamento-plataforma', FILTER_SANITIZE_NUMBER_INT);
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $this->bloquearCarneMercadoPago($descricao, $gatewayId);

            $this->pdo->beginTransaction();
            $meioPagamento = new MeioPagamento($descricao, $gatewayId);
            $meioPagamento->cadastrar();

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 3, new DateTime('now', new DateTimeZone(date_default_timezone_get())), 'Cadastro de meio de pagamento.');

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/meio_pagamento.php?msg=cadastrar-falha");
                exit();
            }

            $this->pdo->commit();

            header("Location: ../view/meio_pagamento.php?msg=cadastrar-sucesso");
        } catch (Exception $e) {
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
    public function buscaTodos()
    {
        try {
            $this->pdo->beginTransaction();
            $meioPagamentoDao = new MeioPagamentoDAO();
            $meiosPagamento = $meioPagamentoDao->buscaTodos();

            if (isset($_SESSION['id_pessoa'])) {
                $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 5, new DateTime('now', new DateTimeZone(date_default_timezone_get())), 'Pesquisa de meios de pagamento.');

                $sistemaLogDao = new SistemaLogDAO($this->pdo);
                if (!$sistemaLogDao->registrar($sistemaLog)) {
                    $this->pdo->rollBack();
                    exit();
                }
            }

            $this->pdo->commit();

            return $meiosPagamento;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
        }
    }

    /**
     * Verifica o estado de atividade do meio de pagamento no banco de dados
     */
    public function verificarStatus(string $meioPagamento, bool $status): bool
    {
        $meioPagamentoDao = new MeioPagamentoDAO();
        $meioPagamento = $meioPagamentoDao->buscarPorNome($meioPagamento);

        if (is_null($meioPagamento)) {
            return false;
        }

        if ($meioPagamento->getStatus() === 0) {
            return false;
        }

        return true;
    }

    /**
     * Realiza os procedimentos necessários para remover um meio de pagamento do sistema.
     */
    public function excluirPorId()
    {
        $meioPagamentoId = filter_input(INPUT_POST, 'meio-pagamento-id', FILTER_SANITIZE_NUMBER_INT);

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            if (!$meioPagamentoId || empty($meioPagamentoId) || $meioPagamentoId < 1) {
                //parar operação
                header("Location: ../view/meio_pagamento.php?msg=excluir-falha#mensagem-tabela");
                exit();
            }

            $this->pdo->beginTransaction();
            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamentoDao->excluirPorId($meioPagamentoId);

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 3, new DateTime('now', new DateTimeZone(date_default_timezone_get())), "Exclusão do meio de pagamento de id $meioPagamentoId.");

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/meio_pagamento.php?msg=excluir-falha#mensagem-tabela");
                exit();
            }

            $this->pdo->commit();

            header("Location: ../view/meio_pagamento.php?msg=excluir-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/meio_pagamento.php?msg=excluir-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para alterar as informações de um meio de pagamento do sistema
     */
    public function editarPorId()
    {
        $descricao = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
        $gatewayId = filter_input(INPUT_POST, 'plataforma', FILTER_SANITIZE_SPECIAL_CHARS);
        $meioPagamentoId = filter_input(INPUT_POST, 'id');

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $this->bloquearCarneMercadoPago($descricao, $gatewayId);

            $this->pdo->beginTransaction();
            $meioPagamento = new MeioPagamento($descricao, $gatewayId);
            $meioPagamento->setId($meioPagamentoId);
            $meioPagamento->editar();

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 3, new DateTime('now', new DateTimeZone(date_default_timezone_get())), "Alteração do meio de pagamento de id {$meioPagamento->getId()}.");

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/meio_pagamento.php?msg=editar-falha#mensagem-tabela");
                exit();
            }

            $this->pdo->commit();
            header("Location: ../view/meio_pagamento.php?msg=editar-sucesso#mensagem-tabela");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/meio_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }

    /**
     * Realiza os procedimentos necessários para ativar/desativar um meio de pagamento no sistema
     */
    public function alterarStatus()
    {
        $meioPagamentoId = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);
            
            if (!$meioPagamentoId || empty($meioPagamentoId)) {
                throw new InvalidArgumentException('O id deve ser maior ou igual a 1.', 400);
            }

            if (!$status || empty($status)) {
                throw new InvalidArgumentException('O status informado não é válido.', 400);
            }

            $descricao = "O meio de pagamento de id $meioPagamentoId foi ";

            if ($status === 'true') {
                $status = 1;
                $descricao .= 'ativado.';
            } elseif ($status === 'false') {
                $status = 0;
                $descricao .= 'desativado.';
            }

            if ($status === 1) {
                $stmt = $this->pdo->prepare(
                    'SELECT cmp.meio, cgp.plataforma FROM contribuicao_meioPagamento cmp
                    JOIN contribuicao_gatewayPagamento cgp ON cgp.id = cmp.id_plataforma
                    WHERE cmp.id = :id'
                );
                $stmt->bindParam(':id', $meioPagamentoId);
                $stmt->execute();
                $meioPagamentoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($meioPagamentoAtual && strtolower($meioPagamentoAtual['meio']) === 'carne' && $meioPagamentoAtual['plataforma'] === 'MercadoPago') {
                    throw new InvalidArgumentException('O Mercado Pago não é compatível com o meio de pagamento "Carne". Não é possível reativar.', 400);
                }
            }

            $this->pdo->beginTransaction();
            $meioPagamentoDao = new MeioPagamentoDAO($this->pdo);
            $meioPagamentoDao->alterarStatusPorId($status, $meioPagamentoId);

            $sistemaLog = new SistemaLog($_SESSION['id_pessoa'], 73, 3, new DateTime('now', new DateTimeZone(date_default_timezone_get())), "$descricao");

            $sistemaLogDao = new SistemaLogDAO($this->pdo);
            if (!$sistemaLogDao->registrar($sistemaLog)) {
                $this->pdo->rollBack();
                header("Location: ../view/meio_pagamento.php?msg=editar-falha#mensagem-tabela");
                exit();
            }

            $this->pdo->commit();

            echo json_encode(['Sucesso']);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            Util::tratarException($e);
            header("Location: ../view/meio_pagamento.php?msg=editar-falha#mensagem-tabela");
        }
    }
}
