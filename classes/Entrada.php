<?php

class Entrada
{
   private $id_entrada;
   private $id_origem;
   private $id_almoxarifado;
   private $id_tipo;
   private $id_responsavel;
   private $data;
   private $hora;
   private $valor_total;
   
    public function __construct($data,$hora,$valor_total,$id_responsavel)
    {

        $this->data=$data;
        $this->hora=$hora;
        
        // Valor total
        if (!is_numeric($valor_total) || $valor_total < 0) {
            throw new InvalidArgumentException("Valor total deve ser um número positivo.");
        }
        $this->valor_total = (float) $valor_total;

        // ID do responsável
        if (!is_numeric($id_responsavel) || $id_responsavel <= 0) {
            throw new InvalidArgumentException("ID do responsável inválido.");
        }
        $this->id_responsavel = (int) $id_responsavel;

    }

    public function getId_entrada()
    {
        return $this->id_entrada;
    }

    public function get_origem()
    {
        return $this->id_origem;
    }

    public function get_almoxarifado()
    {
        return $this->id_almoxarifado;
    }

    public function get_tipo()
    {
        return $this->id_tipo;
    }

    public function get_responsavel()
    {
        return $this->id_responsavel;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getHora()
    {
        return $this->hora;
    }

    public function getValor_total()
    {
        return $this->valor_total;
    }

    public function setId_entrada($id_entrada)
    {
        if (!is_numeric($id_entrada) || $id_entrada <= 0)
            throw new InvalidArgumentException("ID da entrada inválido.");
        $this->id_entrada = (int) $id_entrada;
    }

    public function set_origem($id_origem)
    {
        if (!is_numeric($id_origem) || $id_origem <= 0)
            throw new InvalidArgumentException("ID de origem inválido.");
        $this->id_origem = (int) $id_origem;
    }

    public function set_almoxarifado($id_almoxarifado)
    {
        if (!is_numeric($id_almoxarifado) || $id_almoxarifado <= 0)
            throw new InvalidArgumentException("ID do almoxarifado inválido.");
        $this->id_almoxarifado = (int) $id_almoxarifado;
    }

    public function set_tipo($id_tipo)
    {
        if (!is_numeric($id_tipo) || $id_tipo <= 0)
            throw new InvalidArgumentException("ID do tipo inválido.");
        $this->id_tipo = (int) $id_tipo;
    }

    public function set_responsavel($id_responsavel)
    {
        if (!is_numeric($id_responsavel) || $id_responsavel <= 0)
            throw new InvalidArgumentException("ID do responsável inválido.");
        $this->id_responsavel = (int) $id_responsavel;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setHora($hora)
    {
        $this->hora = $hora;
    }

    public function setValor_total($valor_total)
    {
        if (!is_numeric($valor_total) || $valor_total < 0) {
            throw new InvalidArgumentException("Valor total deve ser um número positivo.");
        }
        $this->valor_total = (float) $valor_total;
    }

    /*
    public function setId_entrada($id_entrada)
    {
        $this->id_entrada = $id_entrada;
    }

    public function set_origem($id_origem)
    {
        $this->id_origem = $id_origem;
    }

    public function set_almoxarifado($id_almoxarifado)
    {
        $this->id_almoxarifado = $id_almoxarifado;
    }

    public function set_tipo($id_tipo)
    {
        $this->id_tipo = $id_tipo;
    }

    public function set_responsavel($id_responsavel)
    {
        $this->id_responsavel = $id_responsavel;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setHora($hora)
    {
        $this->hora = $hora;
    }

    public function setValor_total($valor_total)
    {
        $this->valor_total = $valor_total;
    }
    */
}