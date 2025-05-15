<?php
require_once ROOT . '/classes/Unidade.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class UnidadeDAO
{
    public function incluir($unidade_produto)
    {        
        try {
        	$pdo = Conexao::connect();

            $sql = 'INSERT unidade(descricao_unidade) VALUES(:descricao_unidade)';
            $sql = str_replace("'", "\'", $sql);            
 
            $stmt = $pdo->prepare($sql);

            $descricao_unidade=$unidade_produto->getDescricao_unidade();

            $stmt->bindParam(':descricao_unidade',$descricao_unidade);

            $stmt->execute();
        }catch (PDOException $e) {
            echo 'Error: <b>  na tabela unidade_produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function listarUm($id)
        {
             try {
                $pdo = Conexao::connect();
                $sql = "SELECT id_unidade, descricao_unidade FROM unidade where id_unidade = :id_unidade";
                $consulta = $pdo->prepare($sql);
                $consulta->execute(array(
                ':id_unidade' => $id,
            ));
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $origem = new Unidade($linha['descricao_unidade']);
                $origem->setId_unidade($linha['id_unidade']);
            }
            } catch (PDOException $e) {
                throw $e;
            }
            return $origem;
        }

        public function excluir($id_unidade)
	    {
	        try {
                $pdo = Conexao::connect();
	            $sql = 'DELETE from unidade WHERE id_unidade = :id_unidade';
	            $stmt = $pdo->prepare($sql);
	            $stmt->bindParam(':id_unidade',$id_unidade);
                $stmt->execute();
	        } catch (PDOException $e) {
	            echo 'Error: <b>  na tabela unidade_produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
	        }
	    }
	    public function listarTodos(){

        try{
            $unidades=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_unidade, descricao_unidade FROM unidade");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $unidades[$x]=array('id_unidade'=>$linha['id_unidade'],'descricao_unidade'=>$linha['descricao_unidade']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return json_encode($unidades);
        }
}
?>