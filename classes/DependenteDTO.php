<?php
class DependenteDTO
{
    public ?int $id = null;
    public ?string $nome = null;
    public ?string $sobrenome = null;
    public ?string $sexo = null;
    public ?DateTime $nascimento = null;
    public ?string $telefone = null;
    public ?string $nomePai = null;
    public ?string $nomeMae = null;

    public function __construct(array $data)
    {
        if (key_exists('id_dependente', $data))
            $this->id = $data['id_dependente'];

        if (key_exists('nome', $data))
            $this->nome = $data['nome'];

        if (key_exists('sobrenome', $data))
            $this->sobrenome = $data['sobrenome'];

        if (key_exists('sexo', $data))
            $this->sexo = $data['sexo'];

        if (key_exists('nascimento', $data) || key_exists('data_nascimento', $data))
            $this->nascimento = new DateTime($data['nascimento'] ?? $data['data_nascimento']);

        if(key_exists('telefone', $data))
            $this->telefone = $data['telefone'];

        if(key_exists('nome_pai', $data))
            $this->nomePai = $data['nome_pai'];

        if(key_exists('nome_mae', $data))
            $this->nomeMae = $data['nome_mae'];
    }
}
