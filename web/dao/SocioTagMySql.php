<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SocioTagDAO.php';

class SocioTagMySql implements SocioTagDAO{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(): bool
    {
        throw new \Exception('Not implemented');
    }
    
    public function getById(int $id)
    {
        throw new \Exception('Not implemented');
    }

    public function getAll(): array
    {
        throw new \Exception('Not implemented');
    }

    public function delete(int $id): bool
    {
        $query = 'DELETE FROM socio_tag WHERE id_sociotag=:id';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
}