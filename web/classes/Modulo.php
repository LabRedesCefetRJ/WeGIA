<?php

class Modulo
{
    //Atributos Privados
    private $id_modulo;
    private $id_recurso;
    private $visibilidade;

    /**
     * Método construtor da classe Módulo
     */
    public function __construct(int $id_modulo, int $id_recurso, int $visibilidade)
    {
        $this->id_modulo = $this->setId_modulo($id_modulo);
        $this->id_recurso = $this->setId_recurso($id_recurso);
        $this->visibilidade = $this->setVisibilidade($visibilidade);
    }
    
    /**
     * Retorna o id do módulo
     */
    public function getId_modulo()
    {
        return $this->id_modulo;
    }

    /**
     * Retorna o id do recurso do módulo
     */
    public function getId_recurso()
    {
        return $this->id_recurso;
    }

    /**
     * Retorna a visibilidade do módulo
     */
    public function getVisibilidade()
    {
        return $this->visibilidade;
    }

    /**
     * Atribuí o inteiro positivo passado para o id do móduclo
     */
    public function setId_modulo(int $id_modulo)
    {
        if($id_modulo < 1){
            throw new InvalidArgumentException('O valor do id de um módulo deve ser um inteiro maior ou igual a 1.');
        }
        $this->id_modulo = $id_modulo;
    }

    /**
     * Atribuí o inteiro positivo passado para o id do recurso do módulo
     */
    public function setId_recurso(int $id_recurso)
    {
        if($id_recurso < 1){
            throw new InvalidArgumentException('O valor do id de um recurso deve ser um inteiro maior ou igual a 1.');
        }
        $this->id_recurso = $id_recurso;
    }

    /**
     * Atribuí o valor boleano passado para a visibilidade do módulo
     */
    public function setVisibilidade(bool $visibilidade)
    {
        $this->visibilidade = $visibilidade;
    }
}
