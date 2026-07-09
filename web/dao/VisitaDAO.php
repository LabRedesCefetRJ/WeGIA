<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Visita.php";

class VisitaDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function incluir($visita)
    {
        $this->pdo->beginTransaction();

        try {
            $sqlVisita = "INSERT INTO visita (id_visitante, id_visitado) VALUES (:id_visitante, :id_visitado)";

            $stmtVisita = $this->pdo->prepare($sqlVisita);

            $stmtVisita->bindValue(':id_visitante', $visita->getId_Visitante());
            $stmtVisita->bindValue(':id_visitado', $visita->getId_Visitado());

            $stmtVisita->execute();

            $idVisita = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $idVisita;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}