<?php
require_once '../classes/Cargo.php';
require_once 'Conexao.php';
require_once '../Functions/funcoes.php';

class CargoDAO
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = Conexao::connect();
        } catch (PDOException $e) {
            echo 'Erro ao instanciar objeto do tipo CargoDAO: ' . $e->getMessage();
        }
    }

    public function incluir(Cargo $cargo)
    {
        try {
            $sql = 'INSERT cargo(cargo) VALUES(:cargo)';
            $stmt = $this->pdo->prepare($sql);
            $cargo = $cargo->getCargo();
            $stmt->bindParam(':cargo', $cargo);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela cargo = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarUm($id_cargo)
    {
        try {
            $sql = "SELECT id_cargo, cargo  FROM cargo WHERE id_cargo = :id_cargo";
            $consulta = $this->pdo->prepare($sql);
            $consulta->execute(array(
                'id_cargo' => $id_cargo,
            ));
            try {
                $linha = $consulta->fetch(PDO::FETCH_ASSOC);
                $cargo = new Cargo($linha['cargo'], $linha['id_cargo']);
                return $cargo;
            } catch (InvalidArgumentException $e) {
                exit('Ocorreu um erro ao tentar listar o cargo solicitado: ' . $e->getMessage());
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }
    public function excluir($id_cargo)
    {
        try {
            $sql = 'DELETE FROM cargo WHERE id_cargo = :id_cargo';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_cargo', $id_cargo);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela cargo = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarTodos()
    {
        try {
            $cargos = array();
            $consulta = $this->pdo->query("SELECT id_cargo, cargo FROM cargo");
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            if ($resultados) {
                foreach ($resultados as $resultado) {
                    $cargo = new Cargo($resultado['cargo'], $resultado['id_cargo']);
                    $cargos[] = $cargo;
                }
            }
            return $cargos;
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
    }

    public function listarRecursos(int $cargo): array
    {
        $sql = 'SELECT id_recurso FROM permissao WHERE id_cargo=:cargo';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':cargo', $cargo);

        $stmt->execute();

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recursos = [];

        foreach ($resultados as $resultado) {
            $recursos[] = $resultado['id_recurso'];
        }

        return $recursos;
    }
}
