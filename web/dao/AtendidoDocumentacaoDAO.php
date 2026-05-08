<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AtendidoDocumentacao.php';
interface AtendidoDocumentacaoDAO{
    /**
     * Salva a persistência de um objeto AtendidoDocumentacao no banco de dados do sistema.
     * Em caso de sucesso retorna o id do novo item criado.
     */
    public function create(AtendidoDocumentacao $atendidoDocumentacao):int|false;
    public function getAll():array|null;
}