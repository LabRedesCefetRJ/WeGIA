<?php

namespace api\modules\Contribuicao;

require_once dirname(__DIR__, 4) . '/web/html/contribuicao/service/PdfService.php';

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ContribuicaoController
{
    private ContribuicaoService $contribuicaoService;
    private \api\modules\Socio\SocioRepository $socioRepository;
    private \api\modules\Pessoa\PessoaRepository $pessoaRepository;

    public function __construct(
        ContribuicaoService $contribuicaoService,
        \api\modules\Socio\SocioRepository $socioRepository,
        \api\modules\Pessoa\PessoaRepository $pessoaRepository
    )
    {
        $this->contribuicaoService = $contribuicaoService;
        $this->socioRepository = $socioRepository;
        $this->pessoaRepository = $pessoaRepository;
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
}
