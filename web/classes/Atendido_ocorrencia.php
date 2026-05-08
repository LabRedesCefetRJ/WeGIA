<?php

class Ocorrencia
{
	private $idatendido_ocorrencias;
	private $atendido_idatendido;
	private $funcionario_idfuncionario;
	private $id_tipos_ocorrencia;
	private $descricao;
	private $data;
	// private $nome;

	public function __construct($descricao)
	{
		$this->descricao=$descricao;
	}

	public function getIdatendido_ocorrencias()
	{
		return $this->idatendido_ocorrencias;
	}

	// public function getNome()
	// {
	// 	return $this->nome;
	// }

	public function getData()
	{
		return $this->data;
	}

	public function getAtendido_idatendido()
	{
		return $this->atendido_idatendido;
	}

	public function getFuncionario_idfuncionario()
	{
		return $this->funcionario_idfuncionario;
	}

	public function getId_tipos_ocorrencia()
	{
		return $this->id_tipos_ocorrencia;
	}

	public function getDescricao()
	{
		return $this->descricao;
	}

	public function setIdatendido_ocorrencias($idatendido_ocorrencias)
	{
		$this->idatendido_ocorrencias = $idatendido_ocorrencias;
	}

	public function setAtendido_idatendido($atendido_idatendido)
	{
		$this->atendido_idatendido = $atendido_idatendido;
	}

	// public function setNome($nome)
	// {
	// 	$this->nome = $nome;
	// }


	public function setFuncionario_idfuncionario($funcionario_idfuncionario)
	{
		$this->funcionario_idfuncionario = $funcionario_idfuncionario;
	}

	public function setId_tipos_ocorrencia($id_tipos_ocorrencia)
	{
		$this->id_tipos_ocorrencia = $id_tipos_ocorrencia;
	}

	public function setDescricao($descricao)
	{
		$this->descricao = $descricao;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

}
?>