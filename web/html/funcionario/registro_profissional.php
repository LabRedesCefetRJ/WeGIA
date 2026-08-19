<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
Util::definirFusoHorario();
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

require_once "../permissao/permissao.php";
permissao($_SESSION['id_pessoa'], 11, 7);

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'IdentificadorRegistroProfissionalDAO.php';

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    $dao = new IdentificadorRegistroProfissionalDAO();

    switch ($action) {
        case 'listar':
            $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário informado não é válido.');
            }
            echo json_encode($dao->listarPorIdFuncionario($idFuncionario));
            break;

        case 'adicionar':
            $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
            $idTipo = filter_input(INPUT_POST, 'id_tipo', FILTER_SANITIZE_NUMBER_INT);
            $numeroRegistro = trim((string) filter_input(INPUT_POST, 'numero_registro', FILTER_SANITIZE_SPECIAL_CHARS));
            $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS);
            $uf = ($uf === '' || $uf === null) ? null : $uf;

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário informado não é válido.');
            }
            if (!$idTipo || $idTipo < 1) {
                throw new InvalidArgumentException('Selecione um tipo de registro profissional válido.');
            }
            if ($numeroRegistro === '') {
                throw new InvalidArgumentException('Informe o número do registro.');
            }

            $dao->salvarRegistroProfissional($idFuncionario, $idTipo, $numeroRegistro, $uf);
            echo json_encode($dao->listarPorIdFuncionario($idFuncionario));
            break;

        case 'editar':
            $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
            $idRegistro = filter_input(INPUT_POST, 'id_registro', FILTER_SANITIZE_NUMBER_INT);
            $numeroRegistro = trim((string) filter_input(INPUT_POST, 'numero_registro', FILTER_SANITIZE_SPECIAL_CHARS));
            $uf = filter_input(INPUT_POST, 'uf', FILTER_SANITIZE_SPECIAL_CHARS);
            $uf = ($uf === '' || $uf === null) ? null : $uf;

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário informado não é válido.');
            }
            if (!$idRegistro || $idRegistro < 1) {
                throw new InvalidArgumentException('O id do registro informado não é válido.');
            }
            if ($numeroRegistro === '') {
                throw new InvalidArgumentException('Informe o número do registro.');
            }

            $dao->alterarRegistroPorId($idRegistro, $idFuncionario, $numeroRegistro, $uf);
            echo json_encode($dao->listarPorIdFuncionario($idFuncionario));
            break;

        case 'remover':
            $idFuncionario = filter_input(INPUT_POST, 'id_funcionario', FILTER_SANITIZE_NUMBER_INT);
            $idRegistro = filter_input(INPUT_POST, 'id_registro', FILTER_SANITIZE_NUMBER_INT);

            if (!$idFuncionario || $idFuncionario < 1) {
                throw new InvalidArgumentException('O id do funcionário informado não é válido.');
            }
            if (!$idRegistro || $idRegistro < 1) {
                throw new InvalidArgumentException('O id do registro informado não é válido.');
            }

            $dao->remover($idRegistro, $idFuncionario);
            echo json_encode($dao->listarPorIdFuncionario($idFuncionario));
            break;

        default:
            http_response_code(400);
            echo json_encode(['erro' => 'Ação não reconhecida.']);
            break;
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
