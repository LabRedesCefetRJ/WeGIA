<?php

class Almoxarife {

    // Atributes
    
    private $id_almoxarife;
    private $id_almoxarifado;
    private $id_funcionario;
    private $descricao_funcionario;
    private $descricao_almoxarifado;

    // Constructor

    public function __construct(int $id_almoxarife, int $id_almoxarifado, int $id_funcionario, string $descricao_funcionario, string $descricao_almoxarifado){
        $this
            ->setId_almoxarife($id_almoxarife)
            ->setId_almoxarifado($id_almoxarifado)
            ->setId_funcionario($id_funcionario)
            ->setDescricao_funcionario($descricao_funcionario)
            ->setDescricao_almoxarifado($descricao_almoxarifado)
        ;
    }

    // Getters & Setters

    public function getId_almoxarife()
    {
        return $this->id_almoxarife;
    }

    public function setId_almoxarife(int $id_almoxarife)
    {
        if($id_almoxarife < 1){
            throw new InvalidArgumentException('Erro, o id de um almoxarife não pode ser menor que 1.');
        }

        $this->id_almoxarife = $id_almoxarife;

        return $this;
    }

    public function getId_almoxarifado()
    {
        return $this->id_almoxarifado;
    }

    public function setId_almoxarifado(int $id_almoxarifado)
    {
        if($id_almoxarifado < 1){
            throw new InvalidArgumentException('Erro, o id de um almoxarifado não pode ser menor que 1.');
        }

        $this->id_almoxarifado = $id_almoxarifado;

        return $this;
    }

    public function getId_funcionario()
    {
        return $this->id_funcionario;
    }

    public function setId_funcionario(int $id_funcionario)
    {
        if($id_funcionario < 1){
            throw new InvalidArgumentException('Erro, o id de um funcionário não pode ser menor que 1.');
        }

        $this->id_funcionario = $id_funcionario;

        return $this;
    }

    public function getDescricao_funcionario()
    {
        return $this->descricao_funcionario;
    }

    public function setDescricao_funcionario(string $descricao_funcionario)
    {
        if(empty($descricao_funcionario)){
            throw new InvalidArgumentException('Erro, a descrição de um funcionário não pode ser vazia.');
        }

        $this->descricao_funcionario = $descricao_funcionario;

        return $this;
    }

    public function getDescricao_almoxarifado()
    {
        return $this->descricao_almoxarifado;
    }

    public function setDescricao_almoxarifado(string $descricao_almoxarifado)
    {
        if(empty($descricao_almoxarifado)){
            throw new InvalidArgumentException('Erro, a descrição de um almoxarifado não pode ser vaiza.');
        }
        
        $this->descricao_almoxarifado = $descricao_almoxarifado;

        return $this;
    }
}