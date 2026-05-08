<?php

class Saida
{
   private $id_saida;
   private $id_destino;
   private $id_almoxarifado;
   private $id_tipo;
   private $id_responsavel;
   private $data;
   private $hora;
   private $valor_total;
   
    public function __construct($id_responsavel,$data,$hora,$valor_total)
    {

        $this->id_responsavel=$id_responsavel;
        $this->data=$data;
        $this->hora=$hora;
        $this->valor_total=$valor_total;

    }

    public function getId_saida()
    {
        return $this->id_saida;
    }

    public function getId_destino()
    {
        return $this->id_destino;
    }

    public function getId_almoxarifado()
    {
        return $this->id_almoxarifado;
    }

    public function getId_tipo()
    {
        return $this->id_tipo;
    }

    public function getId_responsavel()
    {
        return $this->id_responsavel;
    }

    public function getdata()
    {
        return $this->data;
    }

    public function gethora()
    {
        return $this->hora;
    }

    public function getvalor_total()
    {
        return $this->valor_total;
    }

    public function setId_saida($id_saida)
    {
        $this->id_saida = $id_saida;
    }

    public function setId_destino($id_destino)
    {
        $this->id_destino = $id_destino;
    }

    public function setId_almoxarifado($id_almoxarifado)
    {
        $this->id_almoxarifado = $id_almoxarifado;
    }

    public function setId_tipo($id_tipo)
    {
        $this->id_tipo = $id_tipo;
    }

    public function setId_responsavel($id_responsavel)
    {
        $this->id_responsavel = $id_responsavel;
    }

    public function setdata($data)
    {
        $this->data = $data;
    }

    public function sethora($hora)
    {
        $this->hora = $hora;
    }

    public function setvalor_total($valor_total)
    {
        $this->valor_total = $valor_total;
    }
}