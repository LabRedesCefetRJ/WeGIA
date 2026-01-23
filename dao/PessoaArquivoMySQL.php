<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PessoaArquivoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivoDTO.php';

class PessoaArquivoMySQL implements PessoaArquivoDAO{
    public function create(PessoaArquivo $pessoaArquivo): int|false
    {
        throw new \Exception('Not implemented');
    }

    public function delete(int $id): bool
    {
        throw new \Exception('Not implemented');
    }

    public function getById(int $id): PessoaArquivoDTO
    {
        throw new \Exception('Not implemented');
    }

    public function getAll(): array
    {
        throw new \Exception('Not implemented');
    }
}