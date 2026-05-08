<?php
require_once'../classes/Situacao.php';
require_once'Conexao.php';
require_once'../Functions/funcoes.php';

class SituacaoDAO
{
    public function incluir($situacao)
    {        
        try {
            $pdo = Conexao::connect();

            $sql = 'INSERT situacao(situacoes) VALUES(:situacoes)';
            $sql = str_replace("'", "\'", $sql);            
 
            $stmt = $pdo->prepare($sql);

            $situacoes=$situacao->getSituacoes();

            $stmt->bindParam(':situacoes',$situacoes);

            $stmt->execute();
        }catch (PDOException $e) {
            echo 'Error: <b>  na tabela situacao = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
   public function listarUm($id_situacao)
    {
        try{
            $pdo = Conexao::connect();
            $sql = "SELECT id_situacao, situacoes  FROM situacao WHERE id_situacao = :id_situacao";
            $consulta = $pdo->prepare($sql);
            $consulta->execute(array(
                'id_situacao' => $id_situacao,
            ));
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $situacao = new Situacao($linha['situacoes']);
                $situacao->setId_situacao($linha['id_situacao']);
            }
        }catch(PDOException $e){
            throw $e;
        }
        return $situacao;
    }
    public function excluir($id_situacao){
        try{
            $pdo = Conexao::connect();
            $sql = 'DELETE FROM situacao WHERE id_situacao = :id_situacao';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_situacao',$id_situacao);
            $stmt->execute();
            
        }catch (PDOException $e) {
                echo 'Error: <b>  na tabela situacao = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
        public function listarTodos(){

        try{
            $situacaos=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_situacao, situacoes FROM situacao");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $situacaos[$x]=array('id_situacao'=>$linha['id_situacao'],'situacoes'=>$linha['situacoes']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return json_encode($situacaos);
        }
}
?>