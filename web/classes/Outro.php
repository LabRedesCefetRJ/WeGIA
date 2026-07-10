<?php

class Outro
{
    private $id_outro;
    private $foto;
    private $descricao;
    private $status;

    public function getId_Outro()
    {
        return $this->id_outro;
    }
    public function getFoto()
    {
        return $this->foto;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getStatus()
    {
        return $this->status;
    }

    public function setId_Outro($id_outro)
    {
        $this->id_outro = $id_outro;
    }
    public function setFoto($foto)
    {
        $this->foto = $foto;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
}