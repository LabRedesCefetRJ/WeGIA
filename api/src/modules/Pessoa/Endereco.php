<?php

namespace api\modules\Pessoa;

class Endereco
{
    private int $id;
    private string $logradouro;
    private string $numero;
    private string $bairro;
    private string $cidade;
    private string $estado;
    private string $cep;

    public function __construct(
        string $logradouro,
        string $numero,
        string $bairro,
        string $cidade,
        string $estado,
        string $cep,
        ?int $id = null
    ) {
        $this->setLogradouro($logradouro)
            ->setNumero($numero)
            ->setBairro($bairro)
            ->setCidade($cidade)
            ->setEstado($estado)
            ->setCep($cep);

        if ($id !== null)
            $this->setId($id);
    }

    //getters e setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogradouro(): string
    {
        return $this->logradouro;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getBairro(): string
    {
        return $this->bairro;
    }

    public function getCidade(): string
    {
        return $this->cidade;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getCep(): string
    {
        return $this->cep;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setLogradouro(string $logradouro)
    {
        $this->logradouro = $logradouro;
        return $this;
    }

    public function setNumero(string $numero)
    {
        $this->numero = $numero;
        return $this;
    }

    public function setBairro(string $bairro)
    {
        $this->bairro = $bairro;
        return $this;
    }

    public function setCidade(string $cidade)
    {
        $this->cidade = $cidade;
        return $this;
    }

    public function setEstado(string $estado)
    {
        $this->estado = $estado;
        return $this;
    }

    public function setCep(string $cep)
    {
        $this->cep = $cep;
        return $this;
    }
}
