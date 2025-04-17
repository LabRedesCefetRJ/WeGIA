<?php

class SistemaLog{
    private int $idPessoa;
    private int $idRecurso;
    private int $idAcao;
    private DateTime $data;
    private string $descricao;

    public function __construct(int $idPessoa, int $idRecurso, int $idAcao, DateTime $data, string $descricao)
    {
        $this->setIdPessoa($idPessoa)->setIdRecurso($idRecurso)->setIdAcao($idAcao)->setData($data)->setDescricao($descricao);
    }

    /**
     * Get the value of idPessoa
     */ 
    public function getIdPessoa()
    {
        return $this->idPessoa;
    }

    /**
     * Set the value of idPessoa
     *
     * @return  self
     */ 
    public function setIdPessoa(int $idPessoa)
    {
        if($idPessoa < 1){
            throw new InvalidArgumentException('O id de uma pessoa não pode ser menor que 1', 400);
        }

        $this->idPessoa = $idPessoa;

        return $this;
    }

    /**
     * Get the value of idRecurso
     */ 
    public function getIdRecurso()
    {
        return $this->idRecurso;
    }

    /**
     * Set the value of idRecurso
     *
     * @return  self
     */ 
    public function setIdRecurso(int $idRecurso)
    {
        if($idRecurso < 1){
            throw new InvalidArgumentException('O id de um recurso não pode ser menor que 1', 400);
        }

        $this->idRecurso = $idRecurso;

        return $this;
    }

    /**
     * Get the value of idAcao
     */ 
    public function getIdAcao()
    {
        return $this->idAcao;
    }

    /**
     * Set the value of idAcao
     *
     * @return  self
     */ 
    public function setIdAcao(int $idAcao)
    {
        if($idAcao < 1){
            throw new InvalidArgumentException('O id de uma ação não pode ser menor que 1', 400);
        }

        $this->idAcao = $idAcao;

        return $this;
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public function setData(DateTime $data)
    {
        //Implementar validação de data posteriormente

        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of descricao
     */ 
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Set the value of descricao
     *
     * @return  self
     */ 
    public function setDescricao(string $descricao)
    {
        if(strlen($descricao) < 1){
            throw new InvalidArgumentException('A descrição não pode ser vazia', 400);
        }

        $this->descricao = $descricao;

        return $this;
    }
}