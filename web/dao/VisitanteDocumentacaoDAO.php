<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'VisitanteDocumentacao.php';
interface VisitanteDocumentacaoDAO
{
    /**
     * Salva a persistência de um objeto VisitanteDocumentacao no banco de dados do sistema.
     * Em caso de sucesso retorna o id do novo item criado.
     */
    public function create(VisitanteDocumentacao $visitanteDocumentacao) : int|false;
    public function getAll() : array | null;
}