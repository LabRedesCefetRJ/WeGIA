<?php

namespace api\modules\Contribuicao;

require_once dirname(__DIR__, 4) . '/web/html/contribuicao/service/PdfService.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/model/ContribuicaoLog.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/dao/ContribuicaoLogDAO.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/dao/MeioPagamentoDAO.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/dao/GatewayPagamentoDAO.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/dao/RegraPagamentoDAO.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/dao/SocioDAO.php';
require_once dirname(__DIR__, 4) . '/web/html/contribuicao/model/GatewayPagamento.php';
require_once dirname(__DIR__, 4) . '/web/classes/Util.php';

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ContribuicaoController
{
    private ContribuicaoService $contribuicaoService;
    private \api\modules\Socio\SocioRepository $socioRepository;
    private \api\modules\Pessoa\PessoaRepository $pessoaRepository;
    private \PDO $pdo;

    public function __construct(
        ContribuicaoService $contribuicaoService,
        \api\modules\Socio\SocioRepository $socioRepository,
        \api\modules\Pessoa\PessoaRepository $pessoaRepository,
        \PDO $pdo
    )
    {
        $this->contribuicaoService = $contribuicaoService;
        $this->socioRepository = $socioRepository;
        $this->pessoaRepository = $pessoaRepository;
        $this->pdo = $pdo;
    }

    /**
     * Validates that the authenticated user can access this socio's data
     *
     * @param Request $request
     * @param int $idSocioRequested
     * @return bool|Response True if authorized, Response error if not
     */
    private function validateSocioOwnership(Request $request, int $idSocioRequested): bool|Response
    {
        $idPessoa = $request->getAttribute('user_id');

        if (!$idPessoa) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Usuário não identificado.'
            ]));
            return $response->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }

        // Get the pessoa ID associated with the requested socio ID
        $idPessoaFromSocio = $this->socioRepository->getIdPessoaByIdSocio($idSocioRequested);
        
        // If the socio doesn't exist or doesn't belong to this pessoa, deny access
        if ($idPessoaFromSocio === null || $idPessoaFromSocio !== $idPessoa) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => 'Acesso negado. Você não tem permissão para acessar os dados de outro sócio.'
            ]));
            return $response->withStatus(403)
                ->withHeader('Content-Type', 'application/json');
        }

        return true;
    }

    private function jsonError(Response $response, string $message, int $statusCode): Response
    {
        $response->getBody()->write(json_encode([
            'error' => $message
        ]));

        return $response->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json');
    }

    private function validarRegrasValor(float $valor, array $regras): ?string
    {
        foreach ($regras as $regraPagamento) {
            $idRegra = (int)($regraPagamento['id_regra'] ?? 0);
            $valorRegra = (float)($regraPagamento['valor'] ?? 0);

            if ($idRegra === 1 && $valor < $valorRegra) {
                return "O valor informado está abaixo do permitido (R\${$valorRegra}).";
            }

            if ($idRegra === 2 && $valor > $valorRegra) {
                return "O valor informado está acima do permitido (R\${$valorRegra}).";
            }
        }

        return null;
    }

    private function resolverDataVencimento(?string $diaInformado): string
    {
        if ($diaInformado !== null && trim($diaInformado) !== '') {
            $dataVencimento = \DateTimeImmutable::createFromFormat('Y-m-d', $diaInformado);
            $errosData = \DateTimeImmutable::getLastErrors();

            if (
                $dataVencimento === false
                || !is_array($errosData)
                || ($errosData['warning_count'] ?? 0) > 0
                || ($errosData['error_count'] ?? 0) > 0
            ) {
                throw new \InvalidArgumentException('Data de vencimento inválida.', 400);
            }

            return $dataVencimento->format('Y-m-d');
        }

        return (new \DateTimeImmutable('now'))->modify('+7 days')->format('Y-m-d');
    }

    private function capturarLinkDaRespostaServico(string $saidaServico): ?string
    {
        $saidaServico = trim($saidaServico);

        if ($saidaServico === '') {
            return null;
        }

        $dados = json_decode($saidaServico, true);
        if (!is_array($dados)) {
            return null;
        }

        return $dados['link'] ?? null;
    }

    private function capturarRespostaPixServico(string $saidaServico): ?array
    {
        $saidaServico = trim($saidaServico);

        if ($saidaServico === '') {
            return null;
        }

        $dados = json_decode($saidaServico, true);
        if (!is_array($dados)) {
            return null;
        }

        if (empty($dados['qrcode']) || empty($dados['copiaCola'])) {
            return null;
        }

        return $dados;
    }

    private function resolverIntervaloCarne(?string $tipoGeracao): int
    {
        if ($tipoGeracao === null || trim($tipoGeracao) === '') {
            return 1;
        }

        $intervalosValidos = [
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '6' => 6
        ];

        $tipoGeracao = trim((string)$tipoGeracao);

        if (!isset($intervalosValidos[$tipoGeracao])) {
            throw new \InvalidArgumentException('O tipo de geração é inválido.', 400);
        }

        return $intervalosValidos[$tipoGeracao];
    }

    private function gerarDatasVencimentoCarne(int $parcelas, int $diaVencimento, int $intervalo = 1): array
    {
        $diasPermitidos = [1, 5, 10, 15, 20, 25];
        if (!in_array($diaVencimento, $diasPermitidos, true)) {
            throw new \InvalidArgumentException('Dia de vencimento inválido.', 400);
        }

        $dataAtual = new \DateTime();
        $dataGeracao = clone $dataAtual;

        if ($diaVencimento <= (int)$dataAtual->format('d')) {
            $dataGeracao->modify('first day of next month');
        }

        $datasVencimento = [];

        for ($i = 0; $i < $parcelas; $i++) {
            $dataVencimento = clone $dataGeracao;
            $dataVencimento->modify('+' . ($intervalo * $i) . ' month');
            $dataVencimento->setDate(
                (int)$dataVencimento->format('Y'),
                (int)$dataVencimento->format('m'),
                $diaVencimento
            );

            if ((int)$dataVencimento->format('d') !== $diaVencimento) {
                $dataVencimento->modify('last day of previous month');
            }

            $datasVencimento[] = $dataVencimento->format('Y-m-d');
        }

        return $datasVencimento;
    }

    /**
     * Get all contributions for a specific socio
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getContribuicoesBySocio(Request $request, Response $response, array $args): Response
    {
        try {
            $idSocio = (int)$args['id'];

            if ($idSocio <= 0) {
                $response->getBody()->write(json_encode([
                    'error' => 'ID do sócio inválido.'
                ]));

                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // Validate socio ownership
            $validation = $this->validateSocioOwnership($request, $idSocio);
            if ($validation instanceof Response) {
                return $validation;
            }

            $contribuicoes = $this->contribuicaoService->obterContribuicoesPorSocio($idSocio);

            if (empty($contribuicoes)) {
                $response->getBody()->write(json_encode([
                    'data' => [],
                    'message' => 'Nenhuma contribuição encontrada para este sócio.'
                ]));

                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            }

            $responseData = $this->contribuicaoService->formatarContribuicoes($contribuicoes, true);

            $response->getBody()->write(json_encode($responseData));

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao recuperar contribuições: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Get contributions filtered by payment status
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getContribuicoesBySocioAndStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $idSocio = (int)$args['id'];
            
            // Get status from query parameter: 'paid' or 'pending', null for all
            $statusParam = $request->getQueryParams()['status'] ?? null;
            $statusPagamento = null;

            if ($statusParam === 'paid') {
                $statusPagamento = true;
            } elseif ($statusParam === 'pending') {
                $statusPagamento = false;
            }

            if ($idSocio <= 0) {
                $response->getBody()->write(json_encode([
                    'error' => 'ID do sócio inválido.'
                ]));

                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // Validate socio ownership
            $validation = $this->validateSocioOwnership($request, $idSocio);
            if ($validation instanceof Response) {
                return $validation;
            }

            $contribuicoes = $this->contribuicaoService->obterContribuicoesPorStatusPagamento($idSocio, $statusPagamento);

            if (empty($contribuicoes)) {
                $response->getBody()->write(json_encode([
                    'data' => [],
                    'message' => 'Nenhuma contribuição encontrada com os filtros especificados.'
                ]));

                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            }

            $responseData = $this->contribuicaoService->formatarContribuicoes($contribuicoes, true);

            $response->getBody()->write(json_encode($responseData));

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao recuperar contribuições: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    /**
     * Get summary of contributions for a specific socio
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getResumoContribuicoes(Request $request, Response $response, array $args): Response
    {
        try {
            $idSocio = (int)$args['id'];

            if ($idSocio <= 0) {
                $response->getBody()->write(json_encode([
                    'error' => 'ID do sócio inválido.'
                ]));

                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            // Validate socio ownership
            $validation = $this->validateSocioOwnership($request, $idSocio);
            if ($validation instanceof Response) {
                return $validation;
            }

            $resumo = $this->contribuicaoService->obterResumoContribuicoes($idSocio);

            $response->getBody()->write(json_encode([
                'resume' => $resumo
            ]));

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao recuperar resumo de contribuições: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function generateContribuicaoPdf(Request $request, Response $response, array $args): Response
    {
        try {
            $idSocio = (int)($args['id'] ?? 0);

            if ($idSocio <= 0) {
                $response->getBody()->write(json_encode([
                    'error' => 'ID do sócio inválido.'
                ]));

                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            $validation = $this->validateSocioOwnership($request, $idSocio);
            if ($validation instanceof Response) {
                return $validation;
            }

            $contribuicoes = $this->contribuicaoService->obterContribuicoesPorSocio($idSocio);

            if (empty($contribuicoes)) {
                $response->getBody()->write(json_encode([
                    'error' => 'Nenhuma contribuição encontrada para este sócio.'
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $idPessoa = (int)$request->getAttribute('user_id');
            $pessoa = $this->pessoaRepository->findById((string)$idPessoa);

            if (!$pessoa) {
                $response->getBody()->write(json_encode([
                    'error' => 'Dados do sócio não encontrados.'
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $pdfService = new \PdfService();
            $pdf = $pdfService->gerarExtratoContribuicoes($contribuicoes, $pessoa);
            $nomeArquivo = sprintf('extrato_contribuicoes_socio_%d.pdf', $idSocio);

            $response->getBody()->write($pdf);

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"')
                ->withHeader('Content-Length', (string)strlen($pdf));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao gerar PDF do extrato: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function generateComprovantePdf(Request $request, Response $response, array $args):Response
    {
        try {
            $idSocio = (int)($args['id'] ?? 0);
            $idContribuicao = (int)($args['contribuicao_id'] ?? 0);

            if ($idSocio <= 0 || $idContribuicao <= 0) {
                $response->getBody()->write(json_encode([
                    'error' => 'ID do sócio ou da contribuição inválido.'
                ]));

                return $response->withStatus(400)
                    ->withHeader('Content-Type', 'application/json');
            }

            $validation = $this->validateSocioOwnership($request, $idSocio);
            if ($validation instanceof Response) {
                return $validation;
            }

            $contribuicao = $this->contribuicaoService->obterContribuicaoPorId($idSocio, $idContribuicao);

            if (!$contribuicao) {
                $response->getBody()->write(json_encode([
                    'error' => 'Contribuição não encontrada para este sócio.'
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $idPessoa = $this->socioRepository->getIdPessoaByIdSocio($idSocio);
            if (!$idPessoa) {
                $response->getBody()->write(json_encode([
                    'error' => 'Dados do sócio não encontrados.'
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $pessoa = $this->pessoaRepository->findById((string)$idPessoa);
            if (!$pessoa) {
                $response->getBody()->write(json_encode([
                    'error' => 'Dados do sócio não encontrados.'
                ]));

                return $response->withStatus(404)
                    ->withHeader('Content-Type', 'application/json');
            }

            $pdfService = new \PdfService();
            $pdf = $pdfService->gerarComprovanteContribuicao($contribuicao, $pessoa);
            $nomeArquivo = sprintf('comprovante_contribuicao_%d.pdf', $idContribuicao);

            $response->getBody()->write($pdf);

            return $response->withStatus(200)
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $nomeArquivo . '"')
                ->withHeader('Content-Length', (string)strlen($pdf));
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao gerar PDF do comprovante: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function generateBoleto(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $idPessoa = (int)$request->getAttribute('user_id');

            if ($idPessoa <= 0) {
                return $this->jsonError($response, 'Usuário não identificado.', 401);
            }

            $valor = $data['valor'] ?? null;
            if (!is_numeric($valor) || (float)$valor <= 0) {
                return $this->jsonError($response, 'Valor inválido.', 400);
            }

            $socioApi = $this->socioRepository->findByPessoaId($idPessoa);
            if (!$socioApi || empty($socioApi['id_socio'])) {
                return $this->jsonError($response, 'Sócio não encontrado para o usuário autenticado.', 404);
            }

            $socioDao = new \SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorId((int)$socioApi['id_socio']);

            if (!$socio) {
                return $this->jsonError($response, 'Sócio não encontrado.', 404);
            }

            $meioPagamentoDao = new \MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome('Boleto');

            if (is_null($meioPagamento)) {
                return $this->jsonError($response, 'Meio de pagamento não encontrado.', 404);
            }

            $regraPagamentoDao = new \RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento($meioPagamento->getId());
            $erroRegra = $this->validarRegrasValor((float)$valor, $conjuntoRegrasPagamento);

            if ($erroRegra !== null) {
                return $this->jsonError($response, $erroRegra, 400);
            }

            $gatewayPagamentoDao = new \GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray || count($gatewayPagamentoArray) < 1) {
                return $this->jsonError($response, 'Gateway de pagamento não encontrado.', 404);
            }

            $gatewayPagamento = new \GatewayPagamento(
                $gatewayPagamentoArray['plataforma'],
                $gatewayPagamentoArray['endPoint'],
                $gatewayPagamentoArray['token'],
                $gatewayPagamentoArray['status']
            );
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            $requisicaoServico = dirname(__DIR__, 4) . '/web/html/contribuicao/service/' . $gatewayPagamento->getNome() . 'BoletoService.php';
            if (!file_exists($requisicaoServico)) {
                return $this->jsonError($response, 'Arquivo de serviço de pagamento não encontrado.', 500);
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . 'BoletoService';
            if (!class_exists($classeService)) {
                return $this->jsonError($response, 'Classe de serviço de pagamento não encontrada.', 500);
            }

            $servicoPagamento = new $classeService();
            $dataGeracao = (new \DateTimeImmutable('now'))->format('Y-m-d');
            try {
                $dataVencimento = $this->resolverDataVencimento($data['dia'] ?? null);
            } catch (\InvalidArgumentException $e) {
                return $this->jsonError($response, $e->getMessage(), 400);
            }

            $contribuicaoLogDao = new \ContribuicaoLogDAO($this->pdo);
            $agradecimento = $contribuicaoLogDao->getAgradecimento();

            $contribuicaoLog = new \ContribuicaoLog();
            $contribuicaoLog
                ->setValor((float)$valor)
                ->setCodigo($contribuicaoLog->gerarCodigo())
                ->setDataGeracao($dataGeracao)
                ->setDataVencimento($dataVencimento)
                ->setSocio($socio)
                ->setGatewayPagamento($gatewayPagamento)
                ->setMeioPagamento($meioPagamento)
                ->setAgradecimento($agradecimento);

            $this->pdo->beginTransaction();

            $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);
            $socioDao->registrarLog($contribuicaoLog->getSocio(), 'Boleto gerado recentemente', \Util::getUserIp(), \Util::getUserAgent());

            ob_start();
            $codigoApi = $servicoPagamento->gerarBoleto($contribuicaoLog);
            $saidaServico = (string)ob_get_clean();

            $linkBoleto = $this->capturarLinkDaRespostaServico($saidaServico);

            if (!$codigoApi || !$linkBoleto) {
                $this->pdo->rollBack();
                return $this->jsonError($response, 'Não foi possível gerar o boleto.', 502);
            }

            $contribuicaoLogDao->alterarCodigoPorId($codigoApi, $contribuicaoLog->getId());
            $this->pdo->commit();

            $response->getBody()->write(json_encode([
                'link' => $linkBoleto,
                'codigo' => $codigoApi,
                'contribuicao_id' => (int)$contribuicaoLog->getId()
            ]));

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $response->getBody()->write(json_encode([
                'error' => 'Erro ao gerar boleto: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function generateCarne(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $idPessoa = (int)$request->getAttribute('user_id');

            if ($idPessoa <= 0) {
                return $this->jsonError($response, 'Usuário não identificado.', 401);
            }

            $valor = $data['valor'] ?? null;
            if (!is_numeric($valor) || (float)$valor <= 0) {
                return $this->jsonError($response, 'Valor inválido.', 400);
            }

            $parcelas = filter_var($data['parcelas'] ?? null, FILTER_VALIDATE_INT);
            if ($parcelas === false || $parcelas < 2 || $parcelas > 12) {
                return $this->jsonError($response, 'A quantidade de parcelas deve ser um número entre 2 e 12.', 400);
            }

            $diaVencimento = filter_var($data['dia'] ?? null, FILTER_VALIDATE_INT);
            if ($diaVencimento === false) {
                return $this->jsonError($response, 'Dia de vencimento inválido.', 400);
            }

            $socioApi = $this->socioRepository->findByPessoaId($idPessoa);
            if (!$socioApi || empty($socioApi['id_socio'])) {
                return $this->jsonError($response, 'Sócio não encontrado para o usuário autenticado.', 404);
            }

            $socioDao = new \SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorId((int)$socioApi['id_socio']);

            if (!$socio) {
                return $this->jsonError($response, 'Sócio não encontrado.', 404);
            }

            $meioPagamentoDao = new \MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome('Carne');

            if (is_null($meioPagamento)) {
                return $this->jsonError($response, 'Meio de pagamento não encontrado.', 404);
            }

            $regraPagamentoDao = new \RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento($meioPagamento->getId());
            $erroRegra = $this->validarRegrasValor((float)$valor, $conjuntoRegrasPagamento);

            if ($erroRegra !== null) {
                return $this->jsonError($response, $erroRegra, 400);
            }

            $gatewayPagamentoDao = new \GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray || count($gatewayPagamentoArray) < 1) {
                return $this->jsonError($response, 'Gateway de pagamento não encontrado.', 404);
            }

            $gatewayPagamento = new \GatewayPagamento(
                $gatewayPagamentoArray['plataforma'],
                $gatewayPagamentoArray['endPoint'],
                $gatewayPagamentoArray['token'],
                $gatewayPagamentoArray['status']
            );
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            $requisicaoServico = dirname(__DIR__, 4) . '/web/html/contribuicao/service/' . $gatewayPagamento->getNome() . 'CarneService.php';
            if (!file_exists($requisicaoServico)) {
                return $this->jsonError($response, 'Arquivo de serviço de pagamento não encontrado.', 500);
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . 'CarneService';
            if (!class_exists($classeService)) {
                return $this->jsonError($response, 'Classe de serviço de pagamento não encontrada.', 500);
            }

            try {
                $intervalo = $this->resolverIntervaloCarne($data['tipoGeracao'] ?? null);
                $datasVencimento = $this->gerarDatasVencimentoCarne((int)$parcelas, (int)$diaVencimento, $intervalo);
            } catch (\InvalidArgumentException $e) {
                return $this->jsonError($response, $e->getMessage(), 400);
            }

            $servicoPagamento = new $classeService();
            $contribuicaoLogDao = new \ContribuicaoLogDAO($this->pdo);
            $agradecimento = $contribuicaoLogDao->getAgradecimento();
            $contribuicaoLogCollection = new \ContribuicaoLogCollection();
            $dataGeracao = (new \DateTimeImmutable('now'))->format('Y-m-d');

            $this->pdo->beginTransaction();

            foreach ($datasVencimento as $dataVencimento) {
                $contribuicaoLog = new \ContribuicaoLog();
                $contribuicaoLog
                    ->setValor((float)$valor)
                    ->setCodigo($contribuicaoLog->gerarCodigo())
                    ->setDataGeracao($dataGeracao)
                    ->setDataVencimento($dataVencimento)
                    ->setSocio($socio)
                    ->setGatewayPagamento($gatewayPagamento)
                    ->setMeioPagamento($meioPagamento)
                    ->setAgradecimento($agradecimento);

                $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);
                $contribuicaoLogCollection->add($contribuicaoLog);
            }

            $ultimoLog = $contribuicaoLogCollection->getIterator()->current();
            if (!$ultimoLog) {
                $this->pdo->rollBack();
                return $this->jsonError($response, 'Não foi possível preparar o carnê.', 500);
            }

            $socioDao->registrarLog($ultimoLog->getSocio(), 'Carnê gerado recentemente', \Util::getUserIp(), \Util::getUserAgent());

            $resultado = $servicoPagamento->gerarCarne($contribuicaoLogCollection);
            if (!$resultado || empty($resultado) || empty($resultado['link'])) {
                $this->pdo->rollBack();
                return $this->jsonError($response, 'Não foi possível gerar o carnê.', 502);
            }

            if (!empty($resultado['contribuicoes']) && is_iterable($resultado['contribuicoes'])) {
                foreach ($resultado['contribuicoes'] as $contribuicao) {
                    $contribuicaoLogDao->alterarCodigoPorId($contribuicao->getCodigo(), $contribuicao->getId());
                }
            }

            $this->pdo->commit();

            $response->getBody()->write(json_encode([
                'link' => WWW . 'html/contribuicao/' . $resultado['link'],
                'parcelas' => (int)$parcelas
            ]));

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $response->getBody()->write(json_encode([
                'error' => 'Erro ao gerar carnê: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    public function generatePix(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody() ?? [];
            $idPessoa = (int)$request->getAttribute('user_id');

            if ($idPessoa <= 0) {
                return $this->jsonError($response, 'Usuário não identificado.', 401);
            }

            $valor = $data['valor'] ?? null;
            if (!is_numeric($valor) || (float)$valor <= 0) {
                return $this->jsonError($response, 'Valor inválido.', 400);
            }

            $socioApi = $this->socioRepository->findByPessoaId($idPessoa);
            if (!$socioApi || empty($socioApi['id_socio'])) {
                return $this->jsonError($response, 'Sócio não encontrado para o usuário autenticado.', 404);
            }

            $socioDao = new \SocioDAO($this->pdo);
            $socio = $socioDao->buscarPorId((int)$socioApi['id_socio']);

            if (!$socio) {
                return $this->jsonError($response, 'Sócio não encontrado.', 404);
            }

            $meioPagamentoDao = new \MeioPagamentoDAO();
            $meioPagamento = $meioPagamentoDao->buscarPorNome('Pix');

            if (is_null($meioPagamento)) {
                return $this->jsonError($response, 'Meio de pagamento não encontrado.', 404);
            }

            $regraPagamentoDao = new \RegraPagamentoDAO();
            $conjuntoRegrasPagamento = $regraPagamentoDao->buscaConjuntoRegrasPagamentoPorIdMeioPagamento($meioPagamento->getId());
            $erroRegra = $this->validarRegrasValor((float)$valor, $conjuntoRegrasPagamento);

            if ($erroRegra !== null) {
                return $this->jsonError($response, $erroRegra, 400);
            }

            $gatewayPagamentoDao = new \GatewayPagamentoDAO();
            $gatewayPagamentoArray = $gatewayPagamentoDao->buscarPorId($meioPagamento->getGatewayId());

            if (!$gatewayPagamentoArray || count($gatewayPagamentoArray) < 1) {
                return $this->jsonError($response, 'Gateway de pagamento não encontrado.', 404);
            }

            $gatewayPagamento = new \GatewayPagamento(
                $gatewayPagamentoArray['plataforma'],
                $gatewayPagamentoArray['endPoint'],
                $gatewayPagamentoArray['token'],
                $gatewayPagamentoArray['status']
            );
            $gatewayPagamento->setId($meioPagamento->getGatewayId());

            $requisicaoServico = dirname(__DIR__, 4) . '/web/html/contribuicao/service/' . $gatewayPagamento->getNome() . 'PixService.php';
            if (!file_exists($requisicaoServico)) {
                return $this->jsonError($response, 'Arquivo de serviço de pagamento não encontrado.', 500);
            }

            require_once $requisicaoServico;

            $classeService = $gatewayPagamento->getNome() . 'PixService';
            if (!class_exists($classeService)) {
                return $this->jsonError($response, 'Classe de serviço de pagamento não encontrada.', 500);
            }

            $servicoPagamento = new $classeService();
            $dataGeracao = (new \DateTimeImmutable('now'))->format('Y-m-d');
            $dataVencimento = (new \DateTimeImmutable('now'))->modify('+1 day')->format('Y-m-d');

            $contribuicaoLogDao = new \ContribuicaoLogDAO($this->pdo);
            $agradecimento = $contribuicaoLogDao->getAgradecimento();

            $contribuicaoLog = new \ContribuicaoLog();
            $contribuicaoLog
                ->setValor((float)$valor)
                ->setCodigo($contribuicaoLog->gerarCodigo())
                ->setDataGeracao($dataGeracao)
                ->setDataVencimento($dataVencimento)
                ->setSocio($socio)
                ->setGatewayPagamento($gatewayPagamento)
                ->setMeioPagamento($meioPagamento)
                ->setAgradecimento($agradecimento);

            $this->pdo->beginTransaction();

            $contribuicaoLog = $contribuicaoLogDao->criar($contribuicaoLog);
            $socioDao->registrarLog($contribuicaoLog->getSocio(), 'Pix gerado recentemente', \Util::getUserIp(), \Util::getUserAgent());

            ob_start();
            $codigoApi = $servicoPagamento->gerarQrCode($contribuicaoLog);
            $saidaServico = (string)ob_get_clean();

            $respostaPix = $this->capturarRespostaPixServico($saidaServico);

            if (!$codigoApi || !$respostaPix) {
                $this->pdo->rollBack();
                return $this->jsonError($response, 'Não foi possível gerar o Pix.', 502);
            }

            $contribuicaoLogDao->alterarCodigoPorId($codigoApi, $contribuicaoLog->getId());
            $this->pdo->commit();

            $response->getBody()->write(json_encode([
                'qrcode' => $respostaPix['qrcode'],
                'copiaCola' => $respostaPix['copiaCola'],
                'codigo' => $codigoApi,
                'contribuicao_id' => (int)$contribuicaoLog->getId()
            ]));

            return $response->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $response->getBody()->write(json_encode([
                'error' => 'Erro ao gerar Pix: ' . $e->getMessage()
            ]));

            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
