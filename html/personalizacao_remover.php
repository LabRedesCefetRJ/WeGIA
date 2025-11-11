<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit();
} else {
    session_regenerate_id();
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 9, 7);

try {
    require_once "../dao/Conexao.php";
    $pdo = Conexao::connect();

    $msg = '';
    $success = true;

    if (!empty($_POST['imagem']) && is_array($_POST['imagem'])) {

        // Filtra todos os valores de uma vez
        $ids = filter_input(INPUT_POST, 'imagem', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $ids = array_map('intval', $ids); // garante valores inteiros
        $ids = array_unique($ids); // evita duplicados

        if (count($ids) === 0) {
            header("Location: personalizacao_imagem.php?msg=warn&code=no_selection");
            exit;
        }

        // Inicia transação
        $pdo->beginTransaction();

        // Verifica quais imagens estão vinculadas a campos
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $sqlVinc = "
            SELECT ic.id_imagem, i.nome
            FROM tabela_imagem_campo ic
            INNER JOIN imagem i ON ic.id_imagem = i.id_imagem
            WHERE ic.id_imagem IN ($in)
        ";

        $stmt = $pdo->prepare($sqlVinc);
        $stmt->execute($ids);
        $vinculados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $idsVinculados = array_column($vinculados, 'id_imagem');
        $idsParaExcluir = array_diff($ids, $idsVinculados);

        // Se houver imagens não vinculadas → deletar todas de uma vez
        if (!empty($idsParaExcluir)) {
            $inDel = str_repeat('?,', count($idsParaExcluir) - 1) . '?';
            $sqlDel = "DELETE FROM imagem WHERE id_imagem IN ($inDel)";
            $stmtDel = $pdo->prepare($sqlDel);
            $stmtDel->execute(array_values($idsParaExcluir));
        }

        // Mensagens de retorno
        $msgVinc = array_map(function ($r) {
            return $r['nome'];
        }, $vinculados);

        // Finaliza transação
        $pdo->commit();

        if (empty($msgVinc)) {
            header("Location: personalizacao_imagem.php?msg=success&del=" . count($idsParaExcluir));
        } else {
            header(
                "Location: personalizacao_imagem.php?msg=warn&del="
                    . count($idsParaExcluir)
                    . "&locked=" . count($msgVinc)
            );
        }
    }

} catch (Exception $e) {
    require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
    Util::tratarException($e);

    if($pdo->inTransaction())
        $pdo->rollBack();

    header("Location: personalizacao_imagem.php?msg=error&code=exception");
}
