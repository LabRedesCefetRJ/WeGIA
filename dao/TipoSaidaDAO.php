<?php
require_once ROOT . '/classes/TipoSaida.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class TipoSaidaDAO
{
    public function incluir($tipo_saida)
    {
        $pdo = Conexao::connect();

        $sql = 'INSERT tipo_saida(descricao) VALUES(:descricao)';
        $sql = str_replace("'", "\'", $sql);

        $stmt = $pdo->prepare($sql);

        $descricao = $tipo_saida->getDescricao();

        $stmt->bindParam(':descricao', $descricao);

        $stmt->execute();
    }

    public function listarUm($id)
    {
        $pdo = Conexao::connect();
        $sql = "SELECT id_tipo, descricao FROM tipo_saida where id_tipo = :id_tipo";
        $consulta = $pdo->prepare($sql);
        $consulta->execute(array(
            ':id_tipo' => $id,
        ));
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $x = new TipoSaida($linha['descricao']);
            $x->setId_tipo($linha['id_tipo']);
        }

        return $x;
    }

    public function excluir($id_tipo)
    {
        $pdo = Conexao::connect();
        $sql = 'DELETE FROM tipo_saida WHERE id_tipo = :id_tipo';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_tipo', $id_tipo);
        $stmt->execute();
    }
    
    public function listarTodos()
    {
        $tiposaidas = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT id_tipo, descricao FROM tipo_saida");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $tiposaidas[$x] = array('id_tipo' => $linha['id_tipo'], 'descricao' => $linha['descricao']);
            $x++;
        }

        return json_encode($tiposaidas);
    }
}
