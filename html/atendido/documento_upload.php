<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}else{
    session_regenerate_id();
}

$idPessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if(!$idPessoa || $idPessoa <1){
    http_response_code(400);
    echo json_encode(['erro' => 'O id da pessoa informado não é válido.']);
    exit();
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($idPessoa, 12, 7);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

if ($_POST) {
    require_once "../../dao/Conexao.php";

    // $idatendido, $id_docfuncional e $arquivo
    extract($_POST);

    $arquivo = $_FILES["arquivo"];
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        die("Houve um erro no upload do arquivo. Código de erro: " . $arquivo['error']);
    }

    $arquivo_nome = $arquivo["name"];
    $extensao_nome = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
    $arquivo_b64 = base64_encode(file_get_contents($arquivo['tmp_name']));

    $tipos_permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

    if (in_array($extensao_nome, $tipos_permitidos)) {
        try {
            $pdo = Conexao::connect();
            $prep = $pdo->prepare("INSERT INTO atendido_documentacao( atendido_idatendido, atendido_docs_atendidos_idatendido_docs_atendidos, data, arquivo_nome, arquivo_extensao, arquivo) 	
            VALUES ( :atendido_idatendido , :atendido_docs_atendidos_idatendido_docs_atendidos , :data, :arquivo_nome , :arquivo_extensao, :arquivo )");

            $prep->bindValue(":atendido_idatendido", $idatendido);
            $prep->bindValue(":atendido_docs_atendidos_idatendido_docs_atendidos", $id_docfuncional);
            $prep->bindValue(":arquivo_nome", $arquivo_nome);
            $prep->bindValue(":arquivo_extensao", $extensao_nome);
            $prep->bindValue(":arquivo", gzcompress($arquivo_b64));

            $dataDocumento = date('Y/m/d');
            $prep->bindValue(":data", $dataDocumento);

            $prep->execute();

            header("Location: Profile_Atendido.php?idatendido=$idatendido");
        } catch (PDOException $e) {
            Util::tratarException($e);
        }
    } else {
        http_response_code(400);
        echo json_encode(['erro' => "Tipo de arquivo não permitido. Apenas arquivos PDF, JPG, JPEG, PNG, DOC e DOCX são aceitos."]);
        exit();
    }
} else {
    header("Location: Informacao_Atendido.php");
}
