<?php
$basePath = dirname(__FILE__, 2);

require_once $basePath . '/classes/Estoque.php';
require_once $basePath . '/dao/Conexao.php';
require_once $basePath . '/Functions/funcoes.php';
require_once $basePath . '/Functions/permissao/permissao.php';

class EstoqueDAO
{
    public function listarTodos()
    {
        try {
            $pdo = Conexao::connect();
            $sql = "
                SELECT p.id_produto, e.qtd_minima, p.descricao, p.codigo, a.descricao_almoxarifado, 
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
                    'id_produto' => $linha['id_produto'],
                    'codigo' => $linha['codigo'],
                    'descricao' => $linha['descricao'],
                    'descricao_almoxarifado' => $linha['descricao_almoxarifado'],
                    'qtd' => $linha['qtd'],
                    'qtd_minima' => $linha['qtd_minima'],
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

    public function buscarDadosEstoqueMinimo(int $idProduto, int $idAlmoxarifado): ?array
    {
        $pdo = Conexao::connect();

        $sql = "
            SELECT 
                e.qtd,
                e.qtd_minima,
                p.descricao AS produto,
                a.descricao_almoxarifado AS almoxarifado
            FROM estoque e
            INNER JOIN produto p ON p.id_produto = e.id_produto
            INNER JOIN almoxarifado a ON a.id_almoxarifado = e.id_almoxarifado
            WHERE e.id_produto = :id_produto
                AND e.id_almoxarifado = :id_almoxarifado
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        $stmt->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
        $stmt->execute();

        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dados ?: null;
    }

    public function buscarResponsaveisAlmoxarifado(int $idAlmoxarifado): array
    {
        $pdo = Conexao::connect();

        $sql = "
            SELECT f.id_pessoa
            FROM almoxarife a
            INNER JOIN funcionario f ON f.id_funcionario = a.id_funcionario
            WHERE a.id_almoxarifado = :id_almoxarifado
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function listarProdutosPorAlmoxarifadoComLimite(int $idAlmoxarifado): array
    {
        $pdo = Conexao::connect();

        $sql = "
            SELECT 
                e.id_produto,
                e.id_almoxarifado,
                e.qtd,
                e.qtd_minima,
                p.codigo,
                p.descricao,
                c.descricao_categoria,
                u.descricao_unidade
            FROM estoque e
            INNER JOIN produto p ON p.id_produto = e.id_produto
            INNER JOIN categoria_produto c ON p.id_categoria_produto = c.id_categoria_produto
            INNER JOIN unidade u ON p.id_unidade = u.id_unidade
            WHERE e.id_almoxarifado = :id_almoxarifado
            ORDER BY p.descricao
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizarQuantidadeMinima(int $idProduto, int $idAlmoxarifado, int $qtdMinima): void
    {
        $pdo = Conexao::connect();

        $sql = "
            UPDATE estoque
            SET qtd_minima = :qtd_minima
            WHERE id_produto = :id_produto
                AND id_almoxarifado = :id_almoxarifado
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':qtd_minima', $qtdMinima, PDO::PARAM_INT);
        $stmt->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
        $stmt->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>
