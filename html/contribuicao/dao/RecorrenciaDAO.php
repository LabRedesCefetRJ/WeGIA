<?php
class RecorrenciaDAO{

    private PDO $pdo;

    public function __construct(?PDO $pdo=null)
    {
        if(!is_null($pdo)){
            $this->pdo = $pdo;
        }else{
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ConexaoDAO.php';
            $this->pdo = ConexaoDAO::conectar(); 
        }
    }

    public function create(Recorrencia $recorrencia){
        $sql = "INSERT INTO recorrencia(id_socio, id_gateway, codigo, valor, data_inicio, status) VALUES(:idSocio, :idGateway, :codigo, :valor, :dataInicio, :status)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idSocio', $recorrencia->getSocio()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':idGateway', $recorrencia->getGatewayPagamento()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':codigo', $recorrencia->getCodigo(), PDO::PARAM_STR);
        $stmt->bindValue(':valor', $recorrencia->getValor());
        $stmt->bindValue(':dataInicio', $recorrencia->getInicio()->format('Y-m-d'));
        $stmt->bindValue(':status', $recorrencia->getStatus(), PDO::PARAM_BOOL);

        $stmt->execute();
    }

    public function alterarCodigoPorId(string $codigo, int $id){
        $sqlPagarPorId = "UPDATE recorrencia SET codigo =:codigo WHERE id=:id";

        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

}