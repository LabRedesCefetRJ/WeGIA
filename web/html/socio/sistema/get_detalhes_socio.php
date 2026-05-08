<?php
session_start();
if (!isset($_SESSION['usuario'])) die("Você não está logado(a).");

require_once '../../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 4, 3);


require("../conexao.php");
if (!isset($_POST) or empty($_POST)) {
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);
    $_POST = $data;
} else if (is_string($_POST)) {
    $_POST = json_decode($_POST, true);
}
$conexao->set_charset("utf8");
extract($_REQUEST);

// Segundo statement
$sql2 = "SELECT p.nome, p.cpf, p.data_nascimento, p.cep, p.logradouro, p.numero_endereco, p.complemento, p.bairro, p.estado, p.cidade, s.email, p.telefone, st.tipo, ss.status, s.data_referencia, s.valor_periodo
FROM pessoa as p
JOIN socio s ON(p.id_pessoa=s.id_pessoa)
JOIN socio_tipo st ON(st.id_sociotipo=s.id_sociotipo)
JOIN socio_status ss ON(ss.id_sociostatus=s.id_sociostatus)
WHERE s.id_socio=?";

$stmt2 = mysqli_prepare($conexao, $sql2);

mysqli_stmt_bind_param($stmt2, 'i', $id_socio);

mysqli_stmt_execute($stmt2);

// Obter o resultado do statement
$result2 = mysqli_stmt_get_result($stmt2);

$resultado = mysqli_fetch_assoc($result2);

if ($resultado) {
    $resultado['tags'] = [];

    $sqlTags = "SELECT st.id_sociotag, st.tag
    FROM socio_has_tag sht
    JOIN socio_tag st ON st.id_sociotag = sht.id_sociotag
    WHERE sht.id_socio = ?
    ORDER BY st.tag ASC";

    $stmtTags = mysqli_prepare($conexao, $sqlTags);
    mysqli_stmt_bind_param($stmtTags, 'i', $id_socio);
    mysqli_stmt_execute($stmtTags);
    $resultTags = mysqli_stmt_get_result($stmtTags);

    while ($tag = mysqli_fetch_assoc($resultTags)) {
        $resultado['tags'][] = $tag;
    }

    mysqli_stmt_close($stmtTags);

    $resultado['tags_texto'] = implode(', ', array_map(static function ($tag) {
        return $tag['tag'];
    }, $resultado['tags']));
}

// Fechar o segundo statement
mysqli_stmt_close($stmt2);

// Fechar a conexão
mysqli_close($conexao);

echo json_encode($resultado);
