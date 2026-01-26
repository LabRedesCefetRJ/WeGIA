<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AtendidoDocumentacao.php';
interface AtendidoDocumentacaoDAO{
    public function create(AtendidoDocumentacao $atendidoDocumentacao):int|false;
    public function getAll():array|null;
}