<?php
require_once '../classes/TipoRegistroProfissional.php';
require_once  'Conexao.php';

class TipoRegistroProfissionalDAO
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = Conexao::connect();
        } catch (PDOException $e){
            echo 'Erro ao instanciar objeto do tipo TipoRegistroProfissionalDAO: '.$e->getMessage();
        }
    }

    public function incluir(TipoRegistroProfissional $registro)
    {
        try {
            $sql = "INSERT INTO registro_profissional_tipo(descricao) VALUES (:registro)";
            $stmt = $this->pdo->prepare($sql);
            $registro = $registro->getDescricao();
            $stmt->bindParam(':registro',$registro);
            $stmt->execute();
        }catch (PDOException $e){
            echo 'Error: <b> na tabela registro_profissional_tipo = ' . $sql . '</b> <br /> <br />' . $e->getMessage();
        }  
    } 

    public function listarUm($id_registro)
    {
        try{
            $sql = "SELECT id_registro_profissional_tipo, descricao, status FROM registro_profissional_tipo WHERE id_registro_profissional_tipo=:id_registro";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':id_registro',$id_registro);
            $consulta->execute();
            try{
                $linha = $consulta->fetch(PDO::FETCH_ASSOC);
                $registro = new tipoRegistroProfissional($linha['descricao'],$linha['id_registro_profissional_tipo'],$linha['status']);
                return $registro;
            }catch (InvalidArgumentException $e){
                exit("Ocorreu um erro ao tentar listar o registro profissional solicitado: " . $e->getMessage());
            }
        }catch (PDOException $e){
            throw $e;
        }
    }

    public function listarTodos($status = 1)
    {
        try{
            $tiposRegistrosProfissionais = array();
            $sql = "SELECT id_registro_profissional_tipo, descricao FROM registro_profissional_tipo WHERE status= :status";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':status',$status, PDO::PARAM_INT);
            $consulta->execute();
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            if($resultados){
                foreach($resultados as $resultado){
                    $tipoRegistroProfissional = new TipoRegistroProfissional($resultado['descricao'], $resultado['id_registro_profissional_tipo']);
                    $tiposRegistrosProfissionais[] = $tipoRegistroProfissional;
                }
            }
            return $tiposRegistrosProfissionais;
        }catch (PDOException $e){
            echo 'Error: <b> na tabela registro_profissional_tipo = ' . $sql . '</b> <br /> <br />' . $e->getMessage();
        }
    }

    public function alterarStatus($idTipo, $status)
    {
        try{
            $sql = "UPDATE registro_profissional_tipo SET status = :status WHERE id_registro_profissional_tipo = :idTipo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status',$status, PDO::PARAM_INT);
            $stmt->bindParam(':idTipo',$idTipo, PDO::PARAM_INT);
            $stmt->execute();
        }catch (PDOException $e){
            echo 'Error: <b> na tabela registro_profissional_tipo = ' . $sql . '</b> <br /> <br />' . $e->getMessage();
        }
    }
    
    public function excluir($idTipo)
    {
        try{
            $sql = "DELETE FROM registro_profissional_tipo WHERE id_registro_profissional_tipo = :idTipo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':idTipo',$idTipo,PDO::PARAM_INT);
            $stmt->execute();
        }catch (PDOException $e){
            echo 'Error: <b>  na tabela cargo = ' . $sql . '</b> <br /><br />' . $e->getMessage();
        }
    }
}
?>