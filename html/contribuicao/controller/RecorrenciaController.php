<?php
//requisições necessárias
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'RecorrenciaDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SocioDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'MeioPagamentoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'RegraPagamentoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'GatewayPagamentoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'ConexaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Recorrencia.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'Util.php';
class RecorrenciaController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar(); //Considerar implementar injeção de dependência caso a aplicação precise de mais flexibilidade
    }

    /**
     * Cria um objeto do tipo ContribuicaoLog, chama o serviço de cartão de crédito recorrente registrado no banco de dados
     * e insere a operação na tabela de contribuicao_log caso o serviço seja executado com sucesso.
     */
    public function criarAssinatura() //<-- Considerar mover para uma nova controladora de recorrências 
    {
        $valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
        $documento = filter_input(INPUT_POST, 'documento_socio');
        $formaPagamento = 'Recorrencia';

        try {
            $this->pdo->beginTransaction();

            // Buscar sócio
            $socioDao = new SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorDocumento($documento);

            if (is_null($socio)) {
                throw new Exception('Sócio não encontrado');
            }

            // Buscar meio de pagamento
            $meioPagamentoDao = new MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome($formaPagamento);

            if (is_null($meioPagamento)) {
                throw new Exception('Meio de pagamento não encontrado');
            }

            // Verificar se o meio de pagamento está ativo
            if (!$meioPagamento->getStatus()) {
                throw new Exception('Meio de pagamento indisponível');
            }

            // Verificar regras de pagamento
            $regraPagamentoDao = new RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento(
                $meioPagamento->getId()
            );

            Util::verificarRegras($valor, $conjuntoRegrasPagamento);

            // Buscar gateway de pagamento
            $gatewayPagamentoDao = new GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray) {
                throw new Exception('Gateway de pagamento não encontrado');
            }

            $gatewayPagamento = new GatewayPagamento(
                $gatewayPagamentoArray['plataforma'],
                $gatewayPagamentoArray['endPoint'],
                $gatewayPagamentoArray['token'],
                $gatewayPagamentoArray['status']
            );
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            // Carregar serviço de pagamento
            $requisicaoServico = '../service/' . $gatewayPagamento->getNome() . $formaPagamento . 'Service.php';

            if (!file_exists($requisicaoServico)) {
                throw new Exception('Serviço de pagamento não encontrado');
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . $formaPagamento . 'Service';

            if (!class_exists($classeService)) {
                throw new Exception('Classe do serviço não encontrada');
            }

            $servicoPagamento = new $classeService();

            //começar a alterar daqui para baixo
            $contribuicaoLog = new ContribuicaoLog();

            //Criar registro de recorrência
            require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'RecorrenciaDAO.php';
            $recorrenciaDao = new RecorrenciaDAO($this->pdo);
            $recorrencia = new Recorrencia($recorrenciaDao);
            $recorrencia
                ->setValor($valor)
                ->setCodigo($contribuicaoLog->gerarCodigo())
                ->setInicio(new DateTime('now'))
                ->setSocio($socio)
                ->setGatewayPagamento($gatewayPagamento)
                ->setStatus(true);

            $recorrencia->create();

            // Criar assinatura
            $codigoAssinatura = $servicoPagamento->criarAssinatura($recorrencia);

            if (empty($codigoAssinatura)) {
                throw new Exception('Falha ao criar assinatura');
            }

            // Atualizar registro com código da assinatura
            $recorrenciaDao->alterarCodigoPorId($codigoAssinatura, $this->pdo->lastInsertId());

            // Registrar log do sócio
            $mensagem = "Assinatura mensal criada - ID: $codigoAssinatura";
            $socioDao->registrarLog($socio, $mensagem);

            $this->pdo->commit();

            // Mensagem de sucesso com detalhes
            $diaCobranca = date('d');
            echo json_encode([
                'sucesso' => true,
                'mensagem' => "Assinatura criada com sucesso! Cobranças mensais no dia $diaCobranca.",
                'assinatura_id' => $codigoAssinatura
            ]);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            http_response_code(400);
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }
}
