<?php
require_once ROOT . '/classes/Almoxarifado.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class AlmoxarifadoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function incluir($almoxarifado)
    {
        try {
            $sql = 'INSERT almoxarifado(descricao_almoxarifado) VALUES(:descricao_almoxarifado)';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $this->pdo->prepare($sql);

            $descricao_almoxarifado = $almoxarifado->getDescricao_almoxarifado();

            $stmt->bindParam(':descricao_almoxarifado', $descricao_almoxarifado);

            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela almoxarifado = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function excluir($id_almoxarifado)
    {
        $sql = 'DELETE FROM almoxarifado WHERE id_almoxarifado = :id_almoxarifado';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_almoxarifado', $id_almoxarifado);
        $stmt->execute();
    }
    public function listarTodos()
    {
        $almoxarifados = array();
        $consulta = $this->pdo->query("SELECT id_almoxarifado, descricao_almoxarifado FROM almoxarifado WHERE ativo = 1 ORDER BY descricao_almoxarifado");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $almoxarifados[$x] = array('id_almoxarifado' => htmlspecialchars($linha['id_almoxarifado']), 'descricao_almoxarifado' => htmlspecialchars($linha['descricao_almoxarifado']));
            $x++;
        }
        
        return json_encode($almoxarifados);
    }

    public function alterarAlmoxarifado($almoxarifado)
    {
        $sql = 'UPDATE almoxarifado 
        set descricao_almoxarifado = :descricao_almoxarifado 
        where id_almoxarifado = :id_almoxarifado';

        $sql = str_replace("'", "\'", $sql);

        $stmt = $this->pdo->prepare($sql);

        $descricao = $almoxarifado->getDescricao_almoxarifado();
        $id = $almoxarifado->getId_almoxarifado();

        $stmt->bindParam(':descricao_almoxarifado', $descricao);
        $stmt->bindParam(':id_almoxarifado', $id);
        $stmt->execute();
    }

    public function listarUm($id_almoxarifado)
    {
        try {
            $sql = "SELECT id_almoxarifado, descricao_almoxarifado  FROM almoxarifado WHERE id_almoxarifado = :id_almoxarifado";
            $consulta = $this->pdo->prepare($sql);
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

    public function listarArquivados()
    {
        $almoxarifados = array();

        $consulta = $this->pdo->query("
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

        try {
            $this->pdo->beginTransaction();

            $stmt1 = $this->pdo->prepare("UPDATE almoxarifado SET ativo = 0 WHERE id_almoxarifado = :id");
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            $stmt2 = $this->pdo->prepare("UPDATE entrada SET ativo = 0 WHERE id_almoxarifado = :id");
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();

            $stmt3 = $this->pdo->prepare("UPDATE saida SET ativo = 0 WHERE id_almoxarifado = :id");
            $stmt3->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt3->execute();

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function desarquivar($id)
    {
        if (!is_numeric($id) || $id < 1) {
            throw new InvalidArgumentException('ID de almoxarifado inválido.');
        }

        try {
            $this->pdo->beginTransaction();

            $stmt1 = $this->pdo->prepare("UPDATE almoxarifado SET ativo = 1 WHERE id_almoxarifado = :id");
            $stmt1->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt1->execute();

            $stmt2 = $this->pdo->prepare("UPDATE entrada SET ativo = 1 WHERE id_almoxarifado = :id");
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt2->execute();

            $stmt3 = $this->pdo->prepare("UPDATE saida SET ativo = 1 WHERE id_almoxarifado = :id");
            $stmt3->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt3->execute();

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
