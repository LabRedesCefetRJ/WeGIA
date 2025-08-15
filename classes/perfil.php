<?php
require_once ('acesso.php');
require_once ('logs.php');

class Perfil
{

    // Atributos da classe
    private $idperfil;

    private $descricao;

    // Insert
    public function incluir($descricao)
    {
        try {
            $sql = 'insert into perfil(descricao) values( :descricao);';
            $sql = str_replace("'", "\'", $sql);
            $acesso = new Acesso();
            $pdo = $acesso->conexao();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':descricao', $descricao);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela perfil = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    // excluir
    public function excluir($idperfil)
    {
        try {
            $sql = 'delete from perfil where idperfil= :idperfil';
            $sql = str_replace("'", "\'", $sql);
            $acesso = new Acesso();
            
            $pdo = $acesso->conexao();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':idperfil', $idperfil);
            
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela perfil = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    // Editar
    public function alterar($idperfil, $descricao)
    {
        try {
            $sql = 'update perfil set idperfil=:idperfil,descricao=:descricao where idperfil= :idperfil';
            $sql = str_replace("'", "\'", $sql);
            $acesso = new Acesso();
            
            $pdo = $acesso->conexao();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindParam(':idperfil', $idperfil);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: <b>  na tabela perfil = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }

    public function consultar($sql)
    {
        $acesso = new Acesso();
        $acesso->conexao();
        $acesso->query($sql);
    }
}

?>