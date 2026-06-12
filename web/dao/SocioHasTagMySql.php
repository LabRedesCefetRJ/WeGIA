<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SocioHasTagDAO.php';

class SocioHasTagMySql implements SocioHasTagDAO
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(SocioHasTag $socioHasTag): bool
    {
        $sql = 'INSERT INTO socio_has_tag (id_socio, id_sociotag) VALUES (:idSocio, :idTag)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idSocio', $socioHasTag->getSocioId(), PDO::PARAM_INT);
        $stmt->bindValue(':idTag', $socioHasTag->getTagId(), PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM socio_has_tag WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getById(int $id): SocioHasTagDTO|false
    {
        $stmt = $this->pdo->prepare('SELECT id, id_socio, id_sociotag FROM socio_has_tag WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        return $registro ? new SocioHasTagDTO($registro) : false;
    }

    public function getAllBySocioId(int $socioId): array|false
    {
        $stmt = $this->pdo->prepare('SELECT id, id_socio, id_sociotag FROM socio_has_tag WHERE id_socio = :idSocio ORDER BY id ASC');
        $stmt->bindValue(':idSocio', $socioId, PDO::PARAM_INT);
        $stmt->execute();

        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($registros)) {
            return false;
        }

        return array_map(static fn(array $registro): SocioHasTagDTO => new SocioHasTagDTO($registro), $registros);
    }

    public function deleteBySocioId(int $socioId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM socio_has_tag WHERE id_socio = :idSocio');
        $stmt->bindValue(':idSocio', $socioId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function sync(int $socioId, array $tagIds): bool
    {
        $tagIdsNormalizadas = [];

        foreach ($tagIds as $tagId) {
            $tagId = (int) $tagId;

            if ($tagId > 0) {
                $tagIdsNormalizadas[$tagId] = $tagId;
            }
        }

        if (empty($tagIdsNormalizadas)) {
            return $this->deleteBySocioId($socioId);
        }

        if (!$this->deleteBySocioId($socioId)) {
            return false;
        }

        $stmt = $this->pdo->prepare('INSERT INTO socio_has_tag (id_socio, id_sociotag) VALUES (:idSocio, :idTag)');

        foreach ($tagIdsNormalizadas as $tagId) {
            $stmt->bindValue(':idSocio', $socioId, PDO::PARAM_INT);
            $stmt->bindValue(':idTag', $tagId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                return false;
            }
        }

        return true;
    }

    public function getTagIdsBySocioId(int $socioId): array
    {
        $stmt = $this->pdo->prepare('SELECT id_sociotag FROM socio_has_tag WHERE id_socio = :idSocio ORDER BY id ASC');
        $stmt->bindValue(':idSocio', $socioId, PDO::PARAM_INT);
        $stmt->execute();

        $tagIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_map('intval', $tagIds);
    }
}
