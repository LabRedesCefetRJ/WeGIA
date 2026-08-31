<?php

namespace api\modules\Socio;

use api\modules\Auth\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SocioMiddleware
{
    private UserRepository $repository;
    private int $resourceId;

    public function __construct(UserRepository $repository, int $resourceId = 4)
    {
        $this->repository = $repository;
        $this->resourceId = $resourceId;
    }

    /**
     * Middleware que verifica se o usuário autenticado possui um funcionário associado
     * com permissão de acesso ao recurso especificado
     *
     * @param Request $request
     * @param $handler
     * @return Response
     */
    public function __invoke(Request $request, $handler): Response
    {
        // Obtém o ID do usuário do token JWT (atributo definido pelo AuthMiddleware)
        $userId = $request->getAttribute('user_id');

        if (!$userId) {
            return $this->forbidden('User ID não encontrado no token');
        }

        // Verifica se o usuário tem acesso ao recurso
        if (!$this->repository->hasAccessToResource($userId, $this->resourceId)) {
            return $this->forbidden('Usuário não possui permissão para acessar este recurso');
        }

        // Adiciona informações de permissão ao request para uso posterior
        $accessLevel = $this->repository->getAccessLevel($userId, $this->resourceId);
        $request = $request->withAttribute('resource_id', $this->resourceId);
        $request = $request->withAttribute('access_level', $accessLevel);

        return $handler->handle($request);
    }

    /**
     * Retorna uma resposta de erro 403 (Forbidden)
     *
     * @param string $msg Mensagem de erro
     * @return Response
     */
    private function forbidden(string $msg = 'Acesso negado'): Response
    {
        $response = new \Slim\Psr7\Response();

        $response->getBody()->write(json_encode([
            'error' => $msg,
            'status' => 'forbidden'
        ]));

        return $response->withStatus(403)
            ->withHeader('Content-Type', 'application/json');
    }
}
