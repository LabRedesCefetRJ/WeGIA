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
        $campos = ['nome', 'sobrenome', 'data_nascimento', 'sexo', 'telefone', 'email'];

        foreach ($campos as $campo) {
            if (isset($dados[$campo])) {
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
}