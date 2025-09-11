<?php
require_once'../classes/Isaida.php';
require_once'Conexao.php';
require_once'../Functions/funcoes.php';
class IsaidaDAO
{
    //Consultar um utilizando o ID - Xablau
    public function listarId($id_saida){
        try{
            $pdo = Conexao::connect();
            $sql = "SELECT i.id_isaida,i.id_saida,p.descricao,i.qtd,i.valor_unitario,u.descricao_unidade
             FROM isaida i 
             RIGHT JOIN produto p ON p.id_produto = i.id_produto 
             INNER JOIN unidade u ON u.id_unidade = p.id_unidade
             WHERE i.id_saida = :id_saida AND i.oculto = false";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_saida',$id_saida);

            $stmt->execute();
            $isaidas = array();
            while($linha = $stmt->fetch(PDO::FETCH_ASSOC)){
                $isaidas[]=array(
                    'id_isaida'=>$linha['id_isaida'], 
                    'id_saida'=>$linha['id_saida'], 
                    'descricao'=>$linha['descricao'], 
                    'qtd'=>$linha['qtd'], 
                    'valor_unitario'=>$linha['valor_unitario'],
                    'unidade'=>$linha['descricao_unidade']
                );
            }
        } catch(PDOException $e){
            echo 'Erro: ' .  $e->getMessage();
        }
        return json_encode($isaidas);  
    }

    public function incluir($isaida)
        {        
            try {
                $pdo = Conexao::connect();

                $sql = 'INSERT INTO isaida(id_saida,id_produto,qtd,valor_unitario) VALUES(:id_saida,:id_produto,:qtd,:valor_unitario)';
                $sql = str_replace("'", "\'", $sql);            
                
                $stmt = $pdo->prepare($sql);

                $id_saida = $isaida->getId_saida()->getId_saida();
                $id_produto = $isaida->getId_produto()->getId_produto();
                $qtd = $isaida->getQtd();
                $valor_unitario = $isaida->getValor_unitario();

                $stmt->bindParam(':id_saida',$id_saida);
                $stmt->bindParam(':id_produto',$id_produto);                
                $stmt->bindParam(':qtd',$qtd);
                $stmt->bindParam(':valor_unitario',$valor_unitario);

                $stmt->execute();
            }catch (PDOException $e) {
                echo 'Error: <b>  na tabela produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
            }

}
}
