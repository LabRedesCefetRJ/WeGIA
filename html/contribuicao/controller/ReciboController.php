<?php
require_once '../model/Recibo.php';
require_once '../model/Socio.php';
require_once '../dao/ReciboDAO.php';
require_once '../dao/SocioDAO.php';
require_once '../dao/ContribuicaoLogDAO.php';
require_once '../dao/ConexaoDAO.php';
require_once '../service/PdfService.php';
require_once dirname(__DIR__, 3) . '/controle/EmailControle.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ContatoInstituicao.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Csrf.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'EmailConfigDAO.php';
date_default_timezone_set('America/Sao_Paulo');

class ReciboController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = ConexaoDAO::conectar();
    }

    /**
     * Gerar recibo de doação para um sócio
     */
    public function gerarRecibo()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $suporte = ContatoInstituicao::listarPorId(1);

            //verificar se smtp está ativo
            $emailDao = new EmailConfigDAO($this->pdo);
            if (!$emailDao->existeConfiguracaoAtiva()) {
                http_response_code(500);
                echo json_encode([
                    'erro' => 'Sistema de envio por e-mail não está ativo.',
                    'suporte' => $suporte->getContato()
                ]);
                exit();
            }

            // Sanitizar entrada
            $cpf = filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_SPECIAL_CHARS);
            $dataInicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_SPECIAL_CHARS);
            $dataFim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_SPECIAL_CHARS);

            // Validações básicas
            if (empty($cpf)) {
                echo json_encode(['erro' => 'CPF é obrigatório']);
                exit;
            }

            if (empty($dataInicio) || empty($dataFim)) {
                echo json_encode(['erro' => 'Datas são obrigatórias']);
                exit;
            }

            // Validação de datas
            $dtInicio = DateTime::createFromFormat('Y-m-d', $dataInicio);
            $dtFim = DateTime::createFromFormat('Y-m-d', $dataFim);

            if (!$dtInicio || !$dtFim) {
                echo json_encode(['erro' => 'Formato de data inválido']);
                exit;
            }

            if ($dtInicio > $dtFim) {
                echo json_encode(['erro' => 'Data inicial não pode ser maior que a data final']);
                exit;
            }

            // Buscar sócio
            $socioDAO = new SocioDAO($this->pdo);
            $socio = $socioDAO->buscarPorDocumento($cpf);

            if (!$socio) {
                echo json_encode([
                    'erro' => 'Doador não localizado: Verifique se o CPF digitado está correto.',
                    'suporte' => $suporte->getContato()
                ]);
                exit;
            }

            // Validar email do sócio
            if (empty($socio->getEmail()) || !filter_var($socio->getEmail(), FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'erro' => 'Doador não possui email válido cadastrado: Atualize seus dados para conseguir completar a requisição.',
                    'suporte' => $suporte->getContato()
                ]);
                exit;
            }

            // Buscar contribuições no período
            $contribuicaoLogDAO = new ContribuicaoLogDAO($this->pdo);
            $contribuicoes = $contribuicaoLogDAO->getContribuicoesPorSocioEPeriodo(
                $socio->getId(),
                $dtInicio->format('Y-m-d'),
                $dtFim->format('Y-m-d')
            );

            if (empty($contribuicoes)) {
                echo json_encode([
                    'erro' => 'Nenhuma contribuição encontrada no período de tempo informado: Experimente realizar uma consulta com datas diferentes.',
                    'suporte' => $suporte->getContato()
                ]);
                exit;
            }

            // Calcular valor total
            $valorTotal = 0;
            foreach ($contribuicoes as $contribuicao) {
                $valorTotal += floatval($contribuicao['valor']);
            }

            if ($valorTotal <= 0) {
                echo json_encode(['erro' => 'Valor total das contribuições deve ser maior que zero']);
                exit;
            }

            // Iniciar transação
            $this->pdo->beginTransaction();

            // Criar recibo
            $recibo = new Recibo();
            $recibo->setIdSocio($socio->getId())
                ->setCodigo(bin2hex(random_bytes(8)))
                ->setEmail($socio->getEmail())
                ->setDataInicio($dtInicio)
                ->setDataFim($dtFim)
                ->setValorTotal($valorTotal)
                ->setTotalContribuicoes(count($contribuicoes))
                ->setContribuicoes($contribuicoes);

            // Gerar PDF
            $pdfService = new PdfService();

            $arquivo = $pdfService->gerarRecibo($recibo, $socio);
            $recibo->setArquivo($arquivo);

            // Salvar no banco
            $reciboDAO = new ReciboDAO($this->pdo);
            $reciboDAO->salvar($recibo);

            // Enviar email
            $resultadoEmail = $this->enviarEmail($recibo, $socio);

            // Registrar log do sócio
            $mensagem = "Comprovante gerado - Código: " . $recibo->getCodigo();
            $socioDAO->registrarLog($socio, $mensagem, Util::getUserIp(), Util::getUserAgent());

            $this->pdo->commit();

            // Resposta de sucesso
            $response = [
                'sucesso' => true,
                'codigo' => $recibo->getCodigo(),
                'email' => $recibo->getEmail(true),
                'valor_total' => number_format($valorTotal, 2, ',', '.'),
                'total_contribuicoes' => count($contribuicoes)
            ];

            if ($resultadoEmail['success']) {
                $response['mensagem'] = 'Comprovante gerado e enviado por email com sucesso';
            } else {
                $response['mensagem'] = 'Comprovante gerado com sucesso. Aviso: ' . $resultadoEmail['message'];
                http_response_code(500);
                echo json_encode(
                    [
                        'erro' => $resultadoEmail['message'],
                        'suporte' => $suporte->getContato()
                    ]
                );
                exit();
            }

            echo json_encode($response);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction())
                $this->pdo->rollBack();

            Util::tratarException($e);
        }
    }

    /**
     * Download do recibo por código
     */
    public function download()
    {
        try {
            if (!Csrf::validateToken($_POST['csrf_token'] ?? null))
                throw new InvalidArgumentException('Token CSRF inválido ou ausente.', 401);

            $codigo = filter_input(INPUT_GET, 'codigo', FILTER_SANITIZE_SPECIAL_CHARS);

            if (empty($codigo)) {
                http_response_code(400);
                exit('Código não fornecido');
            }

            $reciboDAO = new ReciboDAO($this->pdo);
            $recibo = $reciboDAO->buscarPorCodigo($codigo);

            if (!$recibo || !file_exists($recibo['caminho_pdf'])) {
                http_response_code(404);
                exit('Comprovante não encontrado');
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="recibo_' . $codigo . '.pdf"');
            header('Content-Length: ' . filesize($recibo['caminho_pdf']));
            readfile($recibo['caminho_pdf']);
            exit;
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    /**
     * Enviar email com recibo
     */
    private function enviarEmail(Recibo $recibo, Socio $socio)
    {
        try {
            $emailControle = new EmailControle($this->pdo);

            // Verificar se o email está configurado
            if (!$emailControle->isEnabled() || !$emailControle->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Sistema de email não está configurado'
                ];
            }

            $assunto = 'Comprovante de Doação - ' . ($emailControle->getConfiguracoes()['smtp_from_name'] ?: 'WeGIA');

            // Mensagem HTML formatada
            $mensagem = sprintf(
                "<p>Prezado(a) %s,</p>
                <p>Anexamos o comprovante de suas doações no período de %s a %s.</p>
                <p><strong>Valor Total: R$ %s</strong></p>
                <p>Atenciosamente,<br>%s</p>",
                htmlspecialchars($socio->getNome()),
                $recibo->getDataInicio()->format('d/m/Y'),
                $recibo->getDataFim()->format('d/m/Y'),
                number_format($recibo->getValorTotal(), 2, ',', '.'),
                htmlspecialchars($emailControle->getConfiguracoes()['smtp_from_name'] ?: 'WeGIA')
            );

            return $emailControle->enviarEmail(
                $recibo->getEmail(),
                $assunto,
                $mensagem,
                $socio->getNome(),
                [$recibo->getArquivo()]
            );
        } catch (Exception $e) {
            error_log("Erro ao enviar email do comprovante: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao enviar email: ' . $e->getMessage()
            ];
        }
    }
}
