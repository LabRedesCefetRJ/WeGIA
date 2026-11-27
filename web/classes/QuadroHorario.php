<?php

class QuadroHorario
{
	
	private int $id_quadro_horario;
	private int $escala;
	private int $tipo;
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

    public function setEscala(int $escala)
    {
        if($escala <= 0) {
            throw new InvalidArgumentException("Escala inválida, valor negativo ou nulo.", 400);
        }
        if(strlen(("$escala")) > 11) {
            throw new InvalidArgumentException("A escala deve ter, no máximo, 11 dígitos.", 400);
        }

        $this->escala = $escala;
        return $this;

    }

    public function setTipo(int $tipo)
    {
        if($tipo <= 0) {
            throw new InvalidArgumentException("Tipo inválido, valor negativo ou nulo.", 400);
        }
        if(strlen(("$tipo")) > 11) {
            throw new InvalidArgumentException("O tipo deve ter, no máximo, 11 dígitos.", 400);
        }

        $this->tipo = $tipo;
        return $this;
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