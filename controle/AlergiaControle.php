<?php
require_once dirname(__DIR__) . '/dao/Conexao.php';
require_once dirname(__DIR__) . '/dao/AlergiaDAO.php';
require_once dirname(__DIR__) . '/classes/Util.php';

class AlergiaControle
{
    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = Conexao::connect();
        }
    }

    public function listarTodasAsAlergias()
    {
        header('Content-Type: application/json');

        try {
            $alergiaDao = new AlergiaDAO($this->pdo);
            $alergias = $alergiaDao->listarTodasAsAlergias();

            echo json_encode($alergias);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
    }
}
