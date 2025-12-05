<?php

class EtapaProcessoControle
{
    public function salvar()
    {
        session_start();
        require_once ROOT . '/dao/Conexao.php';
        require_once ROOT . '/dao/PaEtapaDAO.php';

        $idProcesso  = (int)($_POST['id_processo'] ?? 0);
        $descricao   = trim($_POST['descricao'] ?? '');
        $dataInicio  = $_POST['data_inicio'] ?? null;
        $dataFim     = $_POST['data_fim'] ?? null;
        $statusId    = (int)($_POST['id_status'] ?? 1); 

        if ($idProcesso <= 0 || empty($descricao)) {
            $_SESSION['mensagem_erro'] = 'Processo e descrição são obrigatórios.';
            header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
            exit();
        }

        $pdo = Conexao::connect();
        $etapaDAO = new PaEtapaDAO($pdo);
        $etapaDAO->inserirEtapa($idProcesso, $statusId, $descricao, $dataInicio, $dataFim);

        $_SESSION['msg'] = 'Etapa cadastrada com sucesso.';
        header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
        exit();
    }

    public function atualizar()
    {
        session_start();
        require_once ROOT . '/dao/Conexao.php';
        require_once ROOT . '/dao/PaEtapaDAO.php';

        $idEtapa    = (int)($_POST['id_etapa'] ?? 0);
        $idProcesso = (int)($_POST['id_processo'] ?? 0);
        $dataFim    = $_POST['data_fim'] ?: null;
        $descricao  = trim($_POST['descricao'] ?? '');
        $statusId   = (int)($_POST['id_status'] ?? 1);

        if ($idEtapa <= 0 || $idProcesso <= 0 || empty($descricao)) {
            $_SESSION['mensagem_erro'] = 'Dados inválidos para edição.';
            header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
            exit();
        }

        $pdo = Conexao::connect();
        $etapaDAO = new PaEtapaDAO($pdo);
        $etapaDAO->atualizar($idEtapa, $statusId, $dataFim, $descricao);

        $_SESSION['msg'] = 'Etapa atualizada com sucesso.';
        header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
        exit();
    }

    public function excluir()
    {
        session_start();
        require_once ROOT . '/dao/Conexao.php';
        require_once ROOT . '/dao/PaEtapaDAO.php';

        $idEtapa    = (int)($_POST['id_etapa'] ?? 0);
        $idProcesso = (int)($_POST['id_processo'] ?? 0);

        if ($idEtapa <= 0 || $idProcesso <= 0) {
            $_SESSION['mensagem_erro'] = 'Dados inválidos para exclusão da etapa.';
            header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
            exit();
        }

        $pdo = Conexao::connect();
        $etapaDAO = new PaEtapaDAO($pdo);

        
        if ($etapaDAO->excluir($idEtapa)) {
            $_SESSION['msg'] = 'Etapa excluída com sucesso.';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir a etapa.';
        }

        header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
        exit();
    }
}
