<?php

class Estoque
{
    //Atributos
   private $id_produto;
   private $id_almoxarifado;
   private $qtd;
   
    public function __construct(int $qtd)
    {
        $this->setQtd($qtd);
    }

    /**
     * Retorna o id de um produto do estoque
     */
    public function getId_produto()
    {
        return $this->id_produto;
    }

    /**
     * Retorna o id de um produto do almoxarifado
     */
    public function getId_almoxarifado()
    {
        return $this->id_almoxarifado;
    }

    /**
     * Retorna a quantidade de um estoque
     */
    public function getQtd()
    {
        return $this->qtd;
    }

    /**
     * Define o id de um produto do estoque
     */
    public function setId_produto(int $id_produto)
    {
        if($id_produto < 1){
            throw new InvalidArgumentException('O valor do id de um produto não pode ser menor que 1.');
        }
        $this->id_produto = $id_produto;
    }

    /**
     * Define o id de um almoxarifado do estoque
     */
    public function setId_almoxarifado(int $id_almoxarifado)
    {
        if($id_almoxarifado < 1){
            throw new InvalidArgumentException('O valor do id de um almoxarifado não pode ser menor que 1.');
        }
        $this->id_almoxarifado = $id_almoxarifado;
    }

    /**
     * Define a quantidade de um estoque
     */
    public function setQtd(int $qtd)
    {
        if($qtd < 0){
            throw new InvalidArgumentException('A quantidade de um estoque não pode ser negativa.');
        }
        $this->qtd = $qtd;
    }   
}