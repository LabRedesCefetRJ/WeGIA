<?php

    ini_set('display_errors',1);
    ini_set('display_startup_erros',1);
    error_reporting(E_ALL);
    extract($_REQUEST);

    session_start();
    if (!isset($_SESSION["usuario"])){
        header("Location: ../../index.php");
    }

    // Verifica Permissão do Usuário
    require_once '../permissao/permissao.php';
    permissao($_SESSION['id_pessoa'], 11, 7);
    require_once '../geral/msg.php';
    require_once '../../dao/Conexao.php';
    $pdo = Conexao::connect();


    $id = filter_input(INPUT_GET, 'id_pessoa', FILTER_VALIDATE_INT);
    $cep = trim((string) filter_input(INPUT_POST, 'cep', FILTER_UNSAFE_RAW));
    $estado = html_entity_decode(trim((string) filter_input(INPUT_POST, 'uf', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $cidade = html_entity_decode(trim((string) filter_input(INPUT_POST, 'cidade', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $bairro = html_entity_decode(trim((string) filter_input(INPUT_POST, 'bairro', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rua = html_entity_decode(trim((string) filter_input(INPUT_POST, 'rua', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $numero = filter_input(INPUT_POST, 'numero_residencia', FILTER_SANITIZE_STRING);
    $complemento = html_entity_decode(trim((string) filter_input(INPUT_POST, 'complemento', FILTER_UNSAFE_RAW)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ibge = trim((string) filter_input(INPUT_POST, 'ibge', FILTER_UNSAFE_RAW));
    $idatendido_familiares = filter_input(INPUT_GET, 'idatendido_familiares', FILTER_VALIDATE_INT);
    try {
        if ($cep !== '' && !preg_match('/^\d{5}-?\d{3}$/', $cep)) {
            throw new InvalidArgumentException('CEP inválido.', 400);
        }

        if ($cep !== '' && (empty($estado) || empty($cidade) || empty($bairro) || empty($rua) || empty($ibge))) {
            throw new InvalidArgumentException('CEP inválido.', 400);
        }

        define("ALTERAR_END", "UPDATE pessoa SET cep=:cep, estado=:estado, cidade=:cidade, bairro=:bairro, logradouro=:rua, numero_endereco=:numero, complemento=:complemento, ibge=:ibge where id_pessoa = :id"); 

        $pessoa = $pdo->prepare(ALTERAR_END);
        $pessoa->bindValue(":id", $id);
        $pessoa->bindValue(":cep", $cep);
        $pessoa->bindValue(":estado", $estado);
        $pessoa->bindValue(":cidade", $cidade);
        $pessoa->bindValue(":bairro", $bairro);
        $pessoa->bindValue(":rua", $rua);
        $pessoa->bindValue(":numero", $numero);
        $pessoa->bindValue(":complemento", $complemento);
        $pessoa->bindValue(":ibge", $ibge);
        $pessoa->execute();
        setSessionMsg('Endereço atualizado com sucesso!', 'sccs');
    } catch (PDOException $th) {
        setSessionMsg('Erro no servidor ao atualizar o endereço do familiar.', 'err');
        header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
        exit();
    } catch (Exception $e) {
        setSessionMsg($e->getMessage(), 'err');
        header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
        exit();
    }


    if (!$idatendido_familiares) {
        die('ID inválido para redirecionamento.');
    }

    header("Location: profile_familiar.php?id_dependente=$idatendido_familiares");
    exit();

?>
