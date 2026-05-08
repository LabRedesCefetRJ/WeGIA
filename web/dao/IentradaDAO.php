<?php
require_once ROOT . '/classes/Ientrada.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class IentradaDAO
{
    //Consultar um utilizando o ID - Xablau
    public function listarId($id_entrada)
    {
        try {
            $pdo = Conexao::connect();

            $sql = "SELECT i.id_ientrada, i.id_entrada, p.descricao, i.qtd, i.valor_unitario, u.descricao_unidade
                    FROM ientrada i
                    INNER JOIN produto p ON p.id_produto = i.id_produto
                    INNER JOIN unidade u ON u.id_unidade = p.id_unidade
                    WHERE i.id_entrada = :id_entrada AND i.oculto = false";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_entrada', $id_entrada, PDO::PARAM_INT);
            $stmt->execute();

            $entradas = [];
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Sanitiza campos para evitar XSS na exibição futura
                $entradas[] = [
                    'id_ientrada'    => (int)$linha['id_ientrada'],
                    'id_entrada'     => (int)$linha['id_entrada'],
                    'descricao'      => htmlspecialchars($linha['descricao'], ENT_QUOTES, 'UTF-8'),
                    'qtd'            => (float)$linha['qtd'],
                    'valor_unitario' => (float)$linha['valor_unitario'],
                    'unidade'        => htmlspecialchars($linha['descricao_unidade'], ENT_QUOTES, 'UTF-8')
                ];
            }

            return json_encode($entradas, JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            return json_encode([
                'error' => true,
                'message' => 'Erro ao listar itens de entrada: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Insere um novo item de entrada
     */
    public function incluir($ientrada)
    {
        try {
            $pdo = Conexao::connect();

            $sql = 'INSERT INTO ientrada (id_entrada, id_produto, qtd, valor_unitario) VALUES (:id_entrada, :id_produto, :qtd, :valor_unitario)';

            $stmt = $pdo->prepare($sql);

            $id_entrada = $ientrada->getId_entrada()->getId_entrada();
            $id_produto = $ientrada->getId_produto()->getId_produto();
            $qtd = $ientrada->getQtd();
            $valor_unitario = $ientrada->getValor_unitario();

            // Bind com tipos corretos
            $stmt->bindParam(':id_entrada', $id_entrada, PDO::PARAM_INT);
            $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);
            $stmt->bindParam(':qtd', $qtd);
            $stmt->bindParam(':valor_unitario', $valor_unitario);

            $stmt->execute();

            return json_encode([
                'success' => true,
                'message' => 'Item de entrada incluído com sucesso.'
            ]);

        } catch (PDOException $e) {
            return json_encode([
                'error' => true,
                'message' => 'Erro ao incluir item de entrada: ' . $e->getMessage()
            ]);
        }
    }
}
/*
class IentradaDAO
{
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
*/
?>