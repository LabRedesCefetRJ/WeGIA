<?php
require_once ROOT . "/dao/Conexao.php";

class ProjetoDAO
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = Conexao::connect();
        } catch (Exception $e) {
            throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public function adicionarProjeto($nome, $descricao, $id_tipo, $id_local, $id_status, $data_inicio, $data_fim)
    {
        try {
            $sql = "INSERT INTO projeto (nome, descricao, id_tipo, id_local, id_status, data_inicio, data_fim) 
                    VALUES (:nome, :descricao, :id_tipo, :id_local, :id_status, :data_inicio, :data_fim)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':id_tipo', $id_tipo, PDO::PARAM_INT);
            $stmt->bindValue(':id_local', $id_local, PDO::PARAM_INT);
            $stmt->bindValue(':id_status', $id_status, PDO::PARAM_INT);
            $stmt->bindValue(':data_inicio', $data_inicio);

            if (empty($data_fim) || $data_fim === '' || $data_fim === null) {
                $stmt->bindValue(':data_fim', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':data_fim', $data_fim);
            }

            $resultado = $stmt->execute();

            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro SQL: " . $errorInfo[2]);
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao inserir projeto: " . $e->getMessage());
        }
    }

    public function listarTodos()
    {
        try {
            $sql = "SELECT p.id_projeto, p.nome, p.descricao, 
                       p.id_status,
                       pt.descricao as tipo, 
                       pl.nome as local, 
                       ps.descricao as status, 
                       p.data_inicio, p.data_fim
                FROM projeto p
                INNER JOIN projeto_tipo pt ON p.id_tipo = pt.id_tipo
                INNER JOIN projeto_local pl ON p.id_local = pl.id_local
                INNER JOIN projeto_status ps ON p.id_status = ps.id_status
                ORDER BY p.data_inicio DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erro ao listar projetos: " . $e->getMessage());
            return [];
        }
    }

    public function listarUm($id_projeto)
    {
        try {
            $pd = $this->pdo->prepare("SELECT * FROM projeto WHERE id_projeto = :id");
            $pd->bindValue(":id", $id_projeto, PDO::PARAM_INT);
            $pd->execute();
            $projeto = $pd->fetch(PDO::FETCH_ASSOC);

            if ($projeto) {
                return array(
                    'nome' => $projeto['nome'],
                    'descricao' => $projeto['descricao'],
                    'id_tipo' => $projeto['id_tipo'],
                    'id_local' => $projeto['id_local'],
                    'id_status' => $projeto['id_status'],
                    'data_inicio' => $projeto['data_inicio'],
                    'data_fim' => $projeto['data_fim']
                );
            }

            return null;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar projeto: " . $e->getMessage());
        }
    }

    public function alterarProjeto($id_projeto, $nome, $descricao, $id_tipo, $id_local, $id_status, $data_inicio, $data_fim)
    {
        try {
            $pd = $this->pdo->prepare("UPDATE projeto SET 
            nome = :nome, 
            descricao = :descricao, 
            id_tipo = :id_tipo, 
            id_local = :id_local, 
            id_status = :id_status, 
            data_inicio = :data_inicio, 
            data_fim = :data_fim 
            WHERE id_projeto = :id_projeto");

            $pd->bindValue(':nome', $nome);
            $pd->bindValue(':descricao', $descricao);
            $pd->bindValue(':id_tipo', $id_tipo, PDO::PARAM_INT);
            $pd->bindValue(':id_local', $id_local, PDO::PARAM_INT);
            $pd->bindValue(':id_status', $id_status, PDO::PARAM_INT);
            $pd->bindValue(':data_inicio', $data_inicio);

            if (empty($data_fim) || $data_fim === '' || $data_fim === null) {
                $pd->bindValue(':data_fim', null, PDO::PARAM_NULL);
            } else {
                $pd->bindValue(':data_fim', $data_fim);
            }

            $pd->bindValue(':id_projeto', $id_projeto, PDO::PARAM_INT);
            $pd->execute();

            if ($pd->rowCount() === 0) {
                throw new Exception("Nenhum projeto encontrado com o ID informado.");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao alterar projeto: " . $e->getMessage());
        }
    }

    public function listarTiposProjeto()
    {
        try {
            $sql = "SELECT * FROM projeto_tipo ORDER BY descricao";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarLocaisProjeto()
    {
        try {
            $sql = "SELECT * FROM projeto_local ORDER BY nome";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarStatusProjeto()
    {
        try {
            $sql = "SELECT * FROM projeto_status ORDER BY descricao";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
