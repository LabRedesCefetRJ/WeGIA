<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'PessoaArquivoMySQL.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AtendidoDocumentacaoMySql.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'AtendidoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AtendidoDocumentacao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PessoaArquivo.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class AtendidoDocumentacaoControle
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    /**
     * Recebe a requisição POST e realiza os procedimentos necessários para salvar uma persistência de AtendidoDocumentacao no banco de dados do sistema.
     * Redireciona o usuário para a página Profile_Atendido do paciente selecionado.
     */
    public function create()
    {
        try {
            //pegar request
            $dados = $_POST;
            $dados['arquivo'] = Arquivo::fromUpload($_FILES['arquivo']);

            //instanciar objetos
            $pessoaArquivoDto = new PessoaArquivoDTO($dados);
            $pessoaArquivoMysql = new PessoaArquivoMySQL($this->pdo);

            $atendidoDao = new AtendidoDAO($this->pdo);

            $atendidoDocumentacaoDto = new AtendidoDocumentacaoDTO($dados);
            $atendidoDocumentacaoMysql = new AtendidoDocumentacaoMySql($this->pdo);

            //iniciar transação
            $this->pdo->beginTransaction();

            $idPessoa = $atendidoDao->getIdPessoaByIdAtendido($atendidoDocumentacaoDto->idAtendido);

            $pessoaArquivoDto->idPessoa = $idPessoa;
            $pessoaArquivo = new PessoaArquivo($pessoaArquivoDto, $pessoaArquivoMysql);

            //realizar procedimentos de inserção
            $idArquivo = $pessoaArquivo->create();

            if ($idArquivo === false)
                throw new RuntimeException('Falha na criação do arquivo de uma pessoa.', 500);

            $atendidoDocumentacaoDto->idPessoaArquivo = $idArquivo;
            $atendidoDocumentacao = new AtendidoDocumentacao($atendidoDocumentacaoDto, $atendidoDocumentacaoMysql);

            $resultado = $atendidoDocumentacao->create();

            //commitar|rollback transação
            if ($resultado === false)
                throw new RuntimeException('Falha na criação da documentação de um atendido.', 500);

            if ($resultado < 1)
                throw new LogicException('Falha na geração do id da documentação do atendido', 500);

            $this->pdo->commit();

            //retornar para página
            header("Location: ../html/atendido/Profile_Atendido.php?idatendido={$atendidoDocumentacao->getIdAtendido()}");
        } catch (Exception $e) {
            if ($this->pdo->inTransaction())
                $this->pdo->rollBack();

            Util::tratarException($e);
        }
    }
}
