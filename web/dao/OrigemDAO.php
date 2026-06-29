<?php
require_once ROOT . '/classes/Origem.php';
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/Functions/funcoes.php';

class OrigemDAO
{
    public function incluir($origem)
    {
        try {
            $pdo = Conexao::connect();

            $sql = 'INSERT INTO origem(nome_origem,cnpj,cpf,telefone) VALUES(:nome_origem,:cnpj,:cpf,:telefone)';
            $sql = str_replace("'", "\'", $sql);

            $stmt = $pdo->prepare($sql);

            $nome = $origem->getNome();
            $cnpj = $origem->getCnpj();
            $cpf = $origem->getCpf();
            $telefone = $origem->getTelefone();

            $stmt->bindParam(':nome_origem', $nome);
            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);

            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela origem = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarUm($id)
    {
        try {
            $pdo = Conexao::connect();
            $sql = "SELECT id_origem, nome_origem, cnpj, cpf, telefone  FROM origem where id_origem = :id_origem";
            $consulta = $pdo->prepare($sql);
            $consulta->execute(array(
                ':id_origem' => $id,
            ));
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $origem = new Origem($linha['nome_origem'], $linha['cnpj'], $linha['cpf'], $linha['telefone']);
                $origem->setId_origem($linha['id_origem']);
            }
        } catch (PDOException $e) {
            throw $e;
        }
        return $origem;
    }
    public function excluir($id_origem)
    {
        try {
            $pdo = Conexao::connect();
            $sql = 'DELETE FROM origem WHERE id_origem = :id_origem';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_origem', $id_origem);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela origem = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
    public function listarTodos()
    {

        try {
            $origens = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("
                SELECT
                    o.id_origem,
                    o.nome_origem,
                    o.cnpj,
                    o.cpf,
                    o.telefone,
                    GROUP_CONCAT(oa.id_almoxarifado) AS almoxarifados
                FROM origem o
                LEFT JOIN origem_almoxarifado oa
                    ON oa.id_origem = o.id_origem
                GROUP BY
                    o.id_origem,
                    o.nome_origem,
                    o.cnpj,
                    o.cpf,
                    o.telefone
                ORDER BY o.nome_origem
            ");
            $x=0;
            while($linha = $consulta->fetch(PDO::FETCH_ASSOC)){
                $origens[$x] = array(
                    'id_origem' => $linha['id_origem'],
                    'nome_origem' => $linha['nome_origem'],
                    'cnpj' => $linha['cnpj'],
                    'cpf' => $linha['cpf'],
                    'telefone' => $linha['telefone'],
                    'almoxarifados' => $linha['almoxarifados']
                        ? explode(',', $linha['almoxarifados'])
                        : array()
                );
                $x++;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($origens);
    }

    public function listarId_Nome()
    {
        try {
            $origens = array();
            $pdo = Conexao::connect();
            $consulta = $pdo->query("SELECT id_origem,nome_origem FROM origem ORDER BY nome_origem");
            $x = 0;
            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                $origens[$x] = array('id_origem' => $linha['id_origem'], 'nome_origem' => $linha['nome_origem']);
                $x++;
            }
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }
        return json_encode($origens);
    }

    public function listarPorAlmoxarifado($id_almoxarifado)
    {
        try {
            $origens = array();
            $pdo = Conexao::connect();

            $sql = "
                SELECT o.id_origem, o.nome_origem
                FROM origem o
                INNER JOIN origem_almoxarifado oa
                    ON oa.id_origem = o.id_origem
                WHERE oa.id_almoxarifado = :id_almoxarifado
                ORDER BY o.nome_origem
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_almoxarifado', $id_almoxarifado, PDO::PARAM_INT);
            $stmt->execute();

            while ($linha = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $origens[] = array(
                    'id_origem' => $linha['id_origem'],
                    'nome_origem' => $linha['nome_origem']
                );
            }

            return json_encode($origens);
        } catch (PDOException $e) {
            echo 'Error:' . $e->getMessage();
        }

        return json_encode(array());
    }

    public function alterar($origem)
    {
        try {
            $pdo = Conexao::connect();

            $sql = "UPDATE origem
                    SET nome_origem = :nome_origem,
                        cnpj = :cnpj,
                        cpf = :cpf,
                        telefone = :telefone
                    WHERE id_origem = :id_origem";

            $stmt = $pdo->prepare($sql);

            $nome = $origem->getNome();
            $cnpj = $origem->getCnpj();
            $cpf = $origem->getCpf();
            $telefone = $origem->getTelefone();
            $id_origem = $origem->getId_origem();

            $stmt->bindParam(':nome_origem', $nome);
            $stmt->bindParam(':cnpj', $cnpj);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':id_origem', $id_origem, PDO::PARAM_INT);

            $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function atualizarAlmoxarifados($id_origem, $almoxarifados)
    {
        $pdo = Conexao::connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("DELETE FROM origem_almoxarifado WHERE id_origem = :id_origem");
            $stmt->bindValue(':id_origem', $id_origem, PDO::PARAM_INT);
            $stmt->execute();

            if (!empty($almoxarifados)) {
                $stmt = $pdo->prepare("
                    INSERT INTO origem_almoxarifado (id_origem, id_almoxarifado)
                    VALUES (:id_origem, :id_almoxarifado)
                ");

                foreach ($almoxarifados as $id_almoxarifado) {
                    $stmt->bindValue(':id_origem', $id_origem, PDO::PARAM_INT);
                    $stmt->bindValue(':id_almoxarifado', (int) $id_almoxarifado, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }   
}
