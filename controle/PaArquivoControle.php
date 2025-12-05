<?php
class PaArquivoControle {
    private $dao;

    public function __construct() {
        require_once __DIR__ . '/../dao/Conexao.php';
        require_once __DIR__ . '/../dao/PaArquivoDAO.php';

        $pdo = Conexao::connect();
        $this->dao = new PaArquivoDAO($pdo);
    }

    public function upload() {
        

        $idProcesso = (int)($_POST['id_processo'] ?? 0);

        if ($idProcesso <= 0) {
            $_SESSION['mensagem_erro'] = 'Processo não informado.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            return;
        }

        if (empty($_FILES['arquivo']['name'])) {
            $_SESSION['mensagem_erro'] = 'Arquivo não informado.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            return;
        }

        $arquivo    = $_FILES['arquivo'];
        $ext        = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['png','jpg','jpeg','pdf','doc','docx','odp'];

        if (!in_array($ext, $permitidas)) {
            $_SESSION['mensagem_erro'] = 'Extensão não permitida.';
            header('Location: ../html/atendido/processo_aceitacao.php');
            return;
        }

        $blob = file_get_contents($arquivo['tmp_name']);

        
        $ok = $this->dao->inserir($idProcesso, null, $arquivo['name'], $ext, $blob);

        if ($ok) {
            $_SESSION['msg'] = 'Arquivo do processo anexado com sucesso.';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao salvar arquivo do processo.';
        }

        header('Location: ../html/atendido/processo_aceitacao.php');
        return;
    }
}
