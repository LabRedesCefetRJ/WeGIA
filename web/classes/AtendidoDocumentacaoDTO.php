<?php
class AtendidoDocumentacaoDTO{
    public $id;
    public int $idAtendido;
    public int $idTipoDocumentacao;
    public int $idPessoaArquivo;

    public function __construct(array $dados)
    {
        if(isset($dados['id']))
            $this->id = $dados['id'];

        if(isset($dados['id_atendido']))
            $this->idAtendido = $dados['id_atendido'];

        if(isset($dados['id_tipo_documentacao']))
            $this->idTipoDocumentacao = $dados['id_tipo_documentacao'];

        if(isset($dados['id_pessoa_arquivo']))
            $this->idPessoaArquivo = $dados['id_pessoa_arquivo'];
    }
}