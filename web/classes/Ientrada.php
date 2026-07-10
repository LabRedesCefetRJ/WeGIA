<?php

class Ientrada
{
   private $id_ientrada;
   private $id_entrada;
   private $id_produto;
   private $qtd;
   private $valor_unitario;
   
    public function __construct($qtd,$valor_unitario)
    {
        if (!is_numeric($qtd) || $qtd <= 0) {
            throw new InvalidArgumentException("A quantidade deve ser um número positivo.");
        }

        if (!is_numeric($valor_unitario) || $valor_unitario < 0) {
            throw new InvalidArgumentException("O valor unitário deve ser um número não negativo.");
        }

        $this->qtd=$qtd;
        $this->valor_unitario=$valor_unitario;

    }

    public function getId_ientrada()
    {
        return $this->id_ientrada;
    }

    public function getId_entrada()
    {
        return $this->id_entrada;
    }

    public function getId_produto()
    {
        return $this->id_produto;
    }

    public function getQtd()
    {
        return $this->qtd;
    }

    public function getValor_unitario()
    {
        return $this->valor_unitario;
    }

    public function setId_entrada($id_entrada)
    {
        $this->id_entrada = $id_entrada;
    }

    public function setId_produto($id_produto)
    {
        $this->id_produto = $id_produto;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setQtd($qtd)
    {
        $this->qtd = $qtd;
    }

    public function setValor_unitario($valor_unitario)
    {
        $this->valor_unitario = $valor_unitario;
    }
}