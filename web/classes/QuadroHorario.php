<?php

class QuadroHorario
{
	
	private $id_quadro_horario;
	private $escala;
	private $tipo;
	private $carga_horaria;
	private $entrada1;
	private $saida1;
	private $entrada2;
	private $saida2;
	private $total;
	private $dias_trabalhados;
	private $folga;

	public function getId_quadro_horario()
    {
        return $this->id_quadro_horario;
    }

    public function getEscala()
    {
        return $this->escala;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getCarga_horaria()
    {
        return $this->carga_horaria;
    }

    public function getEntrada1()
    {
        return $this->entrada1;
    }

    public function getSaida1()
    {
        return $this->saida1;
    }

    public function getEntrada2()
    {
        return $this->entrada2;
    }

    public function getSaida2()
    {
        return $this->saida2;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getDias_trabalhados()
    {
        return $this->dias_trabalhados;
    }

    public function getFolga()
    {
        return $this->folga;
    }

    public function setId_quadro_horario($id_quadro_horario)
    {
        $this->id_quadro_horario = $id_quadro_horario;
    }

    public function setEscala($escala)
    {
        $this->escala = $escala;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    public function setCarga_horaria($carga_horaria)
    {
        $this->carga_horaria = $carga_horaria;
    }

    public function setEntrada1($entrada1)
    {
        $this->entrada1 = $entrada1;
    }

    public function setSaida1($saida1)
    {
        $this->saida1 = $saida1;
    }

    public function setEntrada2($entrada2)
    {
        $this->entrada2 = $entrada2;
    }

    public function setSaida2($saida2)
    {
        $this->saida2 = $saida2;
    }

    public function setTotal($total)
    {
        $this->total = $total;
    }

    public function setDias_trabalhados($dias_trabalhados)
    {
        $this->dias_trabalhados = $dias_trabalhados;
    }

    public function setFolga($folga)
    {
        $this->folga = $folga;
    }
}