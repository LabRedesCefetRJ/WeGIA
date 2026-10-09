<?php

class Setor
{
    public const STATUS_ATIVO = 1;
    public const STATUS_INATIVO = 0;

    private $id_setor;
    private $descricao;
    private $status;

    public function __construct($id_setor = null, $descricao = null, $status = self::STATUS_ATIVO)
    {
        $this->id_setor = $id_setor;
        $this->descricao = $descricao;
        $this->status = $status;
    }

    public function getId_Setor()
    {
        return $this->id_setor;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getStatus()
    {
        return $this->status;
    }

    public function setId_Setor($id_setor)
    {
        $this->id_setor = $id_setor;
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