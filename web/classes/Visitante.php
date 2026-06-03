<?php
require_once 'Pessoa.php';

class Visitante extends Pessoa
{
    private $idVisitante;
    private $idPessoa;
    private $idVisitanteTipo;
    private $idSituacao;

    public function getIdVisitante()
    {
        return $this->idVisitante;
    }
    public function getIdPessoa()
    {
        return $this->idPessoa;
    }
    public function getIdVisitanteTipo()
    {
        return $this->idVisitanteTipo;
    }
    public function getIdSituacao()
    {
        return $this->idSituacao;
    }

    public function setIdVisitante($idVisitante)
    {
        if(!$idVisitante || $idVisitante < 1)
            throw new InvalidArgumentException('O ID do visitante fornecido não é válido.', 412);
        $this->idVisitante = $idVisitante;
    }
    public function setIdPessoa($idPessoa)
    {
        $this->idPessoa = $idPessoa;
    }
    public function setIdVisitanteTipo($idVisitanteTipo)
    {
        $this->idVisitanteTipo = $idVisitanteTipo;
    }
    public function setIdSituacao($idSituacao)
    {
        $this->idSituacao = $idSituacao;
    }

    /**
     * Retorna a data mínima de nascimento permitida para um visitante ser cadastrado no sistema
     */
    static public function getDataNascimentoMinima()
    {
        $idadeMaxima = 170;
        $data = date('Y-m-d', strtotime("-$idadeMaxima years"));
        return $data;
    }

    /**
     * Retorna a data máxima de nascimento permitida para um visitante ser cadastrado no sistema
     */
    static public function getDataNascimentoMaxima()
    {
        $idadeMinima = 0;
        $data = date('Y-m-d', strtotime("-$idadeMinima years"));
        return $data;
    }
}