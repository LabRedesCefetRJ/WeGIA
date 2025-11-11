<?php
require_once 'Pessoa.php';

class Atendido extends Pessoa
{

    private $idatendido;
    private $id_pessoa;
    private $intStatus;
    private $intTipo;

    public function getIdatendido()
    {
        return $this->idatendido;
    }
    public function getId_pessoa()
    {
        return $this->id_pessoa;
    }
    public function getIntStatus()
    {
        return $this->intStatus;
    }
    public function getIntTipo()
    {
        return $this->intTipo;
    }
   
    public function setIdatendido(int $idatendido)
    {
        if(!$idatendido || $idatendido < 1)
            throw new InvalidArgumentException('O id do atendido fornecido não é válido.', 412);

        $this->idatendido = $idatendido;
    }

    public function setIntStatus($intStatus)
    {
        $this->intStatus = $intStatus;
    }
    public function setIntTipo($intTipo)
    {
        $this->intTipo = $intTipo;
    }
    public function setId_pessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
    }

    /**
     * Retorna a data máxima de nascimento permitida para um atendido ser cadastrado no sistema
     */
    static public function getDataNascimentoMaxima()
    {
        $idadeMinima = 0;
        $data = date('Y-m-d', strtotime("-$idadeMinima years"));
        return $data;
    }

    /**
     * Retorna a data mínima de nascimento permitida para um atendido ser cadastrado no sistema
     */
    static public function getDataNascimentoMinima(){
        $idadeMaxima = 170;
        $data = date('Y-m-d', strtotime("-$idadeMaxima years"));
        return $data;
    }  
}