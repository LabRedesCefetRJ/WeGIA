<?php
require("../conexao.php");
if (!isset($_POST) or empty($_POST)) {
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);
    $_POST = $data;
} else if (is_string($_POST)) {
    $_POST = json_decode($_POST, true);
}
$cadastrado =  false;
extract($_REQUEST);
if (!isset($data_nasc)) {
    $data_nasc = null;
}

if (!isset($contribuinte)) {
    $contribuinte = null;
}

if (!isset($data_referencia) or ($data_referencia == null) or ($data_referencia == "") or empty($data_referencia) or ($data_referencia == "imp")) {
    $data_referencia = null;
} else $data_referencia = $data_referencia;

if (!isset($data_nasc) or ($data_nasc == null) or ($data_nasc == "") or empty($data_nasc) or ($data_nasc == "imp")) {
    $data_nasc = null;
} else $data_nasc = $data_nasc;


if (!isset($valor_periodo) or ($valor_periodo == null) or ($valor_periodo == "") or empty($valor_periodo) or ($valor_periodo == "imp")) {
    $valor_periodo = null;
} else $valor_periodo = $valor_periodo;

if (!isset($tag) or ($tag == null) or ($tag == "none")) {
    $tag = null;
}

$sqlBuscaIdPessoa = "SELECT id_pessoa FROM socio WHERE id_socio = ?";

$stmt = mysqli_prepare($conexao, $sqlBuscaIdPessoa);
$stmt->bind_param('s', $id_socio);

if ($stmt->execute()) {
    $resultado = $stmt->get_result();
    if ($resultado) {
        $id_pessoa = $resultado->fetch_assoc()['id_pessoa'];
    }
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro no servidor']);
    exit();
}

$sqlUpdatePessoa = "UPDATE pessoa 
                    SET cpf = ?, 
                        nome = ?, 
                        telefone = ?, 
                        data_nascimento = ?, 
                        cep = ?, 
                        estado = ?, 
                        cidade = ?, 
                        bairro = ?, 
                        logradouro = ?, 
                        numero_endereco = ?, 
                        complemento = ? 
                    WHERE id_pessoa = ?";

$stmt = mysqli_prepare($conexao, $sqlUpdatePessoa);

//sanitização das entradas
$cpf_cnpj = filter_var($cpf_cnpj,FILTER_SANITIZE_STRING);
$socio_nome = filter_var($socio_nome, FILTER_SANITIZE_STRING);
$telefone = filter_var($telefone, FILTER_SANITIZE_STRING);
//$data_nasc = filter_var($data_nasc, FILTER_SANITIZE_STRING);
$cep = filter_var($cep, FILTER_SANITIZE_STRING);
$estado = filter_var($estado, FILTER_SANITIZE_STRING);
$cidade = filter_var($cidade, FILTER_SANITIZE_STRING);
$bairro = filter_var($bairro, FILTER_SANITIZE_STRING);
$rua = filter_var($rua, FILTER_SANITIZE_STRING);
$numero = filter_var($numero, FILTER_SANITIZE_STRING);
$complemento = filter_var($complemento, FILTER_SANITIZE_STRING);
$id_pessoa = filter_var($id_pessoa, FILTER_SANITIZE_NUMBER_INT);

if ($stmt) {
    // Bind dos parâmetros (tipos: 's' para string, 'i' para inteiro, 'd' para float/double)
    $stmt->bind_param(
        'sssssssssssi',
        $cpf_cnpj,
        $socio_nome,
        $telefone,
        $data_nasc,
        $cep,
        $estado,
        $cidade,
        $bairro,
        $rua,
        $numero,
        $complemento,
        $id_pessoa
    );

    // Executa o statement
    if ($stmt->execute()) {
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
        $sqlUpdateSocio = "UPDATE socio 
                   SET id_sociostatus = ?, 
                       id_sociotipo = ?, 
                       email = ?, 
                       data_referencia = ?, 
                       valor_periodo = ?, 
                       id_sociotag = ? 
                   WHERE id_socio = ?";

        $stmt = mysqli_prepare($conexao, $sqlUpdateSocio);

        $status = filter_var($status, FILTER_SANITIZE_STRING);         // String (id_sociostatus)
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);          // String (email)
        $data_referencia = filter_var($data_referencia, FILTER_SANITIZE_STRING); // String (data_referencia)
        $valor_periodo = filter_var($valor_periodo, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Double (valor_periodo)
        $tag = filter_var($tag, FILTER_SANITIZE_NUMBER_INT);            // Inteiro (id_sociotag)
        $id_socio = filter_var($id_socio, FILTER_SANITIZE_NUMBER_INT);       // Inteiro (id_socio)

        if ($stmt) {
            // Bind dos parâmetros
            $stmt->bind_param(
                'sissdii',
                $status,         // String (id_sociostatus)
                $id_sociotipo,   // Inteiro (id_sociotipo)
                $email,          // String (email)
                $data_referencia, // String (data_referencia)
                $valor_periodo,  // Double (valor_periodo)
                $tag,            // Inteiro (id_sociotag)
                $id_socio        // Inteiro (id_socio)
            );

            // Executa o statement
            if ($stmt->execute()) {
                $cadastrado = true;
            } 
        }
    }

    // Fecha o statement
    $stmt->close();
}

echo json_encode($cadastrado);
