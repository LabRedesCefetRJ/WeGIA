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

    public function buscarPorIdFuncionario($idFuncionario){
        $sql = "SELECT numero_registro, uf FROM registro_profissional_identificador WHERE id_funcionario = :id_funcionario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id_funcionario',$idFuncionario, PDO::PARAM_INT);
        $stmt->execute();

        $registros = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = new IdentificadorRegistroProfissional(
                $row['numero_registro'],
                $row['uf']
            );
        }
        return $registros;
    }

    public function alterarRegistro($numeroRegistro, $idFuncionario, $uf = null){
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
}
?>