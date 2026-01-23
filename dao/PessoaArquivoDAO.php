<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivoDTO.php';

interface PessoaArquivoDAO{
    public function __construct(?PDO $pdo = null);
    public function create(PessoaArquivo $pessoaArquivo):int|false;
    public function delete(int $id):bool;
    public function getById(int $id):PessoaArquivoDTO;
    public function getAll():array;
}