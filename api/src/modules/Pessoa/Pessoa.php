<?php

namespace api\modules\Pessoa;

use api\contracts\PessoaInterface;
use DateTime;

class Pessoa implements PessoaInterface, \JsonSerializable
{
    private ?int $id = null;
    private string $nome;
    private string $sobrenome;
    private ?DateTime $dataNascimento = null;
    private ?string $sexo = null;
    private ?string $telefone = null;
    private string $cpf;
    private ?Endereco $endereco = null;

    public function __construct(
        string $nome,
        string $sobrenome,
        string $cpf,
        ?DateTime $dataNascimento = null,
        ?string $sexo = null,
        ?string $telefone = null,
        ?Endereco $endereco = null,
        ?int $id = null
    ) {
        $this->setNome($nome)
            ->setSobrenome($sobrenome)
            ->setCpf($cpf)
            ->setDataNascimento($dataNascimento)
            ->setSexo($sexo)
            ->setTelefone($telefone)
            ->setEndereco($endereco);

        if ($id !== null)
            $this->setId($id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getSobrenome(): string
    {
        return $this->sobrenome;
    }

    public function getDataNascimento(): ?DateTime
    {
        return $this->dataNascimento;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function getTelefone(): ?string
    {
        return $this->telefone;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getEndereco(): ?Endereco
    {
        return $this->endereco;
    }

    public function setEndereco(Endereco $endereco)
    {
        $this->endereco = $endereco;
        return $this;
    }

    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setNome(string $nome)
    {
        $this->nome = $nome;
        return $this;
    }

    public function setSobrenome(string $sobrenome)
    {
        $this->sobrenome = $sobrenome;
        return $this;
    }

    public function setDataNascimento(DateTime $dataNascimento)
    {
        $this->dataNascimento = $dataNascimento;
        return $this;
    }

    public function setSexo(string $sexo)
    {
        $this->sexo = $sexo;
        return $this;
    }

    public function setTelefone(string $telefone)
    {
        $this->telefone = $telefone;
        return $this;
    }

    public function setCpf(string $cpf)
    {
        $this->cpf = $cpf;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'sobrenome' => $this->sobrenome,
            'dataNascimento' => $this->dataNascimento ? $this->dataNascimento->format('Y-m-d') : null,
            'sexo' => $this->sexo,
            'telefone' => $this->telefone,
            'cpf' => $this->cpf,
            'endereco' => $this->endereco ? [
                'logradouro' => $this->endereco->getLogradouro(),
                'numero' => $this->endereco->getNumero(),
                'bairro' => $this->endereco->getBairro(),
                'cidade' => $this->endereco->getCidade(),
                'estado' => $this->endereco->getEstado(),
                'cep' => $this->endereco->getCep()
            ] : null
        ];
    }
}
