<?php
//requisitar arquivo de conexão
require_once '../dao/ConexaoDAO.php';

//requisitar model
require_once '../model/ContribuicaoLog.php';
require_once '../model/ContribuicaoLogCollection.php';

class ContribuicaoLogDAO{
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        if(is_null($pdo)){
            $this->pdo = ConexaoDAO::conectar();
        }else{
            $this->pdo = $pdo;
        }
    }

    public function criar(ContribuicaoLog $contribuicaoLog){
        $sqlInserirContribuicaoLog = 
            "INSERT INTO contribuicao_log (
                    id_socio,
                    id_gateway,
                    id_meio_pagamento, 
                    codigo, 
                    valor, 
                    data_geracao, 
                    data_vencimento, 
                    status_pagamento
                ) 
                VALUES (
                    :idSocio, 
                    :idGateway,
                    :idMeioPagamento,
                    :codigo, 
                    :valor, 
                    :dataGeracao, 
                    :dataVencimento, 
                    :statusPagamento
                )
            ";
        
        $stmt = $this->pdo->prepare($sqlInserirContribuicaoLog);
        $stmt->bindParam(':idSocio', $contribuicaoLog->getSocio()->getId());
        $stmt->bindParam(':idGateway', $contribuicaoLog->getGatewayPagamento()->getId());
        $stmt->bindParam(':idMeioPagamento', $contribuicaoLog->getMeioPagamento()->getId());
        $stmt->bindParam(':codigo', $contribuicaoLog->getCodigo());
        $stmt->bindParam(':valor', $contribuicaoLog->getValor());
        $stmt->bindParam(':dataGeracao', $contribuicaoLog->getDataGeracao());
        $stmt->bindParam(':dataVencimento', $contribuicaoLog->getDataVencimento());
        $stmt->bindParam(':statusPagamento', $contribuicaoLog->getStatusPagamento());

        $stmt->execute();

        $ultimoId = $this->pdo->lastInsertId();
        $contribuicaoLog->setId($ultimoId);

        return $contribuicaoLog;
    }

    public function alterarCodigoPorId($codigo, $id){
        $sqlPagarPorId = "UPDATE contribuicao_log SET codigo =:codigo WHERE id=:id";
        
        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function pagarPorId($id){
        $sqlPagarPorId = "UPDATE contribuicao_log SET status_pagamento = 1 WHERE id=:id";
        
        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function listarPorDocumento(string $documento){
        $sql = "SELECT cl.id, cl.codigo, cl.valor, cl.data_geracao, cl.data_vencimento, cl.status_pagamento FROM contribuicao_log cl JOIN socio s ON (cl.id_socio=s.id_socio) JOIN pessoa p ON(s.id_pessoa=p.id_pessoa) WHERE cpf=:documento";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':documento', $documento);

        $stmt->execute();

        if($stmt->rowCount() < 1){
            return null;
        }

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $contribuicaoLogCollection = new ContribuicaoLogCollection();

        foreach($resultado as $contribuicaoLog){
            $contribuicaoLogObject = new ContribuicaoLog();
            $contribuicaoLogObject
                ->setId($contribuicaoLog['id'])
                ->setCodigo($contribuicaoLog['codigo'])
                ->setValor($contribuicaoLog['valor'])
                ->setDataGeracao($contribuicaoLog['data_geracao'])
                ->setDataVencimento($contribuicaoLog['data_vencimento'])
                ->setStatusPagamento($contribuicaoLog['status_pagamento']);

            $contribuicaoLogCollection->add($contribuicaoLogObject);
        }

        return $contribuicaoLogCollection;
    }

    public function getContribuicoes(){
        $sql = 
        "SELECT 
            cl.codigo, 
            p.nome as nomeSocio, 
            cl.data_geracao as dataGeracao, 
            cl.data_vencimento as dataVencimento, 
            cl.data_pagamento as dataPagamento, 
            cl.valor, 
            cl.status_pagamento as status 
        FROM contribuicao_log cl 
        JOIN socio s ON (s.id_socio=cl.id_socio) 
        JOIN pessoa p ON (p.id_pessoa=s.id_pessoa) 
        ORDER BY cl.data_geracao DESC";

        $contribuicoesArray = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $contribuicoesArray;
    }

    public function getAgradecimento(){
        $sql = "SELECT paragrafo FROM selecao_paragrafo WHERE nome_campo = 'agradecimento_doador'";

        $agradecimento = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['paragrafo'];

        if($agradecimento && strlen($agradecimento) > 0){
            return $agradecimento;
        }else{
            return 'Obrigado pela contribuição!';
        }
    }

}