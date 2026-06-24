<?php
require_once ROOT . '/classes/Saida.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class SaidaDAO
{
	public function listarTodos(){

    try{
        $pdo = Conexao::connect();
            $sql = "SELECT s.id_saida, d.nome_destino, a.descricao_almoxarifado, 
                           t.descricao, p.nome, s.data, s.hora, s.valor_total 
                    FROM saida s
                    INNER JOIN destino d ON d.id_destino = s.id_destino
                    INNER JOIN almoxarifado a ON a.id_almoxarifado = s.id_almoxarifado
                    INNER JOIN tipo_saida t ON t.id_tipo = s.id_tipo
                    INNER JOIN pessoa p ON p.id_pessoa = s.id_responsavel where s.ativo = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $saidas = [];
            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data = new DateTime($linha['data']);
                $saidas[] = [
                    'id_saida' => $linha['id_saida'],
                    'nome_destino' => $linha['nome_destino'],
                    'descricao_almoxarifado' => $linha['descricao_almoxarifado'],
                    'descricao' => $linha['descricao'],
                    'nome' => $linha['nome'],
                    'data' => $data->format('d/m/Y'),
                    'hora' => $linha['hora'],
                    'valor_total' => $linha['valor_total']
                ];
            }
            return $saidas;
        } catch (PDOException $e){
            echo 'Error:' . $e->getMessage();
        }
        return $saidas;
    }
    

    public function incluir($saida){
        try{
            extract($_REQUEST);
            $pdo = Conexao::connect();

            $sql = 'INSERT INTO saida (id_destino, id_almoxarifado, id_tipo, id_responsavel, data, hora, valor_total)
                    VALUES (:id_destino, :id_almoxarifado, :id_tipo, :id_responsavel, :data, :hora, :valor_total)';

            $stmt = $pdo->prepare($sql);

            $id_destino = $saida->getId_destino()->getId_destino();
            $id_almoxarifado = $saida->getId_almoxarifado()->getId_almoxarifado();
            $id_tipo = $saida->getId_tipo()->getId_tipo();
            $id_responsavel = $saida->getId_responsavel();
            $data = $saida->getData();
            $hora = $saida->getHora();
            $valor_total = $saida->getValor_total();

            $stmt->bindParam(':id_destino', $id_destino, PDO::PARAM_INT);
            $stmt->bindParam(':id_almoxarifado', $id_almoxarifado, PDO::PARAM_INT);
            $stmt->bindParam(':id_tipo', $id_tipo, PDO::PARAM_INT);
            $stmt->bindParam(':id_responsavel', $id_responsavel, PDO::PARAM_INT);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':hora', $hora);
            $stmt->bindParam(':valor_total', $valor_total);

            $stmt->execute();
        } catch(PDOException $e){
            echo 'Error: <b>  na tabela produto = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function listarUm($id)
        {
             try {
                $pdo = Conexao::connect();
                $sql = "SELECT id_saida, data, hora, valor_total, id_responsavel FROM saida where id_saida = :id_saida";
                $consulta = $pdo->prepare($sql);
                $consulta->execute(array(
                ':id_saida' => $id,
            ));
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $data = new DateTime($linha['data']);
                $saida = new Saida($data->format('d/m/Y'),$linha['hora'],$linha['valor_total'],$linha['id_responsavel']);
                $saida->setId_saida($linha['id_saida']);
            }
            } catch (PDOException $e) {
                throw $e;
            }
            return $saida;
        }

        public function listarUmCompletoPorId($id) {
                $pdo = Conexao::connect();
                $consulta = $pdo->prepare("
                    SELECT 
                        s.id_saida,
                        s.id_destino,
                        s.id_almoxarifado,
                        s.id_tipo,
                        s.ativo,
                        d.nome_destino, 
                        a.descricao_almoxarifado, 
                        t.descricao, 
                        p.nome, 
                        s.data, 
                        s.hora, 
                        s.valor_total 
                    FROM saida s
                    INNER JOIN destino d ON d.id_destino = s.id_destino
                    INNER JOIN almoxarifado a ON a.id_almoxarifado = s.id_almoxarifado
                    INNER JOIN tipo_saida t ON t.id_tipo = s.id_tipo
                    INNER JOIN pessoa p ON p.id_pessoa = s.id_responsavel
                    WHERE s.id_saida = :id
                ");

                $consulta->bindParam(":id", $id);
                $consulta->execute();

                $linha = $consulta->fetch(PDO::FETCH_ASSOC);

                if ($linha) {
                    $data = new DateTime($linha['data']);
                    $saida = [
                        'id_saida' => $linha['id_saida'],
                        'id_destino' => $linha['id_destino'],
                        'id_almoxarifado' => $linha['id_almoxarifado'],
                        'id_tipo' => $linha['id_tipo'],
                        'ativo' => $linha['ativo'],
                        'nome_destino' => $linha['nome_destino'],
                        'descricao_almoxarifado' => $linha['descricao_almoxarifado'],
                        'descricao' => $linha['descricao'],
                        'nome' => $linha['nome'],
                        'data' => $data->format('d/m/Y'),
                        'hora' => $linha['hora'],
                        'valor_total' => $linha['valor_total']
                    ];

                    return $saida;
                } else {
                    return [];
                }
        }

    public function listarId($id_saida){
        try{
            $pdo = Conexao::connect();
            $sql = "SELECT id_saida, id_destino, id_almoxarifado, id_tipo, id_responsavel, data, hora, valor_total FROM saida WHERE id_saida = :id_saida";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_saida',$id_saida);

            $stmt->execute();
            $saidas = array();
            while($linha = $stmt->fetch(PDO::FETCH_ASSOC)){
                $data = new DateTime($linha['data']);
                $saidas[]=array('id_saida'=>$linha['id_saida'],'id_destino'=>$linha['id_destino'],'id_almoxarifado'=>$linha['id_almoxarifado'],'id_tipo'=>$linha['id_tipo'],'id_responsavel'=>$linha['id_responsavel'],'data'=>$data->format('d/m/Y'),'hora'=>$linha['hora'],'valor_total'=>$linha['valor_total']);
            }
        } catch(PDOException $e){
            echo 'Erro: ' .  $e->getMessage();
        }
        return $saidas;  
    }

    public function ultima(){
        $pdo = Conexao::connect();
        $sql = "SELECT MAX(id_saida) as id_saida FROM saida";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        while($linha = $stmt->fetch(PDO::FETCH_ASSOC)){
            $ultima = array('id_saida'=>$linha['id_saida']);
        }
        return $ultima;
    }

    public function listarArquivados()
    {
        $entradas = array();
        $pdo = Conexao::connect();

        $consulta = $pdo->query("
            SELECT s.id_saida, d.nome_destino, a.descricao_almoxarifado, 
                t.descricao, p.nome, s.data, s.hora, s.valor_total 
                FROM saida s
                INNER JOIN destino d ON d.id_destino = s.id_destino
                INNER JOIN almoxarifado a ON a.id_almoxarifado = s.id_almoxarifado
                INNER JOIN tipo_saida t ON t.id_tipo = s.id_tipo
                INNER JOIN pessoa p ON p.id_pessoa = s.id_responsavel where s.ativo = 0
        ");

        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $almoxarifados[] = $linha;
        }

        return $almoxarifados;
    }

    public function anular(int $idSaida): array
    {
        $pdo = Conexao::connect();
        $pdo->beginTransaction();

        try {
            $sqlSaida = "
                SELECT id_saida, id_almoxarifado, ativo
                FROM saida
                WHERE id_saida = :id_saida
                FOR UPDATE
            ";

            $stmtSaida = $pdo->prepare($sqlSaida);
            $stmtSaida->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
            $stmtSaida->execute();

            $saida = $stmtSaida->fetch(PDO::FETCH_ASSOC);

            if (!$saida) {
                throw new Exception("Saída não encontrada.");
            }

            if ((int)$saida['ativo'] === 0) {
                throw new Exception("Esta saída já está anulada.");
            }

            $idAlmoxarifado = (int)$saida['id_almoxarifado'];

            $sqlItens = "
                SELECT id_isaida, id_produto, qtd
                FROM isaida
                WHERE id_saida = :id_saida
                    AND oculto = false
            ";

            $stmtItens = $pdo->prepare($sqlItens);
            $stmtItens->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
            $stmtItens->execute();

            $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

            if (empty($itens)) {
                throw new Exception("Esta saída não possui itens ativos.");
            }

            foreach ($itens as $item) {
                $idProduto = (int)$item['id_produto'];
                $qtdSaida = (int)$item['qtd'];

                $sqlDevolverEstoque = "
                    UPDATE estoque
                    SET qtd = qtd + :qtd
                    WHERE id_produto = :id_produto
                        AND id_almoxarifado = :id_almoxarifado
                ";

                $stmtDevolverEstoque = $pdo->prepare($sqlDevolverEstoque);
                $stmtDevolverEstoque->bindValue(':qtd', $qtdSaida, PDO::PARAM_INT);
                $stmtDevolverEstoque->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
                $stmtDevolverEstoque->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
                $stmtDevolverEstoque->execute();
            }

            $sqlOcultarItens = "
                UPDATE isaida
                SET oculto = true
                WHERE id_saida = :id_saida
            ";

            $stmtOcultarItens = $pdo->prepare($sqlOcultarItens);
            $stmtOcultarItens->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
            $stmtOcultarItens->execute();

            $sqlAnularSaida = "
                UPDATE saida
                SET ativo = 0
                WHERE id_saida = :id_saida
            ";

            $stmtAnularSaida = $pdo->prepare($sqlAnularSaida);
            $stmtAnularSaida->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
            $stmtAnularSaida->execute();

            $pdo->commit();

            return [
                'id_almoxarifado' => $idAlmoxarifado,
                'produtos' => array_map(function ($item) {
                    return (int)$item['id_produto'];
                }, $itens)
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function editarBasico(int $idSaida, string $data, string $hora, int $idDestino, int $idTipo, array $itens): void {
        $pdo = Conexao::connect();
        $pdo->beginTransaction();

        try {
            $sqlSaida = "
                SELECT id_saida, id_almoxarifado, ativo
                FROM saida
                WHERE id_saida = :id_saida
                FOR UPDATE
            ";

            $stmtSaida = $pdo->prepare($sqlSaida);
            $stmtSaida->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
            $stmtSaida->execute();

            $saida = $stmtSaida->fetch(PDO::FETCH_ASSOC);

            if (!$saida) {
                throw new Exception("Saída não encontrada.");
            }

            if ((int)$saida['ativo'] === 0) {
                throw new Exception("Não é possível editar uma saída anulada.");
            }

            $idAlmoxarifado = (int)$saida['id_almoxarifado'];

            $stmtDestino = $pdo->prepare("SELECT id_destino FROM destino WHERE id_destino = :id_destino");
            $stmtDestino->bindValue(':id_destino', $idDestino, PDO::PARAM_INT);
            $stmtDestino->execute();

            if (!$stmtDestino->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("Destino inválido.");
            }

            $stmtTipo = $pdo->prepare("SELECT id_tipo FROM tipo_saida WHERE id_tipo = :id_tipo");
            $stmtTipo->bindValue(':id_tipo', $idTipo, PDO::PARAM_INT);
            $stmtTipo->execute();

            if (!$stmtTipo->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("Tipo de saída inválido.");
            }

            $sqlAtualizarSaida = "
                UPDATE saida
                SET data = :data,
                    hora = :hora,
                    id_destino = :id_destino,
                    id_tipo = :id_tipo
                WHERE id_saida = :id_saida
            ";

            $stmtAtualizarSaida = $pdo->prepare($sqlAtualizarSaida);
            $stmtAtualizarSaida->bindValue(':data', $data);
            $stmtAtualizarSaida->bindValue(':hora', $hora);
            $stmtAtualizarSaida->bindValue(':id_destino', $idDestino, PDO::PARAM_INT);
            $stmtAtualizarSaida->bindValue(':id_tipo', $idTipo, PDO::PARAM_INT);
            $stmtAtualizarSaida->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
            $stmtAtualizarSaida->execute();

            foreach ($itens as $item) {
                $idIsaida = isset($item['id_isaida']) ? (int)$item['id_isaida'] : 0;
                $novaQtd = isset($item['qtd']) ? (int)$item['qtd'] : 0;
                $novoValorUnitario = isset($item['valor_unitario']) ? (float)$item['valor_unitario'] : -1;

                if ($idIsaida < 1) {
                    throw new Exception("Item de saída inválido.");
                }

                if ($novaQtd <= 0) {
                    throw new Exception("Quantidade da saída inválida.");
                }

                if ($novoValorUnitario < 0) {
                    throw new Exception("Valor unitário inválido.");
                }

                $sqlItemAtual = "
                    SELECT id_isaida, id_produto, qtd
                    FROM isaida
                    WHERE id_isaida = :id_isaida
                        AND id_saida = :id_saida
                        AND oculto = false
                    FOR UPDATE
                ";

                $stmtItemAtual = $pdo->prepare($sqlItemAtual);
                $stmtItemAtual->bindValue(':id_isaida', $idIsaida, PDO::PARAM_INT);
                $stmtItemAtual->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
                $stmtItemAtual->execute();

                $itemAtual = $stmtItemAtual->fetch(PDO::FETCH_ASSOC);

                if (!$itemAtual) {
                    throw new Exception("Um dos itens da saída não foi encontrado.");
                }

                $idProduto = (int)$itemAtual['id_produto'];
                $qtdAntiga = (int)$itemAtual['qtd'];
                $diferenca = $novaQtd - $qtdAntiga;

                if ($diferenca > 0) {
                    $sqlEstoque = "
                        SELECT qtd
                        FROM estoque
                        WHERE id_produto = :id_produto
                            AND id_almoxarifado = :id_almoxarifado
                        FOR UPDATE
                    ";

                    $stmtEstoque = $pdo->prepare($sqlEstoque);
                    $stmtEstoque->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
                    $stmtEstoque->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
                    $stmtEstoque->execute();

                    $estoque = $stmtEstoque->fetch(PDO::FETCH_ASSOC);

                    if (!$estoque) {
                        throw new Exception("Estoque não encontrado para um dos produtos.");
                    }

                    if ((int)$estoque['qtd'] < $diferenca) {
                        throw new Exception(
                            "Não é possível aumentar a quantidade desta saída, pois não há estoque suficiente."
                        );
                    }
                }

                if ($diferenca !== 0) {
                    $sqlAtualizarEstoque = "
                        UPDATE estoque
                        SET qtd = qtd - :diferenca
                        WHERE id_produto = :id_produto
                            AND id_almoxarifado = :id_almoxarifado
                    ";

                    $stmtAtualizarEstoque = $pdo->prepare($sqlAtualizarEstoque);
                    $stmtAtualizarEstoque->bindValue(':diferenca', $diferenca, PDO::PARAM_INT);
                    $stmtAtualizarEstoque->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
                    $stmtAtualizarEstoque->bindValue(':id_almoxarifado', $idAlmoxarifado, PDO::PARAM_INT);
                    $stmtAtualizarEstoque->execute();
                }

                $sqlAtualizarItem = "
                    UPDATE isaida
                    SET qtd = :qtd,
                        valor_unitario = :valor_unitario
                    WHERE id_isaida = :id_isaida
                        AND id_saida = :id_saida
                        AND oculto = false
                ";

                $stmtAtualizarItem = $pdo->prepare($sqlAtualizarItem);
                $stmtAtualizarItem->bindValue(':qtd', $novaQtd, PDO::PARAM_INT);
                $stmtAtualizarItem->bindValue(':valor_unitario', $novoValorUnitario);
                $stmtAtualizarItem->bindValue(':id_isaida', $idIsaida, PDO::PARAM_INT);
                $stmtAtualizarItem->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
                $stmtAtualizarItem->execute();
            }

            $this->recalcularValorTotalSaida($idSaida, $pdo);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function recalcularValorTotalSaida(int $idSaida, PDO $pdo): void
    {
        $sql = "
            UPDATE saida
            SET valor_total = (
                SELECT IFNULL(SUM(qtd * valor_unitario), 0)
                FROM isaida
                WHERE id_saida = :id_saida
                    AND oculto = false
            )
            WHERE id_saida = :id_saida
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_saida', $idSaida, PDO::PARAM_INT);
        $stmt->execute();
    }
}
?>