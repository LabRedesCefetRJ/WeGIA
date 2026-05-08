<?php
require_once 'Pessoa.php';

class Funcionario extends Pessoa
{
    private $id_funcionario;
    private $id_pessoa;
    private $id_cargo;
    private $id_situacao;
    private $data_admissao;
    private $pis;
    private $ctps;
    private $uf_ctps;
    private $numero_titulo;
    private $zona;
    private $secao;
    private $certificado_reservista_numero;
    private $certificado_reservista_serie;

    public function getId_funcionario()
    {
        return $this->id_funcionario;
    }
    public function getId_cargo()
    {
        return $this->id_cargo;
    }

    public function getId_pessoa()
    {
        return $this->id_pessoa;
    }

    public function getData_admissao()
    {
        return $this->data_admissao;
    }

    public function getPis()
    {
        return $this->pis;
    }

    public function getCtps()
    {
        return $this->ctps;
    }

    public function getUf_ctps()
    {
        return $this->uf_ctps;
    }

    public function getNumero_titulo()
    {
        return $this->numero_titulo;
    }

    public function getZona()
    {
        return $this->zona;
    }

    public function getSecao()
    {
        return $this->secao;
    }

    public function getCertificado_reservista_numero()
    {
        return $this->certificado_reservista_numero;
    }

    public function getCertificado_reservista_serie()
    {
        return $this->certificado_reservista_serie;
    }

    public function getId_situacao()
    {
        return $this->id_situacao;
    }

    public function setId_funcionario($id_funcionario)
    {
        $this->id_funcionario = $id_funcionario;
    }

    public function setId_pessoa($id_pessoa)
    {
        $this->id_pessoa = $id_pessoa;
    }

    public function setId_cargo($id_cargo)
    {
        $this->id_cargo = $id_cargo;
    }

    public function setData_admissao($data_admissao)
    {
        $this->data_admissao = $data_admissao;
    }

    public function setPis($pis)
    {
        $this->pis = $pis;
    }

    public function setCtps($ctps)
    {
        $this->ctps = $ctps;
    }

    public function setUf_ctps($uf_ctps)
    {
        $this->uf_ctps = $uf_ctps;
    }

    public function setNumero_titulo($numero_titulo)
    {
        $this->numero_titulo = $numero_titulo;
    }

    public function setZona($zona)
    {
        $this->zona = $zona;
    }

    public function setSecao($secao)
    {
        $this->secao = $secao;
    }

    public function setCertificado_reservista_numero($certificado_reservista_numero)
    {
        $this->certificado_reservista_numero = $certificado_reservista_numero;
    }

    public function setCertificado_reservista_serie($certificado_reservista_serie)
    {
        $this->certificado_reservista_serie = $certificado_reservista_serie;
    }

    public function setId_situacao($id_situacao)
    {
        $this->id_situacao = $id_situacao;
    }

    /**
     * Retorna a data máxima de nascimento para o cadastro de um novo funcionário no sistema.
     */
    static public function getDataNascimentoMaxima()
    {
        $idadeMinima = 14;
        $data = date('Y-m-d', strtotime("-$idadeMinima years"));
        return $data;
    }

    /**
     * Retorna a data mínima de nascimento para o cadastro de um novo funcionário no sistema.
     */
    static public function getDataNascimentoMinima()
    {
        $idadeMaxima = 100;
        $data = date('Y-m-d', strtotime("-$idadeMaxima years"));
        return $data;
    }
}