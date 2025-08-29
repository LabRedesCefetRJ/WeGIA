<?php
require_once  ROOT . '/classes/Ientrada.php';
require_once  ROOT . '/dao/Conexao.php';
require_once  ROOT . '/Functions/funcoes.php';
class IentradaDAO
{
    //Consultar um utilizando o ID - Xablau
    public function listarId($id_entrada){
        try{
            $pdo = Conexao::connect();
            $sql = "SELECT i.id_ientrada,i.id_entrada,p.descricao,i.qtd,i.valor_unitario,u.descricao_unidade
             FROM ientrada i 
             INNER JOIN produto p ON p.id_produto = i.id_produto
             INNER JOIN unidade u ON u.id_unidade = p.id_unidade
             WHERE i.id_entrada = :id_entrada AND i.oculto = false";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_entrada',$id_entrada);

            $stmt->execute();
            $entradas = array();
            while($linha = $stmt->fetch(PDO::FETCH_ASSOC)){
                $entradas[] = array(
                    'id_ientrada'=>$linha['id_ientrada'], 
                    'id_entrada'=>$linha['id_entrada'], 
                    'descricao'=>$linha['descricao'], 
                    'qtd'=>$linha['qtd'], 
                    'valor_unitario'=>$linha['valor_unitario'],
                    'unidade'=>$linha['descricao_unidade']
                );
            }
        } catch(PDOException $e){
            echo 'Erro: ' .  $e->getMessage();
        }
        return json_encode($entradas);  
    }

    public function incluir($ientrada)
        {        
            try {
                $pdo = Conexao::connect();

                $sql = 'INSERT INTO ientrada(id_entrada,id_produto,qtd,valor_unitario) VALUES(:id_entrada,:id_produto,:qtd,:valor_unitario)';
                $sql = str_replace("'", "\'", $sql);            
                
                $stmt = $pdo->prepare($sql);

                $id_entrada = $ientrada->getId_entrada()->getId_entrada();
                $id_produto = $ientrada->getId_produto()->getId_produto();
                $qtd = $ientrada->getQtd();
                $valor_unitario = $ientrada->getValor_unitario();

                $stmt->bindParam(':id_entrada',$id_entrada);
                $stmt->bindParam(':id_produto',$id_produto);                
                $stmt->bindParam(':qtd',$qtd);
                $stmt->bindParam(':valor_unitario',$valor_unitario);

                $stmt->execute();
            }catch (PDOException $e) {
                echo 'Error: <b>  na tabela produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
            }

        }

}

?>