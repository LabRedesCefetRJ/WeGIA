<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'Util.php';

class Memorando
{
	//Atributos
	private $id_memorando;
	private $id_pessoa;
	private $id_status_memorando;
	private $titulo;
	private $data;

	public function __construct($titulo)
	{
		$this->titulo = $titulo;
	}

	//Retorna o id de um memorando
	public function getId_memorando()
	{
		return $this->id_memorando;
	}

	//Retorna o id de uma pessoa
	public function getId_pessoa()
	{
		return $this->id_pessoa;
	}

	//Retorna o id do status do memorando
	public function getId_status_memorando()
	{
		return $this->id_status_memorando;
	}

	//Retorna um título de um memorando
	public function getTitulo()
	{
		return $this->titulo;
	}

	//Retorna a data de um memorando
	public function getData()
	{
		return $this->data;
	}

	//Define o id de um memorando
	public function setId_memorando($id_memorando)
	{
		$this->id_memorando = $id_memorando;
	}

	//Define o id de uma pessoa
	public function setId_pessoa($id_pessoa)
	{
		$this->id_pessoa = $id_pessoa;
	}

	//Define o id de status do memorando
	public function setId_status_memorando($id_status_memorando)
	{
		$this->id_status_memorando = $id_status_memorando;
	}

	//Define o título do memorando
	public function setTitulo($titulo)
	{
		$this->titulo = $titulo;
	}

	//Define a data do memorando
	public function setData($data = null)
	{
		if ($data) {
			$this->data = $data;
		} else {
			Util::definirFusoHorario();
			$data = date('Y-m-d H:i:s');
			$this->data = $data;
		}
	}
}
