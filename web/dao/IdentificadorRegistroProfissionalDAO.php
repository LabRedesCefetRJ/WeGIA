<?php
    require_once '../classes/IdentificadorRegistroProfissional.php';
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

    public function inserir(IdentificadorRegistroProfissional $identificador){
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
        $stmt->bindParam(':id_funcionario',$indFuncionario, PDO::PARAM_INT);
        $stmt->execute();

        $registros = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = new IdentificadorRegistroProfissional(
                $row['id_registro_profissional_identificador'],
                $row['id_registro_profissional_tipo'],
                $row['id_funcionario'],
                $row['numero_registro'],
                $row['UF']
            );
        }
    }
}
?>