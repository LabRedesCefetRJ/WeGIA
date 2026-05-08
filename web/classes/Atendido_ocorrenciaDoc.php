<?php

class Atendido_ocorrenciaDoc
{
	private $idatendido_ocorrencia_doc;
	private $atentido_ocorrencia_idatentido_ocorrencias;
	private $anexo;
	private $extensao;
	private $nome;

	public function getIdatendido_ocorrencia_doc()
	{
		return $this->idatendido_ocorrencia_doc;
	}

	public function getAtentido_ocorrencia_idatentido_ocorrencias()
	{
		return $this->atentido_ocorrencia_idatentido_ocorrencias;
	}

	public function getAnexo()
	{
		return $this->anexo;
	}

	public function getExtensao()
	{
		return $this->extensao;
	}

	public function getNome()
	{
		return htmlspecialchars($this->nome, ENT_QUOTES, 'UTF-8'); 
	}

	public function setIdatendido_ocorrencia_doc($idatendido_ocorrencia_doc)
	{
		if (is_int($idatendido_ocorrencia_doc)) {
			$this->idatendido_ocorrencia_doc = $idatendido_ocorrencia_doc;
		} else {
			throw new InvalidArgumentException("ID inválido");
		}
	}

	public function setAtentido_ocorrencia_idatentido_ocorrencias($atentido_ocorrencia_idatentido_ocorrencias)
	{
		if (is_int($atentido_ocorrencia_idatentido_ocorrencias)) {
			$this->atentido_ocorrencia_idatentido_ocorrencias = $atentido_ocorrencia_idatentido_ocorrencias;
		} else {
			throw new InvalidArgumentException("ID da ocorrência inválido");
		}
	}

	public function setAnexo($anexo)
	{
		$this->anexo = $anexo;
	}

	public function setExtensao($extensao)
	{
		$extensoesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
		if (in_array(strtolower($extensao), $extensoesPermitidas)) {
			$this->extensao = $extensao;
		} else {
			throw new InvalidArgumentException("Extensão de arquivo inválida");
		}
	}

	public function setNome($nome)
	{
		$this->nome = filter_var($nome, FILTER_SANITIZE_STRING);
	}
}
?>
