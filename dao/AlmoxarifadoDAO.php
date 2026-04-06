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
        $pdo = Conexao::connect();
        $sql = 'DELETE FROM almoxarifado WHERE id_almoxarifado = :id_almoxarifado';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_almoxarifado', $id_almoxarifado);
        $stmt->execute();
    }
    public function listarTodos()
    {
        $almoxarifados = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT id_almoxarifado, descricao_almoxarifado FROM almoxarifado WHERE ativo = 1 ORDER BY descricao_almoxarifado");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $almoxarifados[$x] = array('id_almoxarifado' => htmlspecialchars($linha['id_almoxarifado']), 'descricao_almoxarifado' => htmlspecialchars($linha['descricao_almoxarifado']));
            $x++;
        }
        
        return json_encode($almoxarifados);
    }

    public function alterarAlmoxarifado($almoxarifado)
    {
        $pdo = Conexao::connect();

        $sql = 'UPDATE almoxarifado 
        set descricao_almoxarifado = :descricao_almoxarifado 
        where id_almoxarifado = :id_almoxarifado';

        $sql = str_replace("'", "\'", $sql);

        $stmt = $pdo->prepare($sql);

        $descricao = $almoxarifado->getDescricao_almoxarifado();
        $id = $almoxarifado->getId_almoxarifado();

        $stmt->bindParam(':descricao_almoxarifado', $descricao);
        $stmt->bindParam(':id_almoxarifado', $id);
        $stmt->execute();
    }

    public function listarArquivados()
    {
        $almoxarifados = array();
        $pdo = Conexao::connect();

        $consulta = $pdo->query("
            SELECT id_almoxarifado, descricao_almoxarifado
            FROM almoxarifado
            WHERE ativo = 0
            ORDER BY descricao_almoxarifado
        ");

        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $almoxarifados[] = $linha;
        }

        return json_encode($almoxarifados);
    }

    public function arquivar($id)
    {
        if (!is_numeric($id) || $id < 1) {
            throw new InvalidArgumentException('ID de almoxarifado inválido.');
        }

        $pdo = Conexao::connect();

        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("UPDATE almoxarifado SET ativo = 0 WHERE id_almoxarifado = :id");
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            $stmt2 = $pdo->prepare("UPDATE entrada SET ativo = 0 WHERE id_almoxarifado = :id");
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();

            $stmt3 = $pdo->prepare("UPDATE saida SET ativo = 0 WHERE id_almoxarifado = :id");
            $stmt3->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt3->execute();

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function desarquivar($id)
    {
        if (!is_numeric($id) || $id < 1) {
            throw new InvalidArgumentException('ID de almoxarifado inválido.');
        }

        $pdo = Conexao::connect();

        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("UPDATE almoxarifado SET ativo = 1 WHERE id_almoxarifado = :id");
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            $stmt2 = $pdo->prepare("UPDATE entrada SET ativo = 1 WHERE id_almoxarifado = :id");
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();

            $stmt3 = $pdo->prepare("UPDATE saida SET ativo = 1 WHERE id_almoxarifado = :id");
            $stmt3->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt3->execute();

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function temVinculo($id)
    {
        $pdo = Conexao::connect();

        $sql1 = "SELECT COUNT(*) FROM almoxarife WHERE id_almoxarifado = :id";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->bindParam(':id', $id);
        $stmt1->execute();
        $temAlmoxarife = $stmt1->fetchColumn() > 0;

        $sql2 = "SELECT COUNT(*) FROM entrada WHERE id_almoxarifado = :id";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->bindParam(':id', $id);
        $stmt2->execute();
        $temEntrada = $stmt2->fetchColumn() > 0;

        $sql3 = "SELECT COUNT(*) FROM saida WHERE id_almoxarifado = :id";
        $stmt3 = $pdo->prepare($sql3);
        $stmt3->bindParam(':id', $id);
        $stmt3->execute();
        $temSaida = $stmt3->fetchColumn() > 0;

        return ($temAlmoxarife || $temEntrada || $temSaida);
    }
}
