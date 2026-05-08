<?php

class Anexo
{
	//Atributos
	private $id_anexo;
	private $id_despacho;
	private $anexo;
	private $extensao;
	private $nome;

	/**
	 * Retorna o id de um anexo
	 */
	public function getId_anexo()
	{
		return $this->id_anexo;
	}

	/**
	 * Retorna o id de despacho de um anexo
	 */
	public function getId_despacho()
	{
		return $this->id_despacho;
	}

	/**
	 * Retorna o conteúdo de um anexo
	 */
	public function getAnexo()
	{
		return $this->anexo;
	}

	/**
	 * Retorna a extensão de um anexo
	 */
	public function getExtensao()
	{
		return $this->extensao;
	}

	/**
	 * Retorna o nome de um anexo
	 */
	public function getNome()
	{
		return $this->nome;
	}

	/**
	 * Define o id de um anexo
	 */
	public function setId_anexo($id_anexo)
	{
		if (!$id_anexo || !is_numeric($id_anexo) || $id_anexo < 1) {
			throw new InvalidArgumentException('O id de um anexo deve ser um inteiro positivo maior ou igual a 1.');
		}
		$this->id_anexo = $id_anexo;
	}

	/**
	 * Define o id de despacho de um anexo
	 */
	public function setId_despacho($id_despacho)
	{
		if (!$id_despacho || !is_numeric($id_despacho) || $id_despacho < 1) {
			throw new InvalidArgumentException('O id de um despacho deve ser um inteiro positivo maior ou igual a 1.');
		}
		$this->id_despacho = $id_despacho;
	}

	/**
	 * Define o conteúdo de um anexo
	 */
	public function setAnexo(string $anexo)
	{
		if (empty($anexo)) {
			throw new InvalidArgumentException('O anexo não pode ser vazio.');
		}
		//adicionar posteriormente validação do tamanho do arquivo de anexo suportado
		$this->anexo = $anexo;
	}

	/**
	 * Define a extensão para um anexo
	 */
	public function setExtensao(string $extensao)
	{
		if (empty($extensao)) {
			throw new InvalidArgumentException('A extensão de um anexo não pode ser vazia.');
		}
		//Adicionar posteriormente validação dos tipos de extensão aceitados
		$this->extensao = $extensao;
	}

	/**
	 * Define o nome para um anexo
	 */
	public function setNome(string $nome)
	{
		if (empty($nome)) {
			throw new InvalidArgumentException('O nome de um anexo não pode ser vazio.');
		}
		$this->nome = $nome;
	}
}
