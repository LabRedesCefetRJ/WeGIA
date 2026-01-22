<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Arquivo.php';
class PessoaArquivo{
    //properities
    private int $id;
    private int $idPessoa;
    private Arquivo $arquivo;
    private PessoaArquivoDAO $dao;

    public function __construct(PessoaArquivoDTO $dto, ?PessoaArquivoDAO $dao = null)
    {
        $this->setId($dto->id)->setIdPessoa($dto->idPessoa)->setArquivo($dto->arquivo);

        isset($dao) ? $this->dao = $dao : $this->dao = new PessoaArquivoMySQL(Conexao::connect());
    }

    public function getId(){
        return $this->id;
    }

    public function setId(int $id){
        if($id < 1)
            throw new InvalidArgumentException('O id não pode ser menor que 1.', 412);

        if(strlen($id) > 11)
            throw new InvalidArgumentException('O tamanho do id excede o limite máximo permitido.', 412);

        $this->id = $id;
        return $this;
    }

    public function setIdPessoa(int $idPessoa){
        if($idPessoa < 1)
            throw new InvalidArgumentException('O id da pessoa não pode ser menor que 1.', 412);

        if(strlen($idPessoa) > 11)
            throw new InvalidArgumentException('O tamanho do id da pessoa excede o limite máximo permitido.', 412);

        $this->idPessoa = $idPessoa;
        return $this;
    }

    public function getIdPessoa(){
        return $this->idPessoa;
    }

    public function setArquivo(Arquivo $arquivo){
        $this->arquivo = $arquivo;
        return $this;
    }

    public function getArquivo(){
        return $this->arquivo;
    }
}