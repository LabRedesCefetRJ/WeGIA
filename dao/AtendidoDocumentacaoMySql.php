<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'AtendidoDocumentacaoDAO.php';
class AtendidoDocumentacaoMySql implements AtendidoDocumentacaoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    public function create(AtendidoDocumentacao $atendidoDocumentacao): int|false
    {
        $query = 'INSERT INTO 
                    atendido_documentacao (atendido_idatendido, atendido_docs_atendidos_idatendido_docs_atendidos, id_pessoa_arquivo)
                    VALUES (:idAtendido, :idTipoDocumentacao, :idPessoaArquivo)
                ';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':idAtendido', $atendidoDocumentacao->getIdAtendido(), PDO::PARAM_INT);
        $stmt->bindValue(':idTipoDocumentacao', $atendidoDocumentacao->getIdTipoDocumentacao(), PDO::PARAM_INT);
        $stmt->bindValue(':idPessoaArquivo', $atendidoDocumentacao->getIdPessoaArquivo(), PDO::PARAM_INT);

        if(!$stmt->execute())
            return false;

        return (int) $this->pdo->lastInsertId();
    }

    public function getAll(): ?array
    {
        throw new \Exception('Not implemented');
    }
}
