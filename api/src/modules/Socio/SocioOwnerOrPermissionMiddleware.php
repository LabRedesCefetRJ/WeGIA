<?php

namespace api\modules\Socio;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class SocioOwnerOrPermissionMiddleware
{
    private SocioRepository $socioRepository;
    private SocioMiddleware $socioMiddleware;

    public function __construct(SocioRepository $socioRepository, SocioMiddleware $socioMiddleware)
    {
        $this->socioRepository = $socioRepository;
        $this->socioMiddleware = $socioMiddleware;
    }

    public function __invoke(Request $request, $handler): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $route = RouteContext::fromRequest($request)->getRoute();
        $routeId = $route ? $route->getArgument('id') : null;
        $idSocio = filter_var($routeId, FILTER_VALIDATE_INT);
        $idPessoaDona = $idSocio ? $this->socioRepository->getIdPessoaByIdSocio((int) $idSocio) : null;

        if ($userId > 0 && $idPessoaDona === $userId) {
            return $handler->handle($request);
        }

        return $this->socioMiddleware->__invoke($request, $handler);
    }
}
