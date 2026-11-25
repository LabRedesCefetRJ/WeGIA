<?php
require_once 'Pessoa.php';

class Visitante extends Pessoa
{
    private $id_visitante;
    private $id_pessoa;

    public function getId_Visitante()
    {
        return $this->id_visitante;
    }
    public function getId_Pessoa()
    {
        return $this->id_pessoa;
    }
    
    public function setId_Visitante($id_visitante)
    {
        $this->id_visitante = $id_visitante;
    }
    public function setId_Pessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
    }

    /**
     * Retorna a data mínima de nascimento para o cadastro de um novo voluntário no sistema.
     */
    static public function getDataNascimentoMinima()
    {
        $idadeMaxima = 150;
        $data = date('Y-m-d', strtotime("-$idadeMaxima years"));
        return $data;
    }

    /**
     * Retorna a data máxima de nascimento para o cadastro de um novo voluntário no sistema.
     * Pode ser ajustado conforme regra de negócio (ex: 14 anos).
     */
    static public function getDataNascimentoMaxima()
    {
        $idadeMinima = 0;
        $data = date('Y-m-d', strtotime("-$idadeMinima years"));
        return $data;
    }
}