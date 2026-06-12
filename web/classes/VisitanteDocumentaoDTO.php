<?php

class VisitanteDocumentacaoDTO
{
    private int $idDocumentacaoVisitante;
    private int $idVisitante;
    private int $idTipoDocumentacao;
    private int $idPessoaArquivo; 

    public function __construct(array $dados)
    {
        if(isset($dados['idDocumentacaoVisitante']))
            $this->idDocumentacaoVisitante = $dados['idDocumentacaoVisitante'];

        if(isset($dados['idVisitante']))
            $this->idVisitante = $dados['idVisitante'];

        if(isset($dados['idTipoDocumentacao']))
            $this->data = $dados['idTIpoDocumentacao'];

        if(isset($dados['idPessoaArquivo']))
            $this->nomeArquivo = $dados['idPessoaArquivo'];
    }
}