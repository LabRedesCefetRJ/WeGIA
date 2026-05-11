<?php
class Documento
{
	private $imagem;
	private $extensao;
	private $descricao;
	private $idPessoa;
	private $idDocumento;
	public function __construct($idPessoa, $imagem, $extensao, $descricao)
	{
		$this->setIdPessoa($idPessoa);
		$this->setImagem($imagem);
		$this->setExtensao($extensao);
		$this->setDescricao($descricao);
	}
	public function getImagem()
	{
		return $this->imagem;
	}
	public function getExtensao()
	{
		return $this->extensao;
	}
	public function getDescricao()
	{
		return $this->descricao;
	}
	public function getIdPessoa()
	{
		return $this->idPessoa;
	}
	public function getIdDocumento()
	{
		return $this->idDocumento;
	}
	public function setImagem(string $imagem)
	{
		if (empty($imagem)) {
			throw new InvalidArgumentException('A imagem de um documento não pode ser vazia.');
		}
		$this->imagem = $imagem;
	}
	public function setExtensao(string $extensao)
	{
		if (empty($extensao)) {
			throw new InvalidArgumentException('A extensão de um documento não pode ser vazia.');
		}
		$this->extensao = $extensao;
	}
	public function setDescricao(string $descricao)
	{
		if (empty($descricao)) {
			throw new InvalidArgumentException('A descrição de um documento não pode ser vazia.');
		}
		$this->descricao = $descricao;
	}
	public function setIdPessoa(int $idPessoa)
	{
		if ($idPessoa < 1) {
			throw new InvalidArgumentException('O id de uma pessoa não pode ser menor que 1.');
		}
		$this->idPessoa = $idPessoa;
	}
	public function setIdDocumento(int $idDocumento)
	{
		if ($idDocumento < 1) {
			throw new InvalidArgumentException('O id de um documento não pode ser menor que 1.');
		}
		$this->idDocumento = $idDocumento;
	}
}
