<?php

class Endereco
{
	private $id_inst;
	private $nome;
	private $numero_endereco;
	private $logradouro;
	private $bairro;
	private $cidade;
	private $estado;
	private $cep;
	private $complemento;
	private $ibge;

	public function getId_inst()
	{
		return $this->id_inst;
	}
	public function getNome()
	{
		return $this->nome;
	}
	public function getNumeroEndereco()
	{
		return $this->numero_endereco;
	}
	public function getLogradouro()
	{
		return $this->logradouro;
	}
	public function getBairro()
	{
		return $this->bairro;
	}
	public function getCidade()
	{
		return $this->cidade;
	}
	public function getEstado()
	{
		return $this->estado;
	}
	public function getCep()
	{
		return $this->cep;
	}
	public function getComplemento()
	{
		return $this->complemento;
	}
	public function getIbge()
	{
		return $this->ibge;
	}
	public function setId_inst($id_inst)
	{
		$this->id_inst = $id_inst;
	}
	public function setNome($nome)
	{
		$this->nome = $nome;
	}
	public function setNumeroEndereco($numero_endereco)
	{
		$this->numero_endereco = $numero_endereco;
	}
	public function setLogradouro($logradouro)
	{
		$this->logradouro = $logradouro;
	}
	public function setBairro($bairro)
	{
		$this->bairro = $bairro;
	}
	public function setCidade($cidade)
	{
		$this->cidade = $cidade;
	}
	public function setEstado($estado)
	{
		$this->estado = $estado;
	}
	public function setCep($cep)
	{
		$this->cep = $cep;
	}
	public function setComplemento($complemento)
	{
		$this->complemento = $complemento;
	}
	public function setIbge($ibge)
	{
		$this->ibge = $ibge;
	}
}

?>

