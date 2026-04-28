<?php
namespace api\modules\Auth;

use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }
    public function findByLogin(string $login): ?array
    {
        $query = "SELECT * FROM pessoa WHERE cpf = :login";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['login' => $login]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    public function save(array $user): array
    {
        $query = "INSERT INTO pessoa (cpf, senha) VALUES (:login, :senha)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'login' => $user['login'],
            'senha' => $user['senha']
        ]);

        return [
            'id' => $this->pdo->lastInsertId(),
            'login' => $user['login']
        ];
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $query = "UPDATE pessoa SET senha = :senha WHERE id_pessoa = :id_pessoa";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'senha' => $passwordHash,
            'id_pessoa' => $userId,
        ]);
    }
}
