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
                    INNER JOIN pessoa p ON p.id_pessoa = s.id_responsavel";
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
            return json_encode($saidas);
        } catch (PDOException $e){
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($saidas);
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
                        'nome_destino' => $linha['nome_destino'],
                        'descricao_almoxarifado' => $linha['descricao_almoxarifado'],
                        'descricao' => $linha['descricao'],
                        'nome' => $linha['nome'],
                        'data' => $data->format('d/m/Y'),
                        'hora' => $linha['hora'],
                        'valor_total' => $linha['valor_total']
                    ];

                    return json_encode($saida);
                } else {
                    return json_encode([]);
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
        return json_encode($saidas);  
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
}
?>