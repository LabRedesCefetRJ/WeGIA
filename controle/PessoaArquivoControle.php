<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivo.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'PessoaArquivoMySQL.php';

class PessoaArquivoControle
{
    private ?PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    public function create()
    {
        try {
            $dados = $_POST;
            $dados['arquivo'] = Arquivo::fromUpload($_FILES['arquivo']);
            $pessoaArquivoDto = new PessoaArquivoDTO($dados);

            $pessoaArquivoMysql = new PessoaArquivoMySQL($this->pdo);
            $pessoaArquivo = new PessoaArquivo($pessoaArquivoDto, $pessoaArquivoMysql);

            $this->pdo->beginTransaction();
            $resultado = $pessoaArquivo->create();

            if($resultado === false)
                throw new RuntimeException('Falha na criação do arquivo de uma pessoa.', 500);

            if($resultado < 1)
                throw new LogicException('Falha na geração do id do arquivo da pessoa', 500);

            $this->pdo->commit();

            echo json_encode($resultado);
        } catch (Exception $e) {
            if($this->pdo->inTransaction())
                $this->pdo->rollBack();

            Util::tratarException($e);
        }
    }
}
