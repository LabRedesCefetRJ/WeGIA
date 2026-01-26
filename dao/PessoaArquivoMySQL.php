<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PessoaArquivoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivoDTO.php';

class PessoaArquivoMySQL implements PessoaArquivoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    public function create(PessoaArquivo $pessoaArquivo): int|false
    {
        $query = 'INSERT INTO 
                    pessoa_arquivo (id_pessoa, arquivo_nome, arquivo_extensao, arquivo) 
                    VALUES (:idPessoa, :arquivoNome, :arquivoExtensao, :arquivo)
                ';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':idPessoa', $pessoaArquivo->getIdPessoa(), PDO::PARAM_INT);
        $stmt->bindValue(':arquivoNome', $pessoaArquivo->getArquivo()->getNome(), PDO::PARAM_STR);
        $stmt->bindValue(':arquivoExtensao', $pessoaArquivo->getArquivo()->getExtensao(), PDO::PARAM_STR);
        $stmt->bindValue(':arquivo', gzcompress($pessoaArquivo->getArquivo()->getConteudo()), PDO::PARAM_LOB);

        if(!$stmt->execute())
            return false;

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $query = 'DELETE FROM pessoa_arquivo WHERE id=:id';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function getById(int $id): PessoaArquivoDTO|null
    {
        $query = 'SELECT * FROM pessoa_arquivo WHERE id=:id';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $pessoaArquivoDto = new PessoaArquivoDTO($resultado);
        $pessoaArquivoDto->arquivo = Arquivo::fromDatabase($resultado['arquivo'], $resultado['arquivo_nome'], $resultado['arquivo_extensao']);

        return $pessoaArquivoDto;
    }

    public function getAll(): array
    {
        throw new \Exception('Not implemented');
    }
}
