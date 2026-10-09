<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Setor.php";

class SetorDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        is_null($pdo) ? $this->pdo = Conexao::connect() : $this->pdo = $pdo;
    }

    public function incluir(Setor $setor)
    {
        $this->pdo->beginTransaction();

        try {
            $stmtExistente = $this->pdo->prepare("SELECT id_setor FROM setor WHERE descricao = :descricao AND status = :status LIMIT 1");
            $stmtExistente->bindValue(':descricao', $setor->getDescricao(), PDO::PARAM_STR);
            $stmtExistente->bindValue(':status', Setor::STATUS_ATIVO, PDO::PARAM_INT);
            $stmtExistente->execute();

            if ($stmtExistente->fetchColumn() !== false) {
                throw new InvalidArgumentException('Já existe um setor com essa descrição.', 409);
            }

            $stmtSetor = $this->pdo->prepare("INSERT INTO setor (descricao, status) VALUES (:descricao, :status)");
            $stmtSetor->bindValue(':descricao', $setor->getDescricao(), PDO::PARAM_STR);
            $stmtSetor->bindValue(':status', Setor::STATUS_ATIVO, PDO::PARAM_INT);
            $stmtSetor->execute();

            $idSetor = $this->pdo->lastInsertId();

            $this->pdo->commit();

            return $idSetor;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}