<?php
require_once ROOT .'/classes/Categoria.php';
require_once ROOT .'/dao/Conexao.php';
require_once ROOT .'/Functions/funcoes.php';
class CategoriaDAO
{
    public function incluir($categoria)
    {
        try {
            $pdo = Conexao::connect();

            $sql = 'INSERT categoria_produto(descricao_categoria) VALUES( :descricao_categoria)';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $pdo->prepare($sql);

            $descricao_categoria = $categoria->getDescricaoCategoria();
            $stmt->bindParam(':descricao_categoria', $descricao_categoria);

            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela categoria_produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function editar($id_categoria_produto, $descricao_categoria)
    {
        try {
            $sql = 'UPDATE `categoria_produto` SET `descricao_categoria`=:descricao_categoria WHERE `id_categoria_produto`=:id_categoria_produto';

            $sql = str_replace("'", "\'", $sql);
            $pdo = Conexao::connect();
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':descricao_categoria', $descricao_categoria);
            $stmt->bindParam(':id_categoria_produto', $id_categoria_produto);

            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela categoria_produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function listarUm($id)
    {
        try {
            $pdo = Conexao::connect();
            $sql = "SELECT id_categoria_produto, descricao_categoria  FROM categoria_produto where id_categoria_produto = :id_categoria_produto";
            $consulta = $pdo->prepare($sql);
            $consulta->execute(array(
                ':id_categoria_produto' => $id,
            ));
            $resultadoConsulta = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($resultadoConsulta) {
                $categoria = new Categoria($resultadoConsulta['descricao_categoria'], $resultadoConsulta['id_categoria_produto']);
                return $categoria;
            } else {
                return null;
            }
        } catch (Exception $e) {
            echo 'Erro ao lista uma categoria: ' . $e->getMessage();
        }
    }

    public function excluir($id_categoria_produto)
    {
        try {
            $pdo = Conexao::connect();
            $sql = 'DELETE FROM categoria_produto WHERE id_categoria_produto = :id_categoria_produto';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_categoria_produto', $id_categoria_produto);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela categoria_produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarTodos()
    {

        try {
            $categorias = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_categoria_produto, descricao_categoria FROM categoria_produto");
            $x = 0;
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $categorias[$x] = array('id_categoria_produto' => $linha['id_categoria_produto'], 'descricao_categoria' => $linha['descricao_categoria']);
                $x++;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($categorias);
    }
}
