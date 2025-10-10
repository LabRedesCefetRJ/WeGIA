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

try {
    $id = filter_input(INPUT_GET, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS));
    $sobrenome = trim(filter_input(INPUT_POST, 'sobrenomeForm', FILTER_SANITIZE_SPECIAL_CHARS));
    $sexo = trim(filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS));
    $telefone = trim(filter_input(INPUT_POST, 'telefone', FILTER_UNSAFE_RAW));
    $data_nascimento = trim(filter_input(INPUT_POST, 'nascimento', FILTER_UNSAFE_RAW));
    $nome_mae = trim(filter_input(INPUT_POST, 'nome_mae', FILTER_SANITIZE_SPECIAL_CHARS));
    $nome_pai = trim(filter_input(INPUT_POST, 'nome_pai', FILTER_SANITIZE_SPECIAL_CHARS));
    $idatendido_familiares = filter_input(INPUT_GET, 'idatendido_familiares', FILTER_VALIDATE_INT);

    if ($data_nascimento) {
        require_once '../../dao/Conexao.php';
        $pdo_temp = Conexao::connect();
        
        // Buscar data de expedição atual da pessoa
        $sql_expedicao = "SELECT data_expedicao FROM pessoa WHERE id_pessoa = :id_pessoa";
        $stmt_expedicao = $pdo_temp->prepare($sql_expedicao);
        $stmt_expedicao->bindParam(':id_pessoa', $id);
        $stmt_expedicao->execute();
        $pessoa_doc = $stmt_expedicao->fetch(PDO::FETCH_ASSOC);
        
        if ($pessoa_doc && $pessoa_doc['data_expedicao']) {
            $data_nascimento_obj = new DateTime($data_nascimento);
            $data_expedicao_obj = new DateTime($pessoa_doc['data_expedicao']);
            
            if ($data_nascimento_obj >= $data_expedicao_obj) {
                throw new InvalidArgumentException('Erro: A data de nascimento não pode ser posterior ou igual à data de expedição do documento!', 400);
            }
        }
    }

    if (!$id || $id < 1) {
        throw new InvalidArgumentException('O id informado não é válido', 400);
    }

    if (!$nome || strlen($nome) < 1) {
        throw new InvalidArgumentException('O nome informado não é válido', 400);
    }

    if (!$sobrenome || strlen($sobrenome) < 1) {
        throw new InvalidArgumentException('O sobrenome informado não é válido', 400);
    }

    if (!$sexo || ($sexo != 'm' && $sexo != 'f')) {
        throw new InvalidArgumentException('O gênero informado não é válido.', 400);
    }

    //verifica se um telefone é válido
    $regexTelefone = '/^(?:\+55\s?)?\(?[1-9][0-9]\)?\s?(?:9[0-9]{4}|[2-8][0-9]{3})-?[0-9]{4}$/';

    if (!preg_match($regexTelefone, $telefone)) {
        throw new InvalidArgumentException('O telefone informado não está em um formato válido.', 400);
    }

    //verificar se é uma data válida
    $regexData = '/^(19[1-9][0-9]|20[0-9]{2}|21[0-9]{2})-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';
    if (!preg_match($regexData, $data_nascimento)) {
        throw new InvalidArgumentException('A data informada não está em um formato válido.', 400);
    }

    list($ano, $mes, $dia) = explode('-', $data_nascimento);

    if (!checkdate(intval($mes), intval($dia), intval($ano))) {
        throw new InvalidArgumentException('A data informada não é válida.', 400);
    }

    if (!$nome_pai || strlen($nome_pai) < 1) {
        throw new InvalidArgumentException('O nome do pai informado não é válido', 400);
    }

    if (!$nome_mae || strlen($nome_mae) < 1) {
        throw new InvalidArgumentException('O nome da mãe informado não é válido', 400);
    }

    if (!$idatendido_familiares || $idatendido_familiares < 1) {
        throw new InvalidArgumentException('O id do familiar informado não é válido', 400);
    }

    $sql =  "UPDATE pessoa SET nome=:nome, sobrenome=:sobrenome, sexo=:sexo, data_nascimento=:data_nascimento, telefone=:telefone, nome_mae=:nome_mae, nome_pai=:nome_pai WHERE id_pessoa = :id";

    require_once '../../dao/Conexao.php';
    $pdo = Conexao::connect();

    $pessoa = $pdo->prepare($sql);
    $pessoa->bindParam(":id", $id, PDO::PARAM_INT);
    $pessoa->bindParam(":nome", $nome, PDO::PARAM_STR);
    $pessoa->bindParam(":sobrenome", $sobrenome, PDO::PARAM_STR);
    $pessoa->bindParam(":sexo", $sexo, PDO::PARAM_STR);
    $pessoa->bindParam(":telefone", $telefone, PDO::PARAM_STR);
    $pessoa->bindParam(":data_nascimento", $data_nascimento, PDO::PARAM_STR);
    $pessoa->bindParam(":nome_mae", $nome_mae, PDO::PARAM_STR);
    $pessoa->bindParam(":nome_pai", $nome_pai, PDO::PARAM_STR);

    if (!$pessoa->execute()) {
        throw new PDOException('Falha ao executar a consulta', 500);
    }

    header("Location: profile_dependente.php?id_dependente=$idatendido_familiares");
} catch (Exception $e) {
    error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
    http_response_code($e->getCode());
    if ($e instanceof PDOException) {
        echo json_encode(['erro' => 'Erro no servidor ao editar as informações pessoais de um dependente.']);
    } else {
        echo json_encode(['erro' => $e->getMessage()]);
    }
}
