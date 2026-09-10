<?php
//Refatorar para estrutura MVC
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
Util::definirFusoHorario();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../index.php");
    exit();
}

permissao($_SESSION['id_pessoa'], 11, 3);

if ($_POST) {
    require_once "../../dao/Conexao.php";

    // $id_dependente, $id_docdependente e $arquivo
    $id_dependente = filter_input(INPUT_POST, 'id_dependente', FILTER_VALIDATE_INT);
    $id_docdependente = filter_input(INPUT_POST, 'id_docdependente', FILTER_VALIDATE_INT);
    $arquivo = $_FILES["arquivo"];

    // Sanitiza o nome do arquivo enviado: remove diretórios, caracteres de
    // controle e caracteres inseguros antes de gravar no banco de dados.
    $nome_arquivo = basename(trim((string) $arquivo["name"]));
    $nome_arquivo = preg_replace('/[[:cntrl:]]+/', '', $nome_arquivo);
    $nome_arquivo = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $nome_arquivo);

    $extensao_arquivo = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));

    // O tipo MIME informado pelo cliente não é confiável; valida o conteúdo real do arquivo.
    $mime_type = false;
    if (isset($arquivo['tmp_name']) && is_uploaded_file($arquivo['tmp_name'])) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($arquivo['tmp_name']);
    }
    $arquivo_b64 = base64_encode(file_get_contents($arquivo['tmp_name']));

    $data = date('Y-m-d H:i:s', time());
    try {
        if (!$id_dependente || $id_dependente <= 0) {
            throw new Exception("ID do dependente inválido.", 400);
        }
        if (!$id_docdependente || $id_docdependente <= 0) {
            throw new Exception("ID do documento do dependente inválido.", 400);
        }

        if ($nome_arquivo === '' || $nome_arquivo === '.' || $nome_arquivo === '..') {
            throw new Exception("Nome de arquivo inválido.", 400);
        }

        if (!in_array($extensao_arquivo, ['pdf', 'jpg', 'jpeg', 'png'])) {
            throw new Exception("Extensão de arquivo não permitida. Apenas PDF, JPG, JPEG e PNG são aceitos.", 400);
        }

        if ($mime_type !== 'application/pdf' && $mime_type !== 'image/jpeg' && $mime_type !== 'image/png') {
            throw new Exception("Tipo de arquivo não permitido. Apenas PDF, JPG, JPEG e PNG são aceitos.", 400);
        }

        $pdo = Conexao::connect();
        $prep = $pdo->prepare("INSERT INTO funcionario_dependentes_docs (id_dependente, id_docdependentes, data, extensao_arquivo, nome_arquivo, arquivo) VALUES ( :idf , :idd , :data, :ext , :n , COMPRESS(:a) )");
        $prep->bindValue(":idf", $id_dependente);
        $prep->bindValue(":idd", $id_docdependente);
        $prep->bindParam(':data', $data);
        $prep->bindValue(":ext", $extensao_arquivo);
        $prep->bindValue(":n", $nome_arquivo);
        $prep->bindValue(":a", $arquivo_b64);
        $prep->execute();

        header("Location: ./profile_dependente.php?id_dependente=$id_dependente");
    } catch (Exception $e) {
        Util::tratarException($e);
    }
} else {
    header("Location: ../informacao_funcionario.php");
}
