<?php
require_once __DIR__ . '/../classes/TipoRegistroProfissional.php';
require_once  'Conexao.php';

class TipoRegistroProfissionalDAO
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = Conexao::connect();
        } catch (PDOException $e){
            error_log("Erro de Conexão com o banco de dados: " . $e->getMessage());
            throw new Exception('Não foi possível conectar ao serviço de banco de dados.');
        }
    }

    public function incluir(TipoRegistroProfissional $registro)
    {
        try {
            $sql = "INSERT INTO registro_profissional_tipo(descricao) VALUES (:registro)";
            $stmt = $this->pdo->prepare($sql);
            $descricao = $registro->getDescricao();
            $stmt->bindParam(':registro', $descricao);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro DB [incluir]: ". $e->getMessage());
            throw new Exception("Erro tentar ao inserir novo tipo de registro profissional.");
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
                error_log("Erro Argumento Inválido [listarUm]: ". $e->getMessage());
                throw new Exception("Erro ao buscar pelo id informado.");
            }
        }catch (PDOException $e){
            error_log("Erro DB [listarUm]: ". $e->getMessage());
            throw new Exception("Erro ao listar o tipo de registro profissional.");
        }
    }

    public function listarTodos($status = 1)
    {
        try{
            $tiposRegistrosProfissionais = array();
            $sql = "SELECT id_registro_profissional_tipo, descricao, status FROM registro_profissional_tipo WHERE status= :status";
            $consulta = $this->pdo->prepare($sql);
            $consulta->bindParam(':status',$status, PDO::PARAM_INT);
            $consulta->execute();
            $resultados = $consulta->fetchAll(PDO::FETCH_ASSOC);
            if($resultados){
                foreach($resultados as $resultado){
                    $tipoRegistroProfissional = new TipoRegistroProfissional($resultado['descricao'], $resultado['id_registro_profissional_tipo'], $resultado['status']);
                    $tiposRegistrosProfissionais[] = $tipoRegistroProfissional;
                }
            }
            return $tiposRegistrosProfissionais;
        }catch (PDOException $e){
            error_log("Erro DB [listarTodos]: ". $e->getMessage());
            throw new Exception("Erro ao listar os tipos de registros profissionais.");
        }
    }

    public function listarTodos2()
    {
        try{
            $sql = "SELECT id_registro_profissional_tipo AS id, descricao FROM registro_profissional_tipo WHERE status = 1 ORDER BY descricao ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
    
            return $stmt->fetchAll(PDO::FETCH_ASSOC); 
        }catch (PDOException $e){
            error_log("Erro DB [listarTodos2]: ". $e->getMessage());
            throw new Exception("Erro ao listar os tipos de registros profissionais.");
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
            error_log("Erro DB [alterarStatus]: ". $e->getMessage());
            throw new Exception("Erro ao alterar status do tipo de registro profissional.");
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
            error_log("Erro DB [alterarStatus]: ". $e->getMessage());
            throw new Exception("Erro ao remover tipo de registro profissional.");
        }
    }
}
?>