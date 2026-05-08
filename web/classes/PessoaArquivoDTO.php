<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Arquivo.php';

class PessoaArquivoDTO{
    public ?int $id = null;
    public ?int $idPessoa = null;
    public ?Arquivo $arquivo = null;

    public function __construct(array $dados)
    {
        if(isset($dados['id']))
            $this->id = $dados['id'];

        if(isset($dados['id_pessoa']))
            $this->idPessoa = $dados['id_pessoa'];

        if(isset($dados['arquivo']) && $dados['arquivo'] instanceof Arquivo)
            $this->arquivo = $dados['arquivo'];
    }
}