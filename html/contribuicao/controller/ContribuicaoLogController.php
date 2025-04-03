<?php
//Requisições necessárias
require_once '../model/ContribuicaoLog.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../model/Socio.php';
require_once '../dao/SocioDAO.php';
require_once '../dao/MeioPagamentoDAO.php';
require_once '../dao/GatewayPagamentoDAO.php';
require_once '../dao/RegraPagamentoDAO.php';
require_once '../model/GatewayPagamento.php';
require_once '../model/ContribuicaoLogCollection.php';
require_once '../model/StatusPagamento.php';
require_once '../../../config.php';

class ContribuicaoLogController
{

    private $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar(); //Considerar implementar injeção de dependência caso a aplicação precise de mais flexibilidade
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de boleto registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarBoleto() //Talvez seja melhor separar em: criarBoleto, criarCarne e criarPix
    {
        $valor = filter_input(INPUT_POST, 'valor');
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $formaPagamento = 'Boleto';

        //Verificar se existe um sócio que possua de fato o documento
        try {
            $socioDao = new SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                echo json_encode(['erro' => 'Sócio não encontrado']);
                exit();
            }

            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                echo json_encode(['erro' => 'Meio de pagamento não encontrado']);
                exit();
            }

            //Verificar regras
            $regraPagamentoDao = new RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento($meioPagamento->getId());

            $this->verificarRegras($valor, $conjuntoRegrasPagamento);

            //Procura pelo serviço de pagamento através do id do gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray || count($gatewayPagamentoArray) < 1) {
                echo json_encode(['erro' => 'Gateway de pagamento não encontrado']);
                exit();
            }

            $gatewayPagamento = new GatewayPagamento($gatewayPagamentoArray['plataforma'], $gatewayPagamentoArray['endPoint'], $gatewayPagamentoArray['token'], $gatewayPagamentoArray['status']);
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            //Requisição dinâmica e instanciação da classe com base no nome do gateway de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service' . '.php';

            if (!file_exists($requisicaoServico)) {
                echo json_encode(['erro' => 'Arquivo não encontrado']);
                exit();
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                echo json_encode(['erro' => 'Classe não encontrada']);
                exit();
            }

            $servicoPagamento = new $classeService;
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
            exit();
        }

        //Verificar qual fuso horário será utilizado posteriormente

        if (isset($_POST['dia']) && !empty($_POST['dia'])) {
            require_once '../../permissao/permissao.php';

            session_start();
            permissao($_SESSION['id_pessoa'], 4);

            $dataGeracao = date('Y-m-d');
            $dataVencimento = $_POST['dia'];
        } else {
            $dataGeracao = date('Y-m-d');
            $dataVencimento = date_modify(new DateTime(), '+7 day')->format('Y-m-d');
        }

        $contribuicaoLog = new ContribuicaoLog();
        $contribuicaoLog
            ->setValor($valor)
            ->setCodigo($contribuicaoLog->gerarCodigo())
            ->setDataGeracao($dataGeracao)
            ->setDataVencimento($dataVencimento)
            ->setSocio($socio)
            ->setGatewayPagamento($gatewayPagamento)
            ->setMeioPagamento($meioPagamento);

        try {
            /*Controle de transação para que o log só seja registrado
            caso o serviço de pagamento tenha sido executado*/
            $this->pdo->beginTransaction();
            $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);
            $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);

            //Adicionar mensagem de agradecimento
            $agradecimento = $contribuicaoLogDao->getAgradecimento();
            $contribuicaoLog->setAgradecimento($agradecimento);

            //Registrar na tabela de socio_log
            $mensagem = "Boleto gerado recentemente";
            $socioDao->registrarLog($contribuicaoLog->getSocio(), $mensagem);

            $codigoApi = $servicoPagamento->gerarBoleto($contribuicaoLog);

            //Chamada do método de serviço de pagamento requisitado
            if (!$codigoApi) {
                $this->pdo->rollBack();
            } else {
                $contribuicaoLogDao->alterarCodigoPorId($codigoApi, $contribuicaoLog->getId());
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de carne registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarCarne()
    {
        $valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $qtdParcelas = filter_input(INPUT_POST, 'parcelas', FILTER_VALIDATE_INT);
        $diaVencimento = filter_input(INPUT_POST, 'dia', FILTER_VALIDATE_INT);
        $formaPagamento = 'Carne';

        //Verificar se existe um sócio que possua de fato o documento
        try {
            $socioDao = new SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                echo json_encode(['erro' => 'Sócio não encontrado']);
                exit();
            }

            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                echo json_encode(['erro' => 'Meio de pagamento não encontrado']);
                exit();
            }

            //Verificar regras
            $regraPagamentoDao = new RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento($meioPagamento->getId());

            $this->verificarRegras($valor, $conjuntoRegrasPagamento);

            //Procura pelo serviço de pagamento através do id do gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray || count($gatewayPagamentoArray) < 1) {
                echo json_encode(['erro' => 'Gateway de pagamento não encontrado']);
                exit();
            }

            $gatewayPagamento = new GatewayPagamento($gatewayPagamentoArray['plataforma'], $gatewayPagamentoArray['endPoint'], $gatewayPagamentoArray['token'], $gatewayPagamentoArray['status']);
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            //Requisição dinâmica e instanciação da classe com base no nome do gateway de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service' . '.php';

            if (!file_exists($requisicaoServico)) {
                echo json_encode(['erro' => 'Arquivo não encontrado']);
                exit();
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                echo json_encode(['erro' => 'Classe não encontrada']);
                exit();
            }

            $servicoPagamento = new $classeService;

            /*Controle de transação para que o log só seja registrado
            caso o serviço de pagamento tenha sido executado*/
            $this->pdo->beginTransaction();

            $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);

            //Adicionar mensagem de agradecimento
            $agradecimento = $contribuicaoLogDao->getAgradecimento();

            //Criar coleção de contribuições
            $contribuicaoLogCollection = new ContribuicaoLogCollection();

            if (!$qtdParcelas || $qtdParcelas < 2) {
                //implementar mensagem de erro
                echo json_encode(['erro' => 'O mínimo de parcelas deve ser 2']);
                exit();
            }

            // Pegar a data atual
            $dataAtual = new DateTime();

            if (isset($_POST['tipoGeracao']) && !empty($_POST['tipoGeracao'])) {
                //verificar autenticação do funcionário
                require_once '../../permissao/permissao.php';

                session_start();
                permissao($_SESSION['id_pessoa'], 4);

                //escolher qual ação tomar
                $tipoGeracao = $_POST['tipoGeracao'];

                //chamar funções
                require_once '../helper/Util.php';

                $datasVencimento;

                $diaVencimento = ($_POST['dia']);

                $qtd_p = intval($_POST['parcelas']);

                switch ($tipoGeracao) {
                    case '1':
                        $datasVencimento = Util::mensalidadeInterna(1, $qtd_p, $diaVencimento);
                        break;
                    case '2':
                        $datasVencimento = Util::mensalidadeInterna(2, $qtd_p, $diaVencimento);
                        break;
                    case '3':
                        $datasVencimento = Util::mensalidadeInterna(3, $qtd_p, $diaVencimento);
                        break;
                    case '6':
                        $datasVencimento = Util::mensalidadeInterna(6, $qtd_p, $diaVencimento);
                        break;
                    default:
                        echo json_encode(['erro' => 'O tipo de geração é inválido.']);
                        exit();
                }

                foreach ($datasVencimento as $dataVencimento) {
                    $contribuicaoLog = new ContribuicaoLog();
                    $contribuicaoLog
                        ->setValor($valor)
                        ->setCodigo($contribuicaoLog->gerarCodigo())
                        ->setDataGeracao($dataAtual->format('Y-m-d'))
                        ->setDataVencimento($dataVencimento)
                        ->setSocio($socio)
                        ->setGatewayPagamento($gatewayPagamento)
                        ->setMeioPagamento($meioPagamento)
                        ->setAgradecimento($agradecimento);

                    //inserir na collection o resultado do método criar de contribuicaoDao 
                    $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);

                    $contribuicaoLogCollection->add($contribuicaoLog);
                }
            } else {

                $diasPermitidos = [1, 5, 10, 15, 20, 25];

                if (!in_array($diaVencimento, $diasPermitidos)) {
                    http_response_code(400);
                    echo json_encode(['erro' => 'Dia de vencimento inválido']);
                    exit();
                }

                // Verificar se o dia informado já passou neste mês
                if ($diaVencimento <= $dataAtual->format('d')) {
                    // Se o dia informado já passou, começar a partir do próximo mês
                    $dataGeracao = $dataAtual->format('Y-m-d');
                    $dataAtual->modify('first day of next month');
                } else {
                    $dataGeracao = $dataAtual->format('Y-m-d');
                }

                for ($i = 0; $i < $qtdParcelas; $i++) {
                    // Clonar a data atual para evitar modificar o objeto original
                    $dataVencimento = clone $dataAtual;

                    // Adicionar os meses de acordo com o índice da parcela
                    $dataVencimento->modify("+{$i} month");

                    // Definir o dia do vencimento para o dia informado
                    $dataVencimento->setDate($dataVencimento->format('Y'), $dataVencimento->format('m'), $diaVencimento);

                    // Ajustar a data caso o mês não tenha o dia informado (por exemplo, 30 de fevereiro)
                    if ($dataVencimento->format('d') != $diaVencimento) {
                        $dataVencimento->modify('last day of previous month');
                    }

                    $contribuicaoLog = new ContribuicaoLog();
                    $contribuicaoLog
                        ->setValor($valor)
                        ->setCodigo($contribuicaoLog->gerarCodigo())
                        ->setDataGeracao($dataGeracao)
                        ->setDataVencimento($dataVencimento->format('Y-m-d'))
                        ->setSocio($socio)
                        ->setGatewayPagamento($gatewayPagamento)
                        ->setMeioPagamento($meioPagamento)
                        ->setAgradecimento($agradecimento);

                    //inserir na collection o resultado do método criar de contribuicaoDao 
                    $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);

                    $contribuicaoLogCollection->add($contribuicaoLog);
                }
            }

            //Registrar na tabela de socio_log
            $mensagem = "Carnê gerado recentemente";
            $socioDao->registrarLog($contribuicaoLog->getSocio(), $mensagem);

            //Chamada do método de serviço de pagamento requisitado

            //Método deverá retornar o caminho do carne e um array de contribuicões log
            $resultado = $servicoPagamento->gerarCarne($contribuicaoLogCollection);
            if (!$resultado || empty($resultado)) {
                $this->pdo->rollBack();
            } else {
                //loop foreach para alterar o código no banco de dados das respectivas contribuições recebidas
                foreach ($resultado['contribuicoes'] as $contribuicao) {
                    $contribuicaoLogDao->alterarCodigoPorId($contribuicao->getCodigo(), $contribuicao->getId());
                }

                $this->pdo->commit();

                echo json_encode(['link' => WWW . 'html/contribuicao/' . $resultado['link']]);
            }
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de pix registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarQRCode()
    {
        $valor = filter_input(INPUT_POST, 'valor');
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $formaPagamento = 'Pix';

        //Verificar se existe um sócio que possua de fato o documento
        try {
            $socioDao = new SocioDAO();
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                //Colocar uma mensagem para informar que o sócio não existe
                exit('Sócio não encontrado');
            }

            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                //Colocar uma mensagem para informar que o meio de pagamento não existe
                exit('Meio de pagamento não encontrado');
            }

            //Verificar regras
            $regraPagamentoDao = new RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento($meioPagamento->getId());

            $this->verificarRegras($valor, $conjuntoRegrasPagamento);

            //Procura pelo serviço de pagamento através do id do gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray || count($gatewayPagamentoArray) < 1) {
                echo json_encode(['erro' => 'Gateway de pagamento não encontrado']);
                exit();
            }

            $gatewayPagamento = new GatewayPagamento($gatewayPagamentoArray['plataforma'], $gatewayPagamentoArray['endPoint'], $gatewayPagamentoArray['token'], $gatewayPagamentoArray['status']);
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            //Requisição dinâmica e instanciação da classe com base no nome do gateway de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service' . '.php';

            if (!file_exists($requisicaoServico)) {
                //implementar feedback
                exit('Arquivo não encontrado');
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                //implementar feedback
                exit('Classe não encontrada');
            }

            $servicoPagamento = new $classeService;
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
            exit();
        }

        //Verificar qual fuso horário será utilizado posteriormente
        $dataGeracao = date('Y-m-d');
        $dataVencimento = date_modify(new DateTime(), '+1 day')->format('Y-m-d');

        $contribuicaoLog = new ContribuicaoLog();
        $contribuicaoLog
            ->setValor($valor)
            ->setCodigo($contribuicaoLog->gerarCodigo())
            ->setDataGeracao($dataGeracao)
            ->setDataVencimento($dataVencimento)
            ->setSocio($socio)
            ->setGatewayPagamento($gatewayPagamento)
            ->setMeioPagamento($meioPagamento);

        try {
            /*Controle de transação para que o log só seja registrado
            caso o serviço de pagamento tenha sido executado*/
            $this->pdo->beginTransaction();
            $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);
            $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);

            //Adicionar mensagem de agradecimento
            $agradecimento = $contribuicaoLogDao->getAgradecimento();
            $contribuicaoLog->setAgradecimento($agradecimento);

            //Registrar na tabela de socio_log
            $mensagem = "Pix gerado recentemente";
            $socioDao->registrarLog($contribuicaoLog->getSocio(), $mensagem);

            //Chamada do método de serviço de pagamento requisitado

            $codigoApi = $servicoPagamento->gerarQrCode($contribuicaoLog);

            if (!$codigoApi) {
                $this->pdo->rollBack();
            } else {
                $contribuicaoLogDao->alterarCodigoPorId($codigoApi, $contribuicaoLog->getId());
                $this->pdo->commit();
            }
        } catch (PDOException $e) {
            //implementar tratamento de erro
            echo 'Erro: ' . $e->getMessage();
        }
    }

    /**
     * Extraí o id da requisição POST e muda o status de pagamento da contribuição correspondente.
     */
    public function pagarPorId()
    {
        $idContribuicaoLog = filter_input(INPUT_POST, 'id_contribuicao');

        if (!$idContribuicaoLog || $idContribuicaoLog < 1) {
            http_response_code(400);
            exit('O id fornecido não é válido'); //substituir posteriormente por redirecionamento com mensagem de feedback
        }

        try {
            $contribuicaoLogDao = new ContribuicaoLogDAO();
            $contribuicaoLogDao->pagarPorId($idContribuicaoLog);
        } catch (PDOException $e) {
            echo 'Erro: ' . $e->getMessage(); //substituir posteriormente por redirecionamento com mensagem de feedback
        }
    }

    /**
     * Realiza a sincronização entre os status das contribuições no BD da aplicação e os status nos gateways de pagamentos
     */
    public function sincronizarStatus(): void
    {
        try {
            // Pegar gateways de pagamentos
            $gatewayPagamentoDao = new GatewayPagamentoDAO($this->pdo);
            $gatewaysArray = $gatewayPagamentoDao->buscaTodos();

            // Buscar contribuições internas pendentes
            $contribuicaoLogDao = new ContribuicaoLogDAO($this->pdo);
            $contribuicoesPendentesArray = $contribuicaoLogDao->getContribuicoes(StatusPagamento::Pending);

            // Buscar contribuições de APIs externas pagas
            $contribuicoesExternas = new ContribuicaoLogCollection();
            foreach ($gatewaysArray as $gateway) {
                $api = $gateway['plataforma'] . 'ContribuicoesService';
                $caminhoArquivo = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . $api . '.php';

                if (file_exists($caminhoArquivo)) {
                    require_once $caminhoArquivo;
                }

                if (class_exists($api)) {
                    $apiContribuicoesService = new $api;

                    if ($apiContribuicoesService instanceof $api && method_exists($apiContribuicoesService, 'getContribuicoes')) {
                        foreach ($apiContribuicoesService->getContribuicoes('paid') as $contribuicao) {
                            $contribuicoesExternas->add($contribuicao);
                        }
                    }
                }
            }

            // Identificar contribuições pagas
            $atualizou = false;
            $this->pdo->beginTransaction();

            foreach ($contribuicoesPendentesArray as $contribuicaoPendente) {
                $contribuicaoLog = $contribuicoesExternas->findByCodigo($contribuicaoPendente['codigo']);

                if (!is_null($contribuicaoLog)) {
                    // Atualizar status
                    $contribuicaoLogDao->pagarPorCodigo(
                        $contribuicaoLog->getCodigo(),
                        $contribuicaoLog->getDataPagamento()
                    );
                    $atualizou = true;
                }
            }

            if ($atualizou) {
                $this->pdo->commit();
            } else {
                $this->pdo->rollBack();
            }

            echo json_encode(['sucesso' => 'Sincronização realizada com sucesso']);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log("[ERRO] {$e->getMessage()} em {$e->getFile()} na linha {$e->getLine()}");
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno ao sincronizar as contribuições']);
        }
    }

    /**
     * Retorna um JSON das contribuições registradas no banco de dados da aplicação
     */
    public function getContribuicoesLogJSON()
    {
        try {
            $contribuicaoLogDao = new ContribuicaoLogDAO();
            $contribuicoes = $contribuicaoLogDao->getContribuicoes();

            echo json_encode(["data" => $contribuicoes]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao buscar contribuições no banco de dados.']);
        }
    }

    private function verificarRegras($valor, $conjuntoRegrasPagamento)
    {
        if ($conjuntoRegrasPagamento && count($conjuntoRegrasPagamento) > 0) {
            foreach ($conjuntoRegrasPagamento as $regraPagamento) {
                if ($regraPagamento['id_regra'] == 1) {
                    if ($valor < $regraPagamento['valor']) {
                        echo json_encode(['erro' => "O valor informado está abaixo do permitido (R\${$regraPagamento['valor']})."]);
                        exit;
                    }
                } else if ($regraPagamento['id_regra'] == 2) {
                    if ($valor > $regraPagamento['valor']) {
                        echo json_encode(['erro' => "O valor informado está acima do permitido (R\${$regraPagamento['valor']})."]);
                        exit;
                    }
                }
            }
        }
    }
}
