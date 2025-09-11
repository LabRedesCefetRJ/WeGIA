<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_pessoa = filter_var($_SESSION['id_pessoa'], FILTER_SANITIZE_NUMBER_INT);

if (!$id_pessoa || $id_pessoa < 1) {
    http_response_code(400);
    echo json_encode(['erro' => 'O id da pessoa informado não é válido.']);
    exit();
}

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'permissao' . DIRECTORY_SEPARATOR . 'permissao.php';
permissao($id_pessoa, 4, 5);

function montaConsultaStatus(&$consulta, $status, &$where)
{
    if ($status != 'x' && $where === false) {
        $consulta .= " WHERE s.id_sociostatus=$status";
        $where = true;
    } elseif ($status != 'x') {
        $consulta .= " AND s.id_sociostatus=$status";
    }
}

function montaConsultaTAG(&$consulta, $tag, &$where)
{
    if ($tag != 'x' && $where === false) {
        $consulta .= " WHERE s.id_sociotag=$tag";
        $where = true;
    } elseif ($tag != 'x') {
        $consulta .= " AND s.id_sociotag=$tag";
    }
}

function montaConsultaValor(&$consulta, $valor, $operador, &$where)
{
    $op = '';
    switch ($operador) {
        case "maior_q":
            $op = ">";
            break;
        case "maior_ia":
            $op = ">=";
            break;
        case "igual_a":
            $op = "=";
            break;
        case "menor_ia":
            $op = "<=";
            break;
        case "menor_q":
            $op = "<";
            break;
    }

    if (!isset($valor) || empty(trim($valor))) {
        $valor = '0';
    }

    if ($where === false) {
        $consulta .= " WHERE s.valor_periodo $op $valor";
        $where = true;
    } else {
        $consulta .= " AND s.valor_periodo $op $valor";
    }
}

function montaConsultaTipoPessoa(&$consulta, $tipoPessoa, &$where)
{
    $qtdCaracteres = 0;
    switch ($tipoPessoa) {
        case "f":
            $qtdCaracteres = 14;
            break;
        case "j":
            $qtdCaracteres = 18;
            break;
    }

    if ($qtdCaracteres === 0) {
        return;
    }

    if ($where === false) {
        $consulta .= " WHERE LENGTH(p.cpf)=$qtdCaracteres";
        $where = true;
    } else {
        $consulta .= " AND LENGTH(p.cpf)=$qtdCaracteres";
    }
}

function montaConsultaTipoSocio(&$consulta, $tipoSocio, &$where)
{
    $td = false;
    switch ($tipoSocio) {
        case "x":
            break; //Todos
        case "c":
            $td = "0,1";
            break; //Casuais
        case "b":
            $td = "6,7";
            break; //Bimestrais
        case "t":
            $td = "8,9";
            break; //Trimestrais
        case "s":
            $td = "10,11";
            break; //Semestrais
        case "m":
            $td = "2,3";
            break; //Mensais
    }

    if (!$td) {
        return;
    }

    if ($where === false) {
        $consulta .= " WHERE s.id_sociotipo IN ($td)";
        $where = true;
    } else {
        $consulta .= " AND s.id_sociotipo IN ($td)";
    }
}

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

$where = false;
$consultaBasica = "SELECT p.nome, p.telefone, p.cpf, s.valor_periodo, s.email, st.tipo, ss.status, stag.tag FROM pessoa p JOIN socio s ON (p.id_pessoa=s.id_pessoa) JOIN socio_tipo st ON (s.id_sociotipo = st.id_sociotipo) JOIN socio_status ss ON (ss.id_sociostatus = s.id_sociostatus) JOIN socio_tag stag on (stag.id_sociotag = s.id_sociotag)";
montaConsultaStatus($consultaBasica, $status, $where);
montaConsultaTAG($consultaBasica, $tag, $where);
montaConsultaValor($consultaBasica, $valor, $operador, $where);
montaConsultaTipoPessoa($consultaBasica, $tipo_pessoa, $where);
montaConsultaTipoSocio($consultaBasica, $tipo_socio, $where);
$consultaBasica .= " ORDER BY p.nome";

//echo $consultaBasica;

$query = mysqli_query($conexao, $consultaBasica);
while ($resultado = mysqli_fetch_assoc($query)) {
    $dados[] = $resultado;
}

if (!isset($dados)) {
    echo json_encode(null);
} else {
    echo json_encode($dados);
}
