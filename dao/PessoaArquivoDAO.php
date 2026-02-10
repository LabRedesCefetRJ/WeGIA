<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivoDTO.php';

interface PessoaArquivoDAO{
    public function __construct(?PDO $pdo = null);

    /**
     * Salva a persistência de um objeto PessoaArquivo no banco de dados do sistema.
     * Em caso de sucesso retorna o id do novo item criado.
     */
    public function create(PessoaArquivo $pessoaArquivo):int|false;

    /**
     * Remove a persistência de um objeto PessoaArquivo do banco de dados do sistema através do id fornecido.
     * Retorna true em caso de sucesso e false em caso de falha.
     */
    public function delete(int $id):bool;

    /**
     * Procura a persistência no banco de dados do sistema através do seu id.
     * Retorna um objeto do tipo PessoaArquivoDTO em caso de sucesso na busca
     */
    public function getById(int $id):PessoaArquivoDTO|null;
    public function getAll():array;
}