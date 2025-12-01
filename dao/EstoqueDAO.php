<?php
require_once '../classes/Estoque.php';
require_once 'Conexao.php';
require_once '../Functions/funcoes.php';
require_once '../Functions/permissao/permissao.php';

class EstoqueDAO
{
    public function listarTodos()
    {
        try {
            $pdo = Conexao::connect();
            $sql = "
                SELECT p.descricao, p.codigo, a.descricao_almoxarifado, 
                       IFNULL(e.qtd, 0) as qtd, 
                       c.descricao_categoria as categoria, 
                       a.id_almoxarifado 
                FROM produto p 
                LEFT JOIN estoque e ON p.id_produto = e.id_produto
                LEFT JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto
                LEFT JOIN almoxarifado a ON a.id_almoxarifado = e.id_almoxarifado 
                WHERE p.oculto = false
                ORDER BY p.descricao;
            ";

            $consulta = $pdo->prepare($sql);
            $consulta->execute();

            $Estoques = [];
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $Estoques[] = [
                    'codigo' => $linha['codigo'],
                    'descricao' => $linha['descricao'],
                    'descricao_almoxarifado' => $linha['descricao_almoxarifado'],
                    'qtd' => $linha['qtd'],
                    'categoria' => $linha['categoria'],
                    'id_almoxarifado' => $linha['id_almoxarifado']
                ];
            }

            return filtrarAlmoxarifado($_SESSION['id_pessoa'], json_encode($Estoques));

        } catch (PDOException $e) {
            error_log('Erro no método listarTodos: ' . $e->getMessage());
            echo 'Ocorreu um erro ao carregar os dados do estoque.';
        }
    }
}
?>
