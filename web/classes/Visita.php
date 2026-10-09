<?php

class Visita {
    private $id_visita;
    private $id_visitante;
    private $id_visitado;
    private $entrada;
    private $saida;
    private $descricao;

    public function __construct($id_visitante, $id_visitado) {
        $this->id_visitante = $id_visitante;
        $this->id_visitado = $id_visitado;
    }

    public function getId_Visita()
    {
        return $this->id_visita;
    }
    public function getId_Visitante()
    {
        return $this->id_visitante;
    }
    public function getId_Visitado()
    {
        return $this->id_visitado;
    }
    public function getEntrada()
    {
        return $this->entrada;
    }
    public function getSaida()
    {
        return $this->saida;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setId_Visita($id_visita)
    {
        $this->id_visita = $id_visita;
    }
    public function setId_Visitante($id_visitante)
    {
        $this->id_visitante = $id_visitante;
    }
    public function setId_Visitado($id_visitado)
    {
        $this->id_visitado = $id_visitado;
    }
    public function setEntrada($entrada)
    {
        $this->entrada = $entrada;
    }
    public function setSaida($saida)
    {
        $this->saida = $saida;
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
}