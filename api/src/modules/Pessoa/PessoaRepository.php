<?php

namespace api\modules\Pessoa;
use PDO;

class PessoaRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?array
    {
        $query = "SELECT * FROM pessoa WHERE id_pessoa = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function create(Pessoa $pessoa): int|false
    {
        $query = "INSERT INTO pessoa (nome, sobrenome, data_nascimento, sexo, telefone, email, cpf) 
                  VALUES (:nome, :sobrenome, :data_nascimento, :sexo, :telefone, :email, :cpf)";
        $stmt = $this->pdo->prepare($query);

        $resultado = $stmt->execute([
            'nome' => $pessoa->getNome(),
            'sobrenome' => $pessoa->getSobrenome(),
            'data_nascimento' => $pessoa->getDataNascimento() ? $pessoa->getDataNascimento()->format('Y-m-d') : null,
            'sexo' => $pessoa->getSexo(),
            'telefone' => $pessoa->getTelefone(),
            'email' => $pessoa->getEmail(),
            'cpf' => $pessoa->getCpf()
        ]);

        if (!$resultado || !$this->pdo->lastInsertId()) {
            return false;
        }

        return (int)$this->pdo->lastInsertId();
    }

    public function findByCpf(string $cpf): ?array
    {
        $query = "SELECT * FROM pessoa WHERE cpf = :cpf";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['cpf' => $cpf]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function update(int $id, array $dados): bool
    {
        $setClause = [];
        $params = ['id' => $id];
        $campos = [
            'nome',
            'sobrenome',
            'data_nascimento',
            'sexo',
            'telefone',
            'email',
            'cep',
            'estado',
            'cidade',
            'bairro',
            'logradouro',
            'numero_endereco',
            'complemento',
            'ibge'
        ];

        foreach ($campos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $setClause[] = "$campo = :$campo";
                $params[$campo] = $dados[$campo];
            }
        }

        if (empty($setClause)) {
            return true;
        }

        $query = "UPDATE pessoa SET " . implode(', ', $setClause) . " WHERE id_pessoa = :id";
        $stmt = $this->pdo->prepare($query);

        return $stmt->execute($params);
    }

    public function createJuridica(Pessoa $pessoa): int|false
    {
        $query = "INSERT INTO pessoa (nome, cpf, telefone, email, cep, estado, cidade, bairro, logradouro, numero_endereco, complemento) 
                  VALUES (:razao_social, :cnpj, :telefone, :email, :cep, :estado, :cidade, :bairro, :logradouro, :numero_endereco, :complemento)";
        $stmt = $this->pdo->prepare($query);

        $resultado = $stmt->execute([
            ':razao_social' => $pessoa->getNome(),
            ':cnpj' => $pessoa->getCpf(),
            ':telefone' => $pessoa->getTelefone(),
            ':email' => $pessoa->getEmail(),
            ':cep' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getCep() : null,
            ':estado' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getEstado() : null,
            ':cidade' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getCidade() : null,
            ':bairro' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getBairro() : null,
            ':logradouro' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getLogradouro() : null,
            ':numero_endereco' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getNumero() : null,
            ':complemento' => $pessoa->getEndereco() ? $pessoa->getEndereco()->getComplemento() : null
        ]);

        if (!$resultado || !$this->pdo->lastInsertId()) {
            return false;
        }

        return (int)$this->pdo->lastInsertId();
    }
}
