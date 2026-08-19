<?php
    require_once __DIR__ . '/../classes/IdentificadorRegistroProfissional.php';
    require_once  'Conexao.php';

class IdentificadorRegistroProfissionalDAO
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = Conexao::connect();
        } catch (PDOException $e){
            echo 'Erro ao instanciar objeto do tipo IdentificadorRegistroProfissionalDAO: '.$e->getMessage();
        }
    }

    
    public function salvarRegistroProfissional($idFuncionario, $idTipoRegistro, $numeroRegistro, $uf = null)
    {
        try {
            $sql = "INSERT INTO registro_profissional_identificador(id_registro_profissional_tipo, id_funcionario, numero_registro, uf) VALUES (:idTipoRegistro, :idFuncionario, :numeroRegistro, :uf)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->bindValue(':idTipoRegistro', $idTipoRegistro, PDO::PARAM_INT);
            $stmt->bindValue(':numeroRegistro', $numeroRegistro, PDO::PARAM_STR);
            $stmt->bindValue(':uf', $uf, PDO::PARAM_STR);
            $stmt->execute();

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao inserir o registro profissional no banco de dados: " . $e->getMessage());
        }
    }

    public function adicionar(IdentificadorRegistroProfissional $identificador)
    {
        try{
            $sql = "INSERT INTO registro_profissional_identificador(id_registro_profissional_tipo, id_funcionario, numero_registro, uf) VALUES (:idTipo,:idFuncionario,:identificador,:uf)";
            $stmt = $this->pdo->prepare($sql);
            $idTipo = $identificador->getIdTipoRegistro();
            $idFuncionario = $identificador->getIdFuncionario();
            $numero = $identificador->getNumeroRegistro();
            $uf = $identificador->getUf();
            $stmt->bindParam(':idTipo',$idTipo);
            $stmt->bindParam(':idFuncionario',$idFuncionario);
            $stmt->bindParam(':identificador',$numero);
            $stmt->bindParam(':uf',$uf);
            $stmt->execute();
        }catch (PDOException $e) {
            throw new Exception("Erro ao inserir o registro profissional no banco de dados: " . $e->getMessage());
        }
    }

    
    public function listarPorIdFuncionario($idFuncionario)
    {
        try {
            $sql = "SELECT
                        i.id_registro_profissional_identificador AS id_registro,
                        i.id_registro_profissional_tipo AS id_tipo,
                        t.descricao AS descricao,
                        i.numero_registro AS numero_registro,
                        i.uf AS uf
                    FROM registro_profissional_identificador i
                    JOIN registro_profissional_tipo t ON(t.id_registro_profissional_tipo = i.id_registro_profissional_tipo)
                    WHERE i.id_funcionario = :idFuncionario
                    ORDER BY t.descricao";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar os registros profissionais no banco de dados: " . $e->getMessage());
        }
    }

    public function buscarPorIdFuncionario($idFuncionario)
    {
        return $this->listarPorIdFuncionario($idFuncionario);
    }

    public function alterarRegistroPorId($idRegistro, $idFuncionario, $numeroRegistro, $uf = null)
    {
        try {
            $sql = "UPDATE registro_profissional_identificador
                    SET numero_registro = :numeroRegistro, uf = :uf
                    WHERE id_registro_profissional_identificador = :idRegistro AND id_funcionario = :idFuncionario";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':numeroRegistro', $numeroRegistro, PDO::PARAM_STR);
            $stmt->bindValue(':uf', $uf, PDO::PARAM_STR);
            $stmt->bindValue(':idRegistro', $idRegistro, PDO::PARAM_INT);
            $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro ao alterar o registro profissional no banco de dados: " . $e->getMessage());
        }
    }

    public function alterarRegistro($numeroRegistro, $idFuncionario, $uf = null)
    {
        try{
            $sql = "UPDATE registro_profissional_identificador SET numero_registro = :numeroRegistro, uf = :uf WHERE id_funcionario=:idFuncionario";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':numeroRegistro', $numeroRegistro, PDO::PARAM_STR);
            $stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
            $stmt->bindParam(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();
        }catch(PDOException $e){
            echo 'Error: <b> na tabela registro_profissional_tipo = ' . $sql . '</b> <br /> <br />' . $e->getMessage();
        }
    }

    public function remover($idRegistro, $idFuncionario)
    {
        try {
            $sql = "DELETE FROM registro_profissional_identificador WHERE id_registro_profissional_identificador = :idRegistro AND id_funcionario = :idFuncionario";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':idRegistro', $idRegistro, PDO::PARAM_INT);
            $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Erro ao remover o registro profissional no banco de dados: " . $e->getMessage());
        }
    }
}
?>