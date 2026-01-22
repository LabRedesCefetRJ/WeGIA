<?php
//redirecionar o fluxo para uma controladora e apagar o arquivo
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    header("Location: ../../../index.php");
} else {
    session_regenerate_id();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($_SESSION['id_pessoa'], 4, 3);

require_once dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';

require("../conexao.php");
if (!isset($_POST) or empty($_POST)) {
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);
    $_POST = $data;
} else if (is_string($_POST)) {
    $_POST = json_decode($_POST, true);
}

if(!Csrf::validateToken($_POST['csrf_token'])){
    http_response_code(401);
    exit('Token CSRF inválido ou ausente.');
}

$cadastrado =  false;

$socio_nome = trim($_REQUEST['socio_nome']);
$pessoa = trim($_REQUEST['pessoa']);
$contribuinte = trim($_REQUEST['contribuinte']);
$status = trim($_REQUEST['status']);
$email = trim($_REQUEST['email']);
$tag = trim($_REQUEST['tag']);
$telefone = trim($_REQUEST['telefone']);
$cpf_cnpj = trim($_REQUEST['cpf_cnpj']);
$rua = trim($_REQUEST['rua']);
$numero = trim($_REQUEST['numero']);
$complemento = trim($_REQUEST['complemento']);
$bairro = trim($_REQUEST['bairro']);
$estado = trim($_REQUEST['estado']);
$cidade = trim($_REQUEST['cidade']);
$data_nasc = trim($_REQUEST['data_nasc']);
$cep = trim($_REQUEST['cep']);
$data_referencia = trim($_REQUEST['data_referencia']);
$valor_periodo = trim($_REQUEST['valor_periodo']);
$tipo_contribuicao = trim($_REQUEST['tipo_contribuicao']);

if (!$socio_nome || empty($socio_nome)) {
    http_response_code(400);
    exit('O nome de um sócio não pode ser vazio.');
}

if ($pessoa !== 'fisica' && $pessoa !== 'juridica') {
    http_response_code(400);
    exit('O tipo de pessoa informado não é válido.');
}

if (!$contribuinte || empty($contribuinte)) {
    http_response_code(400);
    exit('O tipo do contribuinte não pode ser vazio.');
}

if (!$tag || !is_numeric($tag) || $tag < 1) {
    http_response_code(400);
    exit('O id de uma tag deve ser um inteiro maior ou igual a 1.');
}

if (!$cpf_cnpj || empty($cpf_cnpj)) { //posteriormente adicionar validações de formato
    http_response_code(400);
    exit('Um cpf/cpnj não pode ser vazio.');
}

if (!$data_nasc || empty($data_nasc)) { //posteiormente adicionar validações de formato
    $data_nasc = null;
}

if (!$data_referencia || empty($data_referencia)) { //Posteriormente adicionar validações de formato
    http_response_code(400);
    exit('A data de referência não pode ser vazia.');
}

if (!$valor_periodo || !is_numeric($valor_periodo) || $valor_periodo <= 0) {
    http_response_code(400);
    exit('O valor de doação durante determinado perído deve ser um número com valor maior que 0.');
}

if (!$tipo_contribuicao || !is_numeric($tipo_contribuicao) || $tipo_contribuicao < 1) {
    http_response_code(400);
    exit('O tipo da contribuição deve ter um id maior ou igual a 1.');
}

// si = sem informação

$stmtBuscaSocio = $conexao->prepare("SELECT p.id_pessoa FROM pessoa p JOIN socio s ON(s.id_pessoa=p.id_pessoa) WHERE cpf=?");
$stmtBuscaSocio->bind_param('s', $cpf_cnpj);

if ($stmtBuscaSocio->execute()) {
    $resultado = $stmtBuscaSocio->get_result();
    if ($stmtBuscaSocio->affected_rows > 0) {
        http_response_code(400);
        echo json_encode(['erro' => 'já existe um sócio com esse CPF']);
        exit();
    }
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'erro ao buscar o sócio no banco de dados']);
    exit();
}

$stmt = $conexao->prepare("INSERT INTO pessoa (cpf, nome, telefone, data_nascimento, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param('sssssssssss', $cpf_cnpj, $socio_nome, $telefone, $data_nasc, $cep, $estado, $cidade, $bairro, $rua, $numero, $complemento);


if ($stmt->execute()) {
    $id_pessoa = mysqli_insert_id($conexao);
    switch ($pessoa) {
        case "juridica":
            if ($contribuinte == "mensal") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 23;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 43;
                } else {
                    $id_sociotipo = 3;
                }
            } else if ($contribuinte == "casual") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 21;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 41;
                } else {
                    $id_sociotipo = 1;
                }
            } else if ($contribuinte == "bimestral") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 25;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 45;
                } else {
                    $id_sociotipo = 7;
                }
            } else if ($contribuinte == "trimestral") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 27;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 47;
                } else {
                    $id_sociotipo = 9;
                }
            } else if ($contribuinte == "semestral") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 29;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 49;
                } else {
                    $id_sociotipo = 11;
                }
            }

            if ($contribuinte == null || $contribuinte == "si" || $contribuinte == "") {
                $id_sociotipo = 5;
            }
            break;

        case "fisica":
            if ($contribuinte == "mensal") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 22;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 42;
                } else {
                    $id_sociotipo = 2;
                }
            } else if ($contribuinte == "casual") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 20;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 40;
                } else {
                    $id_sociotipo = 0;
                }
            } else if ($contribuinte == "bimestral") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 24;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 44;
                } else {
                    $id_sociotipo = 6;
                }
            } else if ($contribuinte == "trimestral") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 26;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 46;
                } else {
                    $id_sociotipo = 8;
                }
            } else if ($contribuinte == "semestral") {
                if ($tipo_contribuicao == 2) {
                    $id_sociotipo = 28;
                } else if ($tipo_contribuicao == 3) {
                    $id_sociotipo = 48;
                } else {
                    $id_sociotipo = 10;
                }
            }


            if ($contribuinte == null || $contribuinte == "si" || $contribuinte == "") {
                $id_sociotipo = 4;
            }
            break;
    }

    $stmt2 = $conexao->prepare("INSERT INTO socio (id_pessoa, id_sociostatus, id_sociotipo, email, valor_periodo, data_referencia, id_sociotag) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param('iiisdss', $id_pessoa, $status, $id_sociotipo, $email, $valor_periodo, $data_referencia, $tag);
    $stmt2->execute();

    if ($stmt2->affected_rows > 0) $cadastrado = true;
}

$stmt->close();
$stmt2->close();

echo (json_encode($cadastrado));
