<?php

namespace api\modules\Contribuicao;

use Slim\Psr7\Request;
use Slim\Psr7\Response;

class ContribuicaoController
{
    private ContribuicaoService $contribuicaoService;
    private \api\modules\Socio\SocioRepository $socioRepository;

    public function __construct(ContribuicaoService $contribuicaoService, \api\modules\Socio\SocioRepository $socioRepository)
    {
        $this->contribuicaoService = $contribuicaoService;
        $this->socioRepository = $socioRepository;
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
}
