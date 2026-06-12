<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'PessoaArquivoMySQL.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'VisitanteDocumentacaoMySql.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'VisitanteDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'VisitanteDocumentacao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivo.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class VisitanteDocumentacaoControle
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    /**
     * Recebe a requisição POST e realiza os procedimentos necessários para salvar uma persistência de VisitanteDocumentacao no banco de dados do sistema.
     * Redireciona o usuário para a página Profile_Visitante do visitante selecionado.
     */
    public function create()
    {
        try {
            $dados = $_POST;
            $dados['arquivo'] = Arquivo::fromUpload($_FILES['arquivo']);

            $pessoaArquivoDTO = new PessoaArquivoDTO($dados);
            $pessoaArquivoMySQL = new PessoaArquivoMySQL($this->pdo);
            $visitanteDAO = new VisitanteDAO($this->pdo);
            $visitanteDocumentacaoDTO = new VisitanteDocumentacaoDTO($dados);
            $visitanteDocumentacaoMySql = new VisitanteDocumentacaoMySql($this->pdo);

            $this->pdo->beginTransaction();
            $idPessoa = $visitanteDAO->getIdPessoaByIdVisitante($visitanteDocumentacaoDTO->idVisitante);
            $pessoaArquivoDTO->idPessoa = $idPessoa;
            $pessoaArquivo = new PessoaArquivo($pessoaArquivoDTO, $pessoaArquivoMySQL);

            $idArquivo = $pessoaArquivo->create();

            if($idArquivo === false)
                throw new RuntimeException('Falha na criação do arquivo de uma pessoa.', 500);

            $visitanteDocumentacaoDTO->idPessoaArquivo = $idArquivo;
            $visitanteDocumentacao = new VisitanteDocumentacao($visitanteDocumentacaoDTO, $visitanteDocumentacaoMySql);

            $resultado = $visitanteDocumentacao->create();

            if($resultado === false)
                throw new RuntimeException('Falha na criação da documentação de um visitante.', 500);

            if($resultado < 1)
                throw new LogicException('Falha na geração do ID da documentação do visitante.', 500);

            $this->pdo->commit();

            // header("Location: ../html/visitante/Profile_Visitante.php?idvisitante={$visitanteDocumentacao->getIdVisitante()}");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction())
                $this->pdo->rollBack();
            Util::tratarException($e);
        }
    }
}