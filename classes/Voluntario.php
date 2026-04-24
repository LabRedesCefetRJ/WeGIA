<?php
require_once 'Pessoa.php';

class Voluntario extends Pessoa
{
    private $id_voluntario;
    private $id_pessoa;
    private $id_situacao;
    private $data_admissao;

    public function getId_voluntario()
    {
        return $this->id_voluntario;
    }

    public function getId_pessoa()
    {
        return $this->id_pessoa;
    }

    public function getId_situacao()
    {
        return $this->id_situacao;
    }

    public function getData_admissao()
    {
        return $this->data_admissao;
    }

    public function setId_voluntario($id_voluntario)
    {
        $this->id_voluntario = $id_voluntario;
    }

    public function setId_pessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
    }

    public function setId_situacao($id_situacao)
    {
        $this->id_situacao = $id_situacao;
    }

    public function setData_admissao($data_admissao)
    {
        $this->data_admissao = $data_admissao;
    }

    /**
     * Retorna a data mínima de nascimento para o cadastro de um novo voluntário no sistema.
     */
    static public function getDataNascimentoMinima()
    {
        $idadeMaxima = 100;
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
