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

    /**
     * Verifica se uma pessoa possui um funcionário associado com cargo que tem permissão de acesso ao recurso especificado
     *
     * @param int $idPessoa ID da pessoa
     * @param int $resourceId ID do recurso a verificar (padrão: 4 para Sócio)
     * @return bool True se a pessoa tem acesso ao recurso, false caso contrário
     */
    public function hasAccessToResource(int $idPessoa, int $resourceId = 4): bool
    {
        $query = "SELECT p.id_acao 
                  FROM permissao p
                  INNER JOIN funcionario f ON f.id_cargo = p.id_cargo
                  WHERE f.id_pessoa = :id_pessoa 
                  AND p.id_recurso = :id_recurso
                  LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':id_pessoa' => $idPessoa,
            ':id_recurso' => $resourceId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Retorna true se encontrou uma permissão (tem acesso)
        return $result !== false;
    }

    /**
     * Obtém o nível de ação permitido para uma pessoa em um recurso específico
     *
     * @param int $idPessoa ID da pessoa
     * @param int $resourceId ID do recurso
     * @return int|null ID da ação permitida, ou null se não houver permissão
     */
    public function getAccessLevel(int $idPessoa, int $resourceId = 4): ?int
    {
        $query = "SELECT p.id_acao 
                  FROM permissao p
                  INNER JOIN funcionario f ON f.id_cargo = p.id_cargo
                  WHERE f.id_pessoa = :id_pessoa 
                  AND p.id_recurso = :id_recurso
                  LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':id_pessoa' => $idPessoa,
            ':id_recurso' => $resourceId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['id_acao'] : null;
    }

    /**
     * Verifica se uma pessoa possui um funcionário associado
     *
     * @param int $idPessoa ID da pessoa
     * @return bool True se a pessoa tem um funcionário associado
     */
    public function hasFuncionario(int $idPessoa): bool
    {
        $query = "SELECT id_funcionario FROM funcionario WHERE id_pessoa = :id_pessoa LIMIT 1";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':id_pessoa' => $idPessoa]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result !== false;
    }
}
