<?php
require_once dirname(__DIR__).'/dao/ConexaoDAO.php';
require_once dirname(__DIR__).'/dao/BrandDAO.php';
require_once dirname(__DIR__).'/model/Brand.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'Util.php';

class BrandController
{

    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        try {
            isset($pdo) ? $this->pdo = $pdo : $this->pdo = ConexaoDAO::conectar();
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }

    public function getBrand(): ?Brand
    {
        try {
            $brandDao = new BrandDAO($this->pdo);
            $brand = $brandDao->getBrand();

            return $brand;
        } catch (Exception $e) {
            Util::tratarException($e);
            exit();
        }
    }
}
