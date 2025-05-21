<?php
require_once ROOT . '/classes/Origem.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class OrigemDAO
{
    public function incluir($origem)
    {        
        try {
        	$pdo = Conexao::connect();

            $sql = 'INSERT origem(nome_origem,cnpj,cpf,telefone) VALUES(:nome_origem,:cnpj,:cpf,:telefone)';
            $sql = str_replace("'", "\'", $sql);            
 
            $stmt = $pdo->prepare($sql);

            $nome=$origem->getNome();
            $cnpj=$origem->getCnpj();
            $cpf=$origem->getCpf();
            $telefone=$origem->getTelefone();

            $stmt->bindParam(':nome_origem',$nome);
            $stmt->bindParam(':cnpj',$cnpj);
            $stmt->bindParam(':cpf',$cpf);
            $stmt->bindParam(':telefone',$telefone);

            $stmt->execute();
        }catch (PDOException $e) {
            echo 'Error: <b>  na tabela origem = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarUm($id)
        {
             try {
                $pdo = Conexao::connect();
                $sql = "SELECT id_origem, nome_origem, cnpj, cpf, telefone  FROM origem where id_origem = :id_origem";
                $consulta = $pdo->prepare($sql);
                $consulta->execute(array(
                ':id_origem' => $id,
            ));
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $origem = new Origem($linha['nome_origem'],$linha['cnpj'],$linha['cpf'],$linha['telefone']);
                $origem->setId_origem($linha['id_origem']);

            }
            } catch (PDOException $e) {
                throw $e;
            }
            return $origem;
        }
        public function excluir($id_origem)
	    {
	        try{
                $pdo = Conexao::connect();
                $sql = 'DELETE FROM origem WHERE id_origem = :id_origem';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_origem',$id_origem);
                $stmt->execute();
                
            }catch (PDOException $e) {
                    echo 'Error: <b>  na tabela origem = ' . $sql . '</b> <br /><br />' . $e->getMessage();
            }
	    }
	    public function listarTodos(){

        try{
            $origens=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_origem,nome_origem,cnpj,cpf,telefone FROM origem");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $origens[$x]=array('id_origem'=>$linha['id_origem'],'nome_origem'=>$linha['nome_origem'],'cnpj'=>$linha['cnpj'],'cpf'=>$linha['cpf'],'telefone'=>$linha['telefone']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return json_encode($origens);
        }

        public function listarId_Nome(){

            try{
            $origens=array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_origem,nome_origem FROM origem");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $origens[$x]=array('id_origem'=>$linha['id_origem'],'nome_origem'=>$linha['nome_origem']);
                $x++;
            }
            } catch (PDOException $e){
                echo 'Error:' . $e->getMessage();
            }
            return json_encode($origens);
        }
}
?>