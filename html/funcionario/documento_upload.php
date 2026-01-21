<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
}
if ($_POST) {
    require_once "../../dao/Conexao.php";
    // $id_funcionario, $id_docfuncional e $arquivo
    extract($_POST);
    // A tabela funcioanrio_docs requer id_funcionario, id_docfuncional, extensao_arquivo, nome_arquivo e arquivo
    $arquivo = $_FILES["arquivo"];
    $nome_arquivo = $arquivo["name"];
    $extensao_arquivo = explode(".", $arquivo["name"])[1];
    $arquivo_b64 = base64_encode(file_get_contents($arquivo['tmp_name']));
    try {
        $pdo = Conexao::connect();
        $prep = $pdo->prepare("INSERT INTO funcionario_docs (id_funcionario, id_docfuncional, extensao_arquivo, nome_arquivo, arquivo) VALUES ( :idf , :idd , :ext , :n , :a )");
        $prep->bindValue(":idf", $id_funcionario);
        $prep->bindValue(":idd", $id_docfuncional);
        $prep->bindValue(":ext", $extensao_arquivo);
        $prep->bindValue(":n", $nome_arquivo);
        $prep->bindValue(":a", gzcompress($arquivo_b64));
        $prep->execute();
        header("Location: ../funcionario/profile_funcionario.php?id_funcionario=$id_funcionario");
    } catch (PDOException $e) {
        echo ("Houve um erro ao realizar o upload do documento:<br><br>$e");
    }
} else {
    header("Location: ../funcionario/informacao_funcionario.php");
}
