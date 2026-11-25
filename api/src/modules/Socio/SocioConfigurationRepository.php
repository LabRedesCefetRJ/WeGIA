<?php

namespace api\modules\Socio;

use PDO;

class SocioConfigurationRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT id, type, value FROM socio_configuration ORDER BY id'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByType(string $type): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, type, value FROM socio_configuration WHERE type = :type LIMIT 1'
        );
        $stmt->execute([':type' => $type]);
        $configuration = $stmt->fetch(PDO::FETCH_ASSOC);

        return $configuration === false ? null : $configuration;
    }

    public function create(string $type, string $value): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO socio_configuration (type, value) VALUES (:type, :value)'
        );
        $stmt->execute([
            ':type' => $type,
            ':value' => $value,
        ]);

        return [
            'id' => (int)$this->db->lastInsertId(),
            'type' => $type,
            'value' => $value,
        ];
    }

    public function update(string $type, string $value): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE socio_configuration SET value = :value WHERE type = :type'
        );

        return $stmt->execute([
            ':type' => $type,
            ':value' => $value,
        ]);
    }

    public function delete(string $type): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM socio_configuration WHERE type = :type'
        );
        $stmt->execute([':type' => $type]);

        return $stmt->rowCount() > 0;
    }
}
