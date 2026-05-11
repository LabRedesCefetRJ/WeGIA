<?php

class TipoSaida
{
   private $id_tipo;
   private string $descricao;
   
    public function __construct($descricao)
    {

        $this->descricao=$descricao;

    }

    public function getId_tipo()
    {
        return $this->id_tipo;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setId_tipo($id_tipo)
    {
        if (!is_numeric($id_tipo) || $id_tipo <= 0)
            throw new InvalidArgumentException("ID inválido.");
        $this->id_tipo = $id_tipo;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
}