<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ContribuicaoLogCollection.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ContribuicaoLog.php';

class RecorrenciaDAO
{

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        } else {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ConexaoDAO.php';
            $this->pdo = ConexaoDAO::conectar();
        }
    }

    public function create(Recorrencia $recorrencia)
    {
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

    public function alterarCodigoPorId(string $codigo, int $id)
    {
        $sqlPagarPorId = "UPDATE recorrencia SET codigo =:codigo WHERE id=:id";

        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    /**Recebe como parâmetro um código de uma recorrência e retorna todas as contribuições associadas, caso nenhum código seja informado todas as contribuições do sistema que possuem uma recorrência serão listadas */
    public function getContribuicoesPorRecorrencia(?string $codigo = null): ContribuicaoLogCollection
    {
        $contribuicoesLogArray = [];

        $sql = 'SELECT cl.id, cl.valor, cl.codigo as codigo_contribuicao, cl.data_geracao, cl.data_vencimento, cl.data_pagamento, cl.status_pagamento, cl.id_socio, cl.id_recorrencia, r.codigo as codigo_recorrencia, r.data_inicio, r.data_termino, r.status as status_recorrencia FROM contribuicao_log cl JOIN recorrencia r ON(r.id=cl.id_recorrencia) WHERE id_recorrencia IS NOT NULL';

        if (!is_null($codigo)) {
            $sql .= ' AND r.codigo=:codigo';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmt->execute();

            $contribuicoesLogArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $contribuicoesLogArray = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        $contribuicaoLogCollection = new ContribuicaoLogCollection();

        foreach ($contribuicoesLogArray as $contribuicaoLogArray) {
            $contribuicaoLog = new ContribuicaoLog();
            $contribuicaoLog->setId($contribuicaoLogArray['id'])
                ->setValor($contribuicaoLogArray['valor'])
                ->setCodigo($contribuicaoLogArray['codigo_contribuicao'])
                ->setDataGeracao($contribuicaoLogArray['data_geracao'])
                ->setDataPagamento($contribuicaoLogArray['data_pagamento'])
                ->setStatusPagamento($contribuicaoLogArray['status_pagamento']);

            $contribuicaoLogCollection->add($contribuicaoLog);
        }

        return $contribuicaoLogCollection;
    }
}
