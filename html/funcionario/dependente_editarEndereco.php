<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../index.php");
    exit();
}

// Verifica Permissão do Usuário
require_once '../permissao/permissao.php';
permissao($_SESSION['id_pessoa'], 11, 7);
require_once '../geral/msg.php';
require_once '../../dao/DependenteDAO.php';

try {
    $cep = trim((string) filter_input(INPUT_POST, 'cep', FILTER_UNSAFE_RAW));
    $estado = html_entity_decode(trim((string) filter_input(INPUT_POST, 'uf', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $cidade = html_entity_decode(trim((string) filter_input(INPUT_POST, 'cidade', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $bairro = html_entity_decode(trim((string) filter_input(INPUT_POST, 'bairro', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rua = html_entity_decode(trim((string) filter_input(INPUT_POST, 'rua', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $numero = filter_input(INPUT_POST, 'numero_residencia', FILTER_SANITIZE_NUMBER_INT);
    $complemento = html_entity_decode(trim((string) filter_input(INPUT_POST, 'complemento', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ibge = filter_input(INPUT_POST, 'ibge', FILTER_SANITIZE_NUMBER_INT);
    $idatendido_familiares = filter_input(INPUT_GET, 'idatendido_familiares', FILTER_SANITIZE_NUMBER_INT);

    if ($cep !== '' && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
        throw new InvalidArgumentException('O CEP informado não está em um formato válido.', 400);
    }

    if (!is_numeric($numero)) {
        $numero = null;
    }

    if ($cep !== '' && (empty($estado) || empty($cidade) || empty($bairro) || empty($rua) || empty($ibge))) {
        throw new InvalidArgumentException('Preencha todos os campos obrigatórios antes de prosseguir.', 400);
    }

    if(!$idatendido_familiares || $idatendido_familiares < 1){
        throw new InvalidArgumentException('O id do dependente informado não é válido.', 400);
    }

    $dependenteDao = new DependenteDAO();
    $dependenteDao->editarEndereco($idatendido_familiares, $cep, $estado, $cidade, $bairro, $rua, $complemento, (int) $ibge, $numero);
   
    setSessionMsg('Endereço atualizado com sucesso!', 'sccs');
    header("Location: profile_dependente.php?id_dependente=$idatendido_familiares");
    exit();
} catch (Exception $e) {
    error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
    setSessionMsg($e instanceof PDOException ? 'Erro no servidor ao atualizar o endereço do dependente.' : $e->getMessage(), 'err');
    header("Location: profile_dependente.php?id_dependente=$idatendido_familiares");
    exit();
}
