<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR .'SocioHasTagDTO.php';
require_once dirname(__DIR__) .  DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SocioHasTagDAO.php';

class SocioHasTag {
    private ?int $id;
    private int $socioId;
    private int $tagId;
    private SocioHasTagDAO $dao;

    public function __construct(SocioHasTagDTO $dto, SocioHasTagDAO $dao) {
        $this->setSocioId($dto->socioId)->setTagId($dto->tagId);

        if ($dto->id !== null) {
            $this->setId($dto->id);
        }

        $this->dao = $dao;
    }

    public function create() {
        return $this->dao->create($this);
    }

    public static function delete(int $id, SocioHasTagDAO $dao) {
        return $dao->delete($id);
    }

    public static function getById(int $id, SocioHasTagDAO $dao) {
        return $dao->getById($id);
    }

    public static function getAllBySocioId(int $socioId, SocioHasTagDAO $dao) {
        return $dao->getAllBySocioId($socioId);
    }

    public function getSocioId() {
        return $this->socioId;
    }

    public function getTagId() {
        return $this->tagId;
    }

    public function getId() {
        return $this->id;
    }

    public function setId(int $id) {
        if ($id < 1) {
            throw new InvalidArgumentException('O id deve ser maior que 0.', 412);
        }
        $this->id = $id;
        return $this;
    }

    public function setSocioId(int $socioId) {
        if ($socioId < 1) {
            throw new InvalidArgumentException('O id do sócio deve ser maior que 0.', 412);
        }
        $this->socioId = $socioId;
        return $this;
    }

    public function setTagId(int $tagId) {
        if ($tagId < 1) {
            throw new InvalidArgumentException('O id da tag deve ser maior que 0.', 412);
        }
        $this->tagId = $tagId;
        return $this;
    }
}
