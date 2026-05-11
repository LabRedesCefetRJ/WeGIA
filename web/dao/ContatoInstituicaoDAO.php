<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ContatoInstituicao.php';

interface ContatoInstituicaoDAO{
    //assinaturas de métodos
    public function __construct(PDO $pdo);
    public function incluir(ContatoInstituicao $contato):int|false;
    public function listarPorId(int $id):ContatoInstituicao|null;
    public function listarTodos():array|null;
    public function alterar(ContatoInstituicao $contato):bool;
    public function excluirPorId(int $id): bool;
}