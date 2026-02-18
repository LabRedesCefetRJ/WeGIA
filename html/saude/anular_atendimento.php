<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"]) || !isset($_SESSION['id_pessoa'])) {
    header("Location: ../../index.php");
    exit();
}

session_regenerate_id();

require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 52, 7);

require_once "../../dao/Conexao.php";

$idFichamedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_VALIDATE_INT);
$idAtendimento = filter_input(INPUT_POST, 'id_atendimento', FILTER_VALIDATE_INT);
$motivoAnulacao = trim((string)($_POST['motivo_anulacao'] ?? ''));

if (!$idFichamedica || $idFichamedica < 1 || !$idAtendimento || $idAtendimento < 1) {
    $_SESSION['msg_e'] = "Não foi possível anular o atendimento. Parâmetros inválidos.";
    header("Location: profile_paciente.php?id_fichamedica=" . urlencode((string)$idFichamedica));
    exit();
}

if ($motivoAnulacao === '') {
    $_SESSION['msg_e'] = "Informe o motivo da anulação.";
    header("Location: profile_paciente.php?id_fichamedica=" . urlencode((string)$idFichamedica));
    exit();
}

if (strlen($motivoAnulacao) > 255) {
    $_SESSION['msg_e'] = "O motivo da anulação deve ter no máximo 255 caracteres.";
    header("Location: profile_paciente.php?id_fichamedica=" . urlencode((string)$idFichamedica));
    exit();
}

$pdo = null;

try {
    $pdo = Conexao::connect();
    $pdo->beginTransaction();

    $stmtFuncionario = $pdo->prepare("SELECT id_funcionario FROM funcionario WHERE id_pessoa = :id_pessoa LIMIT 1");
    $stmtFuncionario->bindValue(':id_pessoa', $_SESSION['id_pessoa'], PDO::PARAM_INT);
    $stmtFuncionario->execute();
    $idFuncionario = (int)$stmtFuncionario->fetchColumn();

    if ($idFuncionario < 1) {
        throw new RuntimeException("Funcionário responsável não encontrado.");
    }

    $stmtAtendimento = $pdo->prepare("SELECT anulado FROM saude_atendimento WHERE id_atendimento = :id_atendimento AND id_fichamedica = :id_fichamedica LIMIT 1 FOR UPDATE");
    $stmtAtendimento->bindValue(':id_atendimento', $idAtendimento, PDO::PARAM_INT);
    $stmtAtendimento->bindValue(':id_fichamedica', $idFichamedica, PDO::PARAM_INT);
    $stmtAtendimento->execute();
    $atendimento = $stmtAtendimento->fetch(PDO::FETCH_ASSOC);

    if (!$atendimento) {
        throw new RuntimeException("Atendimento não encontrado para esta ficha.");
    }

    if ((int)($atendimento['anulado'] ?? 0) === 1) {
        $pdo->commit();
        $_SESSION['msg_e'] = "Esse atendimento já está anulado.";
        header("Location: profile_paciente.php?id_fichamedica=" . urlencode((string)$idFichamedica));
        exit();
    }

    $stmtAnulaAtendimento = $pdo->prepare("UPDATE saude_atendimento
        SET anulado = 1,
            data_anulacao = NOW(),
            motivo_anulacao = :motivo,
            id_funcionario_anulacao = :id_funcionario
        WHERE id_atendimento = :id_atendimento
          AND id_fichamedica = :id_fichamedica
          AND anulado = 0");
    $stmtAnulaAtendimento->bindValue(':id_atendimento', $idAtendimento, PDO::PARAM_INT);
    $stmtAnulaAtendimento->bindValue(':id_fichamedica', $idFichamedica, PDO::PARAM_INT);
    $stmtAnulaAtendimento->bindValue(':id_funcionario', $idFuncionario, PDO::PARAM_INT);
    $stmtAnulaAtendimento->bindValue(':motivo', $motivoAnulacao, PDO::PARAM_STR);
    $stmtAnulaAtendimento->execute();

    if ($stmtAnulaAtendimento->rowCount() < 1) {
        throw new RuntimeException("Não foi possível atualizar o atendimento.");
    }

    $stmtStatusCancelado = $pdo->prepare("SELECT idsaude_medicacao_status
        FROM saude_medicacao_status
        WHERE LOWER(TRIM(descricao)) = 'cancelado'
        LIMIT 1");
    $stmtStatusCancelado->execute();
    $idStatusCancelado = (int)$stmtStatusCancelado->fetchColumn();

    if ($idStatusCancelado < 1) {
        throw new RuntimeException("Status de medicação 'Cancelado' não encontrado.");
    }

    $stmtAtualizaMedicacoes = $pdo->prepare("UPDATE saude_medicacao
        SET saude_medicacao_status_idsaude_medicacao_status = :id_status_cancelado
        WHERE id_atendimento = :id_atendimento");
    $stmtAtualizaMedicacoes->bindValue(':id_status_cancelado', $idStatusCancelado, PDO::PARAM_INT);
    $stmtAtualizaMedicacoes->bindValue(':id_atendimento', $idAtendimento, PDO::PARAM_INT);
    $stmtAtualizaMedicacoes->execute();

    $pdo->commit();

    $_SESSION['msg'] = "Atendimento anulado com sucesso.";
    header("Location: profile_paciente.php?id_fichamedica=" . urlencode((string)$idFichamedica));
    exit();
} catch (Exception $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['msg_e'] = "Não foi possível anular o atendimento. Tente novamente.";
    header("Location: profile_paciente.php?id_fichamedica=" . urlencode((string)$idFichamedica));
    exit();
}
