<?php

class Funcao extends Cargo
{
    //Atributos
    private $idFuncaoVoluntario;
    private $descricaoFuncao;

    public function __construct(string $descricaoFuncao, int $idFuncaoVoluntario = null)
    {
        $this->setDescricaoFuncao($descricaoFuncao);
        if($idFuncaoVoluntario){
            $this->setIdFuncaoVoluntario($idFuncaoVoluntario);
        }
    }

    /**
     * Retorna o valor do id da função de um voluntario
     */ 
    public function getIdFuncaoVoluntario()
    {
        return $this->idFuncaoVoluntario;
    }

    /**
     * Define o valor do id da função de um voluntário
     */ 
    public function setIdFuncaoVoluntario(int $idFuncaoVoluntario)
    {
        if($idFuncaoVoluntario < 1){
            throw new InvalidArgumentException('O valor do id da função de um voluntário não pode ser menor que 1.');
        }
        $this->idFuncaoVoluntario = $idFuncaoVoluntario;
    }

    /**
     * Retorna o valor da descrição de uma função
     */ 
    public function getDescricaoFuncao()
    {
        return $this->descricaoFuncao;
    }

    /**
     * Define o valor da descrição de uma função
     */ 
    public function setDescricaoFuncao(string $descricaoFuncao)
    {
        if(empty($descricaoFuncao)){
            throw new InvalidArgumentException('A descrição de uma função não pode ser vazia');
        }
        $this->descricaoFuncao = $descricaoFuncao;
    }
}