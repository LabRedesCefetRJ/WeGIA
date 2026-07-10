<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SocioHasTag.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SocioHasTagDTO.php';

interface SocioHasTagDAO {
    public function __construct(PDO $pdo);
    public function create(SocioHasTag $socioHasTag):bool;
    public function delete(int $id):bool;
    public function getById(int $id):SocioHasTagDTO|false;
    public function getAllBySocioId(int $socioId):array|false;
    public function deleteBySocioId(int $socioId):bool;
    public function sync(int $socioId, array $tagIds):bool;
    public function getTagIdsBySocioId(int $socioId):array;
}
