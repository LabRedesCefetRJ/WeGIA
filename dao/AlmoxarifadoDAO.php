<?php
require_once ROOT . '/classes/Almoxarifado.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class AlmoxarifadoDAO
{
    public function incluir($almoxarifado)
    {
        try {
            $pdo = Conexao::connect();

            $sql = 'INSERT almoxarifado(descricao_almoxarifado) VALUES(:descricao_almoxarifado)';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $pdo->prepare($sql);

            $descricao_almoxarifado = $almoxarifado->getDescricao_almoxarifado();

            $stmt->bindParam(':descricao_almoxarifado', $descricao_almoxarifado);

            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela almoxarifado = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarUm($id_almoxarifado)
    {
        try {
            $pdo = Conexao::connect();
            $sql = "SELECT id_almoxarifado, descricao_almoxarifado  FROM almoxarifado WHERE id_almoxarifado = :id_almoxarifado";
            $consulta = $pdo->prepare($sql);
            $consulta->execute(array(
                'id_almoxarifado' => $id_almoxarifado,
            ));
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
            try {
                $almoxarifado = new Almoxarifado($resultado['descricao_almoxarifado']);
                $almoxarifado->setId_almoxarifado(intval($resultado['id_almoxarifado']));
                return $almoxarifado;
            } catch (InvalidArgumentException $e) {
                exit('Erro ao listar um almoxarifado: ' . $e->getMessage());
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function excluir($id_almoxarifado)
    {
        try {
            $pdo = Conexao::connect();
            $sql = 'DELETE FROM almoxarifado WHERE id_almoxarifado = :id_almoxarifado';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_almoxarifado', $id_almoxarifado);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela almoxarifado = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarTodos()
    {
        $almoxarifados = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT id_almoxarifado, descricao_almoxarifado FROM almoxarifado");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $almoxarifados[$x] = array('id_almoxarifado' => htmlspecialchars($linha['id_almoxarifado']), 'descricao_almoxarifado' => htmlspecialchars($linha['descricao_almoxarifado']));
            $x++;
        }

        return json_encode($almoxarifados);
    }
}
