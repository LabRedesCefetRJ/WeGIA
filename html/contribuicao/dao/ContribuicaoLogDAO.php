<?php
//requisitar arquivo de conexão
require_once '../dao/ConexaoDAO.php';

//requisitar model
require_once '../model/ContribuicaoLog.php';
require_once '../model/ContribuicaoLogCollection.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'StatusPagamento.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'ConfiguracaoRelatorioContribuicoes.php';

class ContribuicaoLogDAO
{
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $this->pdo = ConexaoDAO::conectar();
        } else {
            $this->pdo = $pdo;
        }
    }

    public function criar(ContribuicaoLog $contribuicaoLog)
    {
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

    public function alterarCodigoPorId($codigo, $id)
    {
        $sqlPagarPorId = "UPDATE contribuicao_log SET codigo =:codigo WHERE id=:id";

        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    public function pagarPorId($id)
    {
        $sqlPagarPorId = "UPDATE contribuicao_log SET status_pagamento = 1 WHERE id=:id";

        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':id', $id);

        $stmt->execute();
    }

    /**
     * Realiza a alteração do status de pagamento no BD para 1 referente a contribuição que possui o código passado como parâmetro
     */
    public function pagarPorCodigo(string $codigo, string $dataPagamento): void
    {
        $sqlPagarPorId = "UPDATE contribuicao_log SET status_pagamento = 1, data_pagamento=:dataPagamento WHERE codigo=:codigo";

        $stmt = $this->pdo->prepare($sqlPagarPorId);
        $stmt->bindParam(':dataPagamento', $dataPagamento);
        $stmt->bindParam(':codigo', $codigo);

        $stmt->execute();
    }

    public function listarPorDocumento(string $documento)
    {
        $sql = "SELECT cl.id, cl.codigo, cl.valor, cl.data_geracao, cl.data_vencimento, cl.status_pagamento FROM contribuicao_log cl JOIN socio s ON (cl.id_socio=s.id_socio) JOIN pessoa p ON(s.id_pessoa=p.id_pessoa) WHERE cpf=:documento";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':documento', $documento);

        $stmt->execute();

        if ($stmt->rowCount() < 1) {
            return null;
        }

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $contribuicaoLogCollection = new ContribuicaoLogCollection();

        foreach ($resultado as $contribuicaoLog) {
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

    /**
     * Retorna um array das contribuições armazenadas no BD da aplicação.
     * 
     * Null retorna todas as contribuições, independente do status.
     * 
     * Paid retorna contribuições pagas.
     * 
     * Pending retorna contribuições pendentes.
     */
    public function getContribuicoes(?StatusPagamento $statusPagamento = null)
    {
        $sql =
            'SELECT 
            cl.codigo, 
            p.nome as nomeSocio, 
            cl.data_geracao as dataGeracao, 
            cl.data_vencimento as dataVencimento, 
            cl.data_pagamento as dataPagamento, 
            cl.valor, 
            cl.status_pagamento as status,
            cg.plataforma as plataforma,
            cm.meio as meio  
        FROM contribuicao_log cl 
        JOIN socio s ON (s.id_socio=cl.id_socio) 
        JOIN pessoa p ON (p.id_pessoa=s.id_pessoa) 
        JOIN contribuicao_gatewayPagamento as cg ON (cg.id=cl.id_gateway) 
        JOIN contribuicao_meioPagamento as cm ON (cm.id=cl.id_meio_pagamento)';

        if (!is_null($statusPagamento)) {
            match ($statusPagamento) {
                StatusPagamento::Paid => $sql .= ' WHERE cl.status_pagamento=1',
                StatusPagamento::Pending => $sql .= ' WHERE cl.status_pagamento=0'
            };
        }

        $sql .= ' ORDER BY cl.data_geracao DESC';

        $contribuicoesArray = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return $contribuicoesArray;
    }

    public function getAgradecimento()
    {
        $sql = "SELECT paragrafo FROM selecao_paragrafo WHERE nome_campo = 'agradecimento_doador'";

        $agradecimento = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC)['paragrafo'];

        if ($agradecimento && strlen($agradecimento) > 0) {
            return $agradecimento;
        } else {
            return 'Obrigado pela contribuição!';
        }
    }

    /**
     * Retorna os dados da base de dados para a montagem do relatório
     */
    public function getRelatorio(ConfiguracaoRelatorioContribuicoes $configuracao)
    {
        $where = '';
        $conditions = [];
        $params = [];

        $sql =
            'SELECT 
            cl.id,
            cl.codigo, 
            p.nome as nomeSocio, 
            cl.data_geracao as dataGeracao, 
            cl.data_vencimento as dataVencimento, 
            cl.data_pagamento as dataPagamento, 
            cl.valor, 
            cl.status_pagamento as status,
            cg.plataforma as plataforma,
            cm.meio as meio  
        FROM contribuicao_log cl 
        JOIN socio s ON (s.id_socio=cl.id_socio) 
        JOIN pessoa p ON (p.id_pessoa=s.id_pessoa) 
        JOIN contribuicao_gatewayPagamento as cg ON (cg.id=cl.id_gateway) 
        JOIN contribuicao_meioPagamento as cm ON (cm.id=cl.id_meio_pagamento) ';

        if ($configuracao->getPeriodo() != 1) {
            $dataInicio = null;
            $dataFim = null;
            $agora = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

            switch ($configuracao->getPeriodo()) {
                case 2: // Mês atual
                    $dataInicio = (clone $agora)->modify('first day of this month')->setTime(0, 0, 0);
                    $dataFim    = (clone $agora)->modify('last day of this month')->setTime(23, 59, 59);
                    break;

                case 3: // Mês passado
                    $dataInicio = (clone $agora)->modify('first day of last month')->setTime(0, 0, 0);
                    $dataFim    = (clone $agora)->modify('last day of last month')->setTime(23, 59, 59);
                    break;

                case 4: // Bimestre anterior
                    $mesAtual = (int) $agora->format('n');
                    $anoAtual = (int) $agora->format('Y');
                    $mesInicio = $mesAtual - (($mesAtual - 1) % 2) - 2;
                    if ($mesInicio < 1) {
                        $mesInicio += 12;
                        $anoAtual -= 1;
                    }
                    $dataInicio = new DateTime("$anoAtual-$mesInicio-01", new DateTimeZone('America/Sao_Paulo'));
                    $dataFim    = (clone $dataInicio)->modify('+1 month')->modify('last day of this month')->setTime(23, 59, 59);
                    break;

                case 5: // Trimestre anterior
                    $mesAtual = (int) $agora->format('n');
                    $anoAtual = (int) $agora->format('Y');
                    $mesInicio = $mesAtual - (($mesAtual - 1) % 3) - 3;
                    if ($mesInicio < 1) {
                        $mesInicio += 12;
                        $anoAtual -= 1;
                    }
                    $dataInicio = new DateTime("$anoAtual-$mesInicio-01", new DateTimeZone('America/Sao_Paulo'));
                    $dataFim    = (clone $dataInicio)->modify('+2 months')->modify('last day of this month')->setTime(23, 59, 59);
                    break;

                case 6: // Semestre anterior
                    $anoAtual = (int) $agora->format('Y');
                    $mesAtual = (int) $agora->format('n');
                    if ($mesAtual <= 6) {
                        $dataInicio = new DateTime(($anoAtual - 1) . '-07-01', new DateTimeZone('America/Sao_Paulo'));
                        $dataFim    = new DateTime(($anoAtual - 1) . '-12-31 23:59:59', new DateTimeZone('America/Sao_Paulo'));
                    } else {
                        $dataInicio = new DateTime("$anoAtual-01-01", new DateTimeZone('America/Sao_Paulo'));
                        $dataFim    = new DateTime("$anoAtual-06-30 23:59:59", new DateTimeZone('America/Sao_Paulo'));
                    }
                    break;

                case 7: // Ano atual
                    $anoAtual = (int) $agora->format('Y');
                    $dataInicio = new DateTime("$anoAtual-01-01", new DateTimeZone('America/Sao_Paulo'));
                    $dataFim    = new DateTime("$anoAtual-12-31 23:59:59", new DateTimeZone('America/Sao_Paulo'));
                    break;

                case 8: // Ano passado
                    $anoPassado = ((int) $agora->format('Y')) - 1;
                    $dataInicio = new DateTime("$anoPassado-01-01", new DateTimeZone('America/Sao_Paulo'));
                    $dataFim    = new DateTime("$anoPassado-12-31 23:59:59", new DateTimeZone('America/Sao_Paulo'));
                    break;
            }
        }

        // Exemplo de uso:
        if ((isset($dataInicio) && !is_null($dataInicio)) && (isset($dataFim) && !is_null($dataFim))) {
            switch ($configuracao->getStatus()) {
                case 1: // Todos (qualquer uma das datas no intervalo)
                    $conditions[] = '(cl.data_geracao BETWEEN :data_inicio AND :data_fim
                                      OR cl.data_vencimento BETWEEN :data_inicio AND :data_fim
                                      OR cl.data_pagamento BETWEEN :data_inicio AND :data_fim)';
                    break;
                case 2: // Emitida
                    $conditions[] = 'cl.data_geracao BETWEEN :data_inicio AND :data_fim';
                    break;
                case 3: // Vencida
                    $conditions[] = 'cl.data_vencimento BETWEEN :data_inicio AND :data_fim AND cl.status_pagamento=0';
                    break;
                case 4: // Paga
                    $conditions[] = 'cl.data_pagamento BETWEEN :data_inicio AND :data_fim';
                    break;
            }

            // Parâmetros para bind
            $params[':data_inicio'] = $dataInicio->format('Y-m-d H:i:s');
            $params[':data_fim']    = $dataFim->format('Y-m-d H:i:s');
        } else if ($configuracao->getStatus() === 4) {
            $conditions[] = 'cl.status_pagamento=1';
        }

        // Filtro por sócio (se for diferente de 0)
        $socioId = $configuracao->getSocioId();
        if ($socioId !== 0) {
            $conditions[] = 'cl.id_socio = :id_socio';
            $params[':id_socio'] = $socioId;
        }

        // Junta as condições
        if (count($conditions) > 0) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        if($configuracao->getStatus()!=1 && $configuracao->getStatus()!=2){
            switch($configuracao->getStatus()){
                case 3: $sql .= $where . ' ORDER BY cl.data_vencimento DESC';break;
                case 4: $sql .= $where . ' ORDER BY cl.data_pagamento DESC';break;
            }
        }else{
            $sql .= $where . ' ORDER BY cl.data_geracao DESC';
        }

        //return $sql; //Parada para testar o SQL montado

        //Prepara a consulta e pega os resultados no banco de dados
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultados;
    }
}
