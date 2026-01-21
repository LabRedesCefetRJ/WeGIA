<?php
require_once ROOT . '/classes/Destino.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class DestinoDAO
{
    public function incluir($destino)
    {
        $pdo = Conexao::connect();

        $sql = 'INSERT destino(nome_destino,cnpj,cpf,telefone) VALUES(:nome_destino,:cnpj,:cpf,:telefone)';
        $sql = str_replace("'", "\'", $sql);

        $stmt = $pdo->prepare($sql);

        $nome = $destino->getNome();
        $cnpj = $destino->getCnpj();
        $cpf = $destino->getCpf();
        $telefone = $destino->getTelefone();

        $stmt->bindParam(':nome_destino', $nome);
        $stmt->bindParam(':cnpj', $cnpj);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':telefone', $telefone);

        $stmt->execute();
    }
    public function listarUm($id)
    {
        $pdo = Conexao::connect();
        $sql = "SELECT id_destino, nome_destino, cnpj, cpf, telefone  FROM destino where id_destino = :id_destino";
        $consulta = $pdo->prepare($sql);
        $consulta->execute(array(
            ':id_destino' => $id,
        ));
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $destino = new Destino($linha['nome_destino'], $linha['cnpj'], $linha['cpf'], $linha['telefone']);
            $destino->setId_destino($linha['id_destino']);
        }

        return $destino;
    }

    public function excluir($id_destino)
    {
        $pdo = Conexao::connect();
        $sql = 'DELETE FROM destino WHERE id_destino = :id_destino';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_destino', $id_destino);
        $stmt->execute();
    }
    public function listarTodos()
    {
        $destinos = array();
        $pdo = Conexao::connect();
        $consulta = $pdo->query("SELECT id_destino,nome_destino,cnpj,cpf,telefone FROM destino");
        $x = 0;
        while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $destinos[$x] = array('id_destino' => $linha['id_destino'], 'nome_destino' => $linha['nome_destino'], 'cnpj' => $linha['cnpj'], 'cpf' => $linha['cpf'], 'telefone' => $linha['telefone']);
            $x++;
        }

        return json_encode($destinos);
    }
}
