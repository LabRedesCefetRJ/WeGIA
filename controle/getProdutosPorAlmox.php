<?php
    require_once('../dao/Conexao.php');
    require_once('../classes/Produto.php');
    require_once('../config.php');
    $almox = $_REQUEST['almox'];
    $pdo = Conexao::connect();
    $produtos = array();
    if($conexao){
        $query = "SELECT produto.id_produto, produto.codigo, produto.descricao, estoque.qtd, produto.preco FROM produto, estoque WHERE produto.id_produto=estoque.id_produto AND estoque.qtd>0 AND estoque.id_almoxarifado=:almox";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':almox', $almox);
        $stmt->execute();
        while($resultado = $stmt->fetchAll(PDO::FETCH_ASSOC)){
            $produtos[] = $resultado; 
        }
        echo(json_encode($produtos));
    }else echo false;
?>