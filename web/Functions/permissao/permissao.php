<?php
require_once ROOT . '/dao/Conexao.php';
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';

function getFuncionario($id_pessoa)
{
    $pdo = Conexao::connect();
    $stmt = $pdo->prepare("SELECT id_funcionario FROM funcionario WHERE id_pessoa = :id_pessoa;");
    $stmt->bindValue(':id_pessoa', $id_pessoa, PDO::PARAM_INT);
    $stmt->execute();
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) $funcionario['id_funcionario'];
}

function getPermissao($id_cargo, $id_recurso)
{
    $pdo = Conexao::connect();
    $stmt = $pdo->prepare("SELECT id_acao FROM permissao WHERE id_cargo = :id_cargo AND id_recurso = :id_recurso;");
    $stmt->bindValue(':id_cargo', $id_cargo, PDO::PARAM_INT);
    $stmt->bindValue(':id_recurso', $id_recurso, PDO::PARAM_INT);
    $stmt->execute();
    $permissao = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) $permissao['id_acao'];
}

function isAlmoxarife($id_pessoa, $id_almoxarifado)
{
    if ($id_almoxarifado) {
        $id_funcionario = getFuncionario($id_pessoa);
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare("SELECT * FROM almoxarife WHERE id_funcionario = :id_funcionario AND id_almoxarifado = :id_almoxarifado;");
        $stmt->bindValue(':id_funcionario', $id_funcionario, PDO::PARAM_INT);
        $stmt->bindValue(':id_almoxarifado', $id_almoxarifado, PDO::PARAM_INT);
        $stmt->execute();
        $almoxarifados = $stmt->fetch(PDO::FETCH_ASSOC);
        return !!$almoxarifados;
    } else {
        return true;
    }
}

function permissaoUsuario($id_pessoa, $id_recurso)
{
    $pdo = Conexao::connect();
    $pessoaDAO = new PessoaDAO();
    
    $id_cargo = $pessoaDAO->getCargoPorPessoa($id_pessoa);

    $sql = "SELECT p.id_acao 
        FROM permissao p 
        WHERE p.id_cargo = :ID_CARGO
        WHERE p.id_recurso = :ID_RECURSO;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":ID_CARGO", $id_cargo, PDO::PARAM_INT);
    $stmt->bindParam(":ID_RECURSO", $id_recurso, PDO::PARAM_INT);
    $stmt->execute();
    $permissao = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) $permissao['id_acao'];
}

function filtrarAlmoxarifado($id_pessoa, $estoque_JSON)
{
    $estoque = json_decode($estoque_JSON);
    $lista_filtrada = array();
    foreach ($estoque as $key => $item) {
        if (isAlmoxarife($id_pessoa, $item->id_almoxarifado)) {
            array_push($lista_filtrada, $item);
        }
    }
    return json_encode($lista_filtrada);
}
?>