<?php

require_once ROOT . '/classes/GrupoProduto.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';
class GrupoProdutoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function incluir($grupoProduto)
    {
        try {
            $sql = 'INSERT grupo_produto(descricao_grupo) VALUES(:descricao_grupo)';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $this->pdo->prepare($sql);

            $descricao_grupo = $grupoProduto->getDescricaoGrupo();
            $stmt->bindParam(':descricao_grupo', $descricao_grupo);

            $stmt->execute();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());

            if ($e->getCode() == 23000) {
                throw new InvalidArgumentException(
                    "Já existe um grupo de produto com essa descrição.",
                    409
                );
            }

            throw new Exception(
                "Não foi possível cadastrar o grupo de produto.",
                500
            );
        }
    }

    public function editar($grupoProduto)
    {
        try {
            $sql = 'UPDATE `grupo_produto` SET `descricao_grupo` = :descricao_grupo WHERE `id_grupo_produto` = :id_grupo_produto';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $this->pdo->prepare($sql);

            $descricao_grupo = $grupoProduto->getDescricaoGrupo();
            $id_grupo_produto = $grupoProduto->getIdGrupoProduto();
            $stmt->bindParam(':descricao_grupo', $descricao_grupo);
            $stmt->bindParam(':id_grupo_produto', $id_grupo_produto);

            $stmt->execute();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            throw new Exception('Não foi possível editar o grupo de produto.', 500);
        }
    }

    public function listarUm($id)
    {
        try {
            $sql = "SELECT id_grupo_produto, descricao_grupo FROM grupo_produto WHERE id_grupo_produto = :id_grupo_produto";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':id_grupo_produto' => $id,));
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if($resultado) {
                $grupoProduto = new GrupoProduto($resultado['descricao_grupo'], $resultado['id_grupo_produto']);
                return $grupoProduto;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            throw new Exception('Não foi possível consultar o grupo de produto.', 500);
        }
    }

    public function excluir($id)
    {
        try {
            $sql = 'DELETE FROM grupo_produto WHERE id_grupo_produto = :id_grupo_produto';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_grupo_produto', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            throw new Exception('Não foi possível excluir o grupo de produto.', 500);
        }
    }

    public function listarTodos()
    {
        try {
            $gruposProduto = array();
            $sql = "SELECT id_grupo_produto, descricao_grupo FROM grupo_produto ORDER BY descricao_grupo";
            $stmt = $this->pdo->query($sql);
            $x = 0;
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $gruposProduto[$x] = array(
                    'id_grupo_produto' => htmlspecialchars($linha['id_grupo_produto']),
                    'descricao_grupo' => htmlspecialchars($linha['descricao_grupo'])
                );
                $x++;
            }
            return json_encode($gruposProduto);
        } catch (PDOException $e) {
            error_log(__METHOD__ . ': ' . $e->getMessage());
            throw new Exception('Não foi possível consultar os grupos de produto.', 500);
        }
    }
}
