<?php

session_start();
if (!isset($_SESSION["usuario"])){
    header("Location: ../index.php");
}

if ($_POST){
    require_once "../../dao/Conexao.php";

    // $id_dependente, $id_docdependente e $arquivo
    extract($_POST);

    // A tabela funcioanrio_docs requer id_dependente, id_docdependente, extensao_arquivo, nome_arquivo e arquivo
    $arquivo = $_FILES["arquivo"];
    $nome_arquivo = $arquivo["name"];
    $extensao_arquivo = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
    $arquivo_b64 = base64_encode(file_get_contents($arquivo['tmp_name']));

    // Tipos de arquivos permitidos
    $tipos_permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    // Verifica se a extensão do arquivo é permitida
    if (in_array($extensao_arquivo, $tipos_permitidos)) {
        try {
            $pdo = Conexao::connect();
            $prep = $pdo->prepare("INSERT INTO funcionario_dependentes_docs (id_dependente, id_docdependentes, extensao_arquivo, nome_arquivo, arquivo) 
            VALUES ( :idf , :idd , :ext , :n , COMPRESS(:a) )");

            $prep->bindValue(":idf", $id_dependente);
            $prep->bindValue(":idd", $id_docdependente);
            $prep->bindValue(":ext", $extensao_arquivo);
            $prep->bindValue(":n", $nome_arquivo);
            $prep->bindValue(":a", $arquivo_b64);

            $prep->execute();
            
            header("Location: ../profile_dependente.php?id_dependente=$id_dependente");
        } catch (PDOException $e) {
            echo("Houve um erro ao realizar o upload do documento:<br><br>$e");
        }
    } else {
        echo "Tipo de arquivo não permitido. Apenas arquivos PDF, JPG, JPEG, PNG, DOC e DOCX são aceitos.";
    }
}else {
    header("Location: ../informacao_funcionario.php");
}


