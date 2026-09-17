<?php
require_once 'Pessoa.php';

class Voluntario extends Pessoa
{
    private $id_voluntario;
    private $id_pessoa;
    private $id_situacao;
    private $id_cargo;
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

    public function getId_cargo()
    {
        return $this->id_cargo;
    }

    public function getData_admissao()
    {
        return $this->data_admissao;
    }

    public function setId_voluntario($id_voluntario)
    {
        if (!is_numeric($id_voluntario) || $id_voluntario < 0) {
            throw new InvalidArgumentException("O id deve ser um valor numérico não negativo.", 412);
        }
        $this->id_voluntario = $id_voluntario;
    }

    public function setId_pessoa($id_pessoa)
    {
        if (!is_numeric($id_pessoa) || $id_pessoa < 0) {
            throw new InvalidArgumentException("O id deve ser um valor numérico não negativo.", 412);
        }
        $this->id_pessoa = $id_pessoa;
    }

    public function setId_situacao($id_situacao)
    {
        if (!is_numeric($id_situacao) || $id_situacao < 0) {
            throw new InvalidArgumentException("O id deve ser um valor numérico não negativo.", 412);
        }
        $this->id_situacao = $id_situacao;
    }

    public function setId_cargo($id_cargo)
    {
        if (!is_numeric($id_cargo) || $id_cargo < 0) {
            throw new InvalidArgumentException("O id deve ser um valor numérico não negativo.", 412);
        }
        $this->id_cargo = $id_cargo;
    }

    public function setData_admissao(string $data_admissao)
    {
        $d = DateTime::createFromFormat("Y-m-d", $data_admissao);

        if (!$d || $d->format('Y-m-d') !== $data_admissao) {
            throw new InvalidArgumentException("A data deve estar no formato Y-m-d.", 412);
        }

        if ($d > new DateTime()) {
            throw new InvalidArgumentException("A data não pode ser futura.", 412);
        }
        $this->data_admissao = $data_admissao;
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
