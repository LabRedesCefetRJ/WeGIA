<?php

require_once __DIR__ . '/../dao/Conexao.php';
require_once __DIR__ . '/../dao/EtapaArquivoDAO.php';
require_once __DIR__ . '/../dao/PaArquivoDAO.php';

class ArquivoEtapaControle
{
    private $daoEtapa;
    private $daoPa;

    public function __construct()
    {

        $pdo = Conexao::connect();
        $this->daoEtapa = new EtapaArquivoDAO($pdo);
        $this->daoPa    = new PaArquivoDAO($pdo);
    }

    public function upload()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idProcesso = (int) filter_input(INPUT_POST, 'id_processo', FILTER_SANITIZE_NUMBER_INT);
        $idEtapa    = (int) filter_input(INPUT_POST, 'id_etapa', FILTER_SANITIZE_NUMBER_INT);
        $alvo       = filter_input(INPUT_POST, 'alvo', FILTER_SANITIZE_STRING) ?: 'etapa';

        $urlRetorno = ($alvo === 'etapa')
            ? '../html/atendido/etapa_processo.php?id=' . $idProcesso
            : '../html/atendido/processo_aceitacao.php';

        if ($alvo === 'etapa' && $idEtapa <= 0) {
            $_SESSION['mensagem_erro'] = 'Etapa não informada.';
        } elseif ($alvo === 'processo' && $idProcesso <= 0) {
            $_SESSION['mensagem_erro'] = 'Processo não informado.';
        }

        if (!empty($_SESSION['mensagem_erro']) || empty($_FILES['arquivo']['name'])) {
            if (empty($_SESSION['mensagem_erro'])) {
                $_SESSION['mensagem_erro'] = 'Arquivo não informado.';
            }
            header('Location: ' . $urlRetorno);
            return;
        }

        $arquivo    = $_FILES['arquivo'];
        $ext        = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'odp'];

        if (!in_array($ext, $permitidas, true)) {
            $_SESSION['mensagem_erro'] = 'Extensão não permitida.';
            header('Location: ' . $urlRetorno);
            return;
        }

        $blob = file_get_contents($arquivo['tmp_name']);

        if ($alvo === 'etapa') {
            $ok = $this->daoEtapa->inserir($idEtapa, $arquivo['name'], $ext, $blob);
        } else {
            $ok = $this->daoPa->inserir($idProcesso, $idEtapa ?: null, $arquivo['name'], $ext, $blob);
        }

        if ($ok) {
            $_SESSION['msg'] = 'Arquivo anexado com sucesso.';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao salvar arquivo.';
        }

        header('Location: ' . $urlRetorno);
        return;
    }
}
