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
        $sql = 'SELECT * FROM contato_instituicao WHERE id=:id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() < 1)
            return null;

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        //Instaciar um objeto do tipo ContatoInstituicao
        $contatoInstituicao = new ContatoInstituicao($resultado['descricao'], $resultado['contato']);
        $contatoInstituicao->setId($resultado['id']);

        return $contatoInstituicao;
    }

    public function listarTodos(): ?array
    {
        $sql = 'SELECT * FROM contato_instituicao';
        $query = $this->pdo->query($sql);

        if($query->rowCount() < 1)
            return null;

        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        $contatos = [];

        foreach($resultado as $contato){
            $contatos []= new ContatoInstituicao($contato['descricao'], $contato['contato'])->setId($contato['id']);
        }

        return $contatos;
    }

    public function alterar(ContatoInstituicao $contato): bool
    {
        $sql = "UPDATE contato_instituicao SET descricao=:descricao, contato=:contato WHERE id=:id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':descricao', $contato->getDescricao(), PDO::PARAM_STR);
        $stmt->bindValue(':contato', $contato->getContato(), PDO::PARAM_STR);
        $stmt->bindValue(':id', $contato->getId(), PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() < 1)
            return false;

        return true;
    }

    public function excluirPorId(int $id): bool
    {
        throw new \Exception('Not implemented');
    }
}