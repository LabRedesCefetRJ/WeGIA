<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../dao/Conexao.php';
require_once __DIR__ . '/../dao/PaArquivoDAO.php';
require_once __DIR__ . '/../dao/ProcessoAceitacaoDAO.php';

require_once __DIR__ . '/../classes/Arquivo.php';
require_once __DIR__ . '/../classes/PessoaArquivoDTO.php';
require_once __DIR__ . '/../classes/PessoaArquivo.php';
require_once __DIR__ . '/../dao/PessoaArquivoMySQL.php';

class PaArquivoControle
{
    private PDO $pdo;
    private PaArquivoDAO $dao;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
        $this->dao = new PaArquivoDAO($this->pdo);
    }

    public function upload()
    {
        $idProcesso = (int)($_POST['id_processo'] ?? 0);
        $idTipoDoc  = (int)($_POST['id_tipo_documentacao'] ?? 0); 

        if ($idProcesso <= 0) {
            $_SESSION['mensagem_erro'] = 'Processo não informado.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            exit;
        }

        if ($idTipoDoc <= 0) {
            $_SESSION['mensagem_erro'] = 'Tipo de documento não informado.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            exit;
        }

        if (empty($_FILES['arquivo']['name'])) {
            $_SESSION['mensagem_erro'] = 'Arquivo não informado.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            exit;
        }

        try {
            $this->pdo->beginTransaction();

            $processoDao = new ProcessoAceitacaoDAO($this->pdo);
            $idPessoa = $processoDao->getIdPessoaByProcesso($idProcesso);

            $arquivo = Arquivo::fromUpload($_FILES['arquivo']);

            $pessoaArquivoDto = new PessoaArquivoDTO([
                'id_pessoa' => $idPessoa,
                'arquivo' => $arquivo
            ]);

            $pessoaArquivo = new PessoaArquivo(
                $pessoaArquivoDto,
                new PessoaArquivoMySQL($this->pdo)
            );

            $idPessoaArquivo = $pessoaArquivo->create();

            if ($idPessoaArquivo === false || $idPessoaArquivo < 1) {
                throw new RuntimeException('Erro ao salvar arquivo da pessoa.');
            }

            $ok = $this->dao->inserir($idProcesso, null, (int)$idPessoaArquivo, $idTipoDoc);

            if (!$ok) {
                throw new RuntimeException('Erro ao vincular arquivo ao processo.');
            }

            $this->pdo->commit();
            $_SESSION['msg'] = 'Arquivo do processo anexado com sucesso.';
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }

        header('Location: ../html/atendido/processo_aceitacao.php');
        exit;
    }

    public function excluir()
    {
        $idArquivo = filter_input(INPUT_POST, 'id_arquivo', FILTER_VALIDATE_INT);
        $idProcesso = filter_input(INPUT_POST, 'id_processo', FILTER_VALIDATE_INT);

        if (!$idArquivo || !$idProcesso) {
            $_SESSION['mensagem_erro'] = 'Dados inválidos para exclusão.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            exit;
        }

        try {
            if ($this->dao->excluir((int)$idArquivo)) {
                $_SESSION['msg'] = 'Arquivo removido com sucesso.';
            } else {
                $_SESSION['mensagem_erro'] = 'Erro ao remover arquivo.';
            }
        } catch (Exception $e) {
            $_SESSION['mensagem_erro'] = $e->getMessage();
        }

        header('Location: ../html/atendido/processo_aceitacao.php');
        exit;
    }
}
