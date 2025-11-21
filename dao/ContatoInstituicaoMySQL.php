<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ContatoInstituicaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'ContatoInstituicao.php';

class ContatoInstituicaoMySQL implements ContatoInstituicaoDAO{
    //atributos
    private PDO $pdo;

    /**
     * Classe de gerenciamento da persistência do contato da instituição no banco de dados MySQL.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function incluir(ContatoInstituicao $contato): int|false
    {
        $sql = 'INSERT INTO contato_instituicao(descricao, contato) VALUES (:descricao, :contato)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':descricao', $contato->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':contato', $contato->getContato(), PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() < 1)
            return false;

        return intval($this->pdo->lastInsertId());
    }

    public function listarPorId(int $id): ?ContatoInstituicao
    {
        throw new \Exception('Not implemented');
    }

    public function listarTodos(): ?array
    {
        throw new \Exception('Not implemented');
    }

    public function alterar(ContatoInstituicao $contato): bool
    {
        throw new \Exception('Not implemented');
    }

    public function excluirPorId(int $id): bool
    {
        throw new \Exception('Not implemented');
    }
}