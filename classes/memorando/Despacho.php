<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'Util.php';

class Despacho
{
	//Atributos
	private $id_despacho;
	private $id_memorando;
	private $id_remetente;
	private $id_destinatario;
	private $texto;
	private $data;

	public function __construct($texto, $id_remetente, $id_destinatario, $id_memorando, $data = null)
	{
		$this->setTexto($texto);
		$this->setIdRemetente($id_remetente);
		$this->setIdDestinatario($id_destinatario);
		$this->setIdMemorando($id_memorando);
		$this->setData($data);
	}

	/**
	 * Retorna o id de um despacho
	 */
	public function getId_despacho()
	{
		return $this->id_despacho;
	}

	/**
	 * Retorna o id de um memorando
	 */
	public function getId_memorando()
	{
		return $this->id_memorando;
	}

	/**
	 * Retorna o id de um remetente
	 */
	public function getId_remetente()
	{
		return $this->id_remetente;
	}

	/**
	 * Retorna o id de um destinatario
	 */
	public function getId_destinatario()
	{
		return $this->id_destinatario;
	}

	/**
	 * Retorna o texto de um despacho
	 */
	public function getTexto()
	{
		return $this->texto;
	}

	/**
	 * Retorna a data de um despacho
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Define o id de um despacho
	 */
	public function setIdDespacho($id_despacho)
	{
		if (!$id_despacho || !is_numeric($id_despacho) || $id_despacho < 1) {
			throw new InvalidArgumentException('O id de um despacho deve ser um inteiro positivo maior ou igual a 1.');
		}
		$this->id_despacho = $id_despacho;
	}

	/**
	 * Define o id de um memorando
	 */
	public function setIdMemorando($id_memorando)
	{
		if (!$id_memorando || !is_numeric($id_memorando) || $id_memorando < 1) {
			throw new InvalidArgumentException('O id de um memorando deve ser um inteiro positivo maior ou igual a 1.');
		}
		$this->id_memorando = $id_memorando;
	}

	/**
	 * Define o id de um remetente
	 */
	public function setIdRemetente($id_remetente)
	{
		if (!$id_remetente || !is_numeric($id_remetente) || $id_remetente < 1) {
			throw new InvalidArgumentException('O id de um remetente deve ser um inteiro positivo maior ou igual a 1.');
		}
		$this->id_remetente = $id_remetente;
	}

	/**
	 * Define o id de um destinatario
	 */
	public function setIdDestinatario($id_destinatario)
	{
		if (!$id_destinatario || !is_numeric($id_destinatario) || $id_destinatario < 1) {
			throw new InvalidArgumentException('O id de um destinatário deve ser um inteiro positivo maior ou igual a 1.');
		}
		$this->id_destinatario = $id_destinatario;
	}

	/**
	 * Define o texto de um despacho
	 */
	public function setTexto(string $texto)
	{
		if (empty($texto)) {
			throw new InvalidArgumentException('O texto de um despacho não pode ser vazio.');
		}
		$this->texto = $texto;
	}

	/**
	 * Define a data de um despacho
	 */
	public function setData($data = null)
	{
		if ($data) {
			if (!$this->validarData($data)) {
				throw new InvalidArgumentException('A data informada não está no formato adequado: YYYY-MM-DD');
			}

			$this->data = $data;
		} else {
			Util::definirFusoHorario();
			$data = date('Y-m-d H:i:s');
			$this->data = $data;
		}
	}

	/**
	 * Função responsável por validar o formato da data informada
	 */
	private function validarData($data, $formato = 'Y-m-d')
	{
		$d = DateTime::createFromFormat($formato, $data);
		return $d && $d->format($formato) === $data;
	}
}
