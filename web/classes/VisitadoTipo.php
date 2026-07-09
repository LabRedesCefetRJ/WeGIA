<?php

class VisitadoTipo
{
    private $id_visitado_tipo;
    private $descricao;

    public function getId_Visitado_Tipo()
    {
        return $this->id_visitado_tipo;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setId_Visitado_Tipo($id_visitado_tipo)
    {
        $this->id_visitado_tipo = $id_visitado_tipo;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
}