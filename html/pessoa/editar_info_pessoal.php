<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
} else {
    session_regenerate_id();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 1, 3);

require_once "../../dao/Conexao.php";
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

extract($_POST);

if ($action == "mudarInfoPessoal") {
    $id_pessoa = $_POST['id_pessoa'];
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $telefone = $_POST['telefone'];
    $sexo = $_POST['sexo'];
    $data_nascimento = $_POST['data_nascimento'];
    $nome_pai = $_POST['nome_pai'];
    $nome_mae = $_POST['nome_mae'];

    try {
        $sql = "UPDATE pessoa SET nome = :nome, sobrenome = :sobrenome, telefone = :telefone, sexo = :sexo, nome_pai = :nome_pai, nome_mae = :nome_mae, data_nascimento = :data_nascimento WHERE id_pessoa = :id_pessoa";

        $pdo = Conexao::connect();

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_pessoa', $id_pessoa);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':sobrenome', $sobrenome);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':data_nascimento', $data_nascimento);
        $stmt->bindParam(':nome_pai', $nome_pai);
        $stmt->bindParam(':nome_mae', $nome_mae);

        $stmt->execute();
        echo json_encode("Dados atualizados com sucesso");
    } catch (PDOException $e) {
        Util::tratarException($e);
    }
}
