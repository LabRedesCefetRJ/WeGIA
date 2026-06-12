<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'VisitanteDocumentacaoDAO.php';
class VisitanteDocumentacaoMySql implements VisitanteDocumentacaoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    public function create(VisitanteDocumentacao $visitanteDocumentacao) : int|false
    {
        $query = 'INSERT INTO 
                    visitante_documentacao (id_visitante, id_tipo_documentacao, id_pessoa_arquivo)
                    VALUES (:idVisitante, :idTipoDocumentacao, :idPessoaArquivo)
                ';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':idVisitante', $visitanteDocumentacao->getIdVisitante(), PDO::PARAM_INT);
        $stmt->bindValue(':idTipoDocumentacao', $visitanteDocumentacao->getIdTipoDocumentacao(), PDO::PARAM_INT);
        $stmt->bindValue(':idPessoaArquivo', $visitanteDocumentacao->getIdPessoaArquivo(), PDO::PARAM_INT);

        if(!$stmt->execute())
            return false;

        return (int) $this->pdo->lastInsertId();
    }

    public function getAll() : ?array
    {
        throw new \Exception('Not implemented.');
    }
}