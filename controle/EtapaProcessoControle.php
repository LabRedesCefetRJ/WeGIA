<?php

session_start();
require_once ROOT . '/dao/Conexao.php';
require_once ROOT . '/dao/PaEtapaDAO.php';

class EtapaProcessoControle
{
    public function salvar()
    {
        $idProcesso  = (int)($_POST['id_processo'] ?? 0);
        $titulo   = trim($_POST['titulo'] ?? '');
        $dataInicio  = $_POST['data_inicio'] ?? null;
        $dataFim     = $_POST['data_fim'] ?? null;
        $statusId    = (int)($_POST['id_status'] ?? 1);

        if ($idProcesso <= 0 || empty($titulo)) {
            $_SESSION['mensagem_erro'] = 'Processo e descrição são obrigatórios.';
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();
        }

        $dataInicioFinal = $dataInicio ?: date('Y-m-d');
        $dataFimFinal    = ($dataFim !== null && trim($dataFim) !== '') ? trim($dataFim) : null;

        if ($dataFimFinal !== null) {
            $dtIni = new DateTime($dataInicioFinal);
            $dtFim = new DateTime($dataFimFinal);

            if ($dtFim < $dtIni) {
                $_SESSION['mensagem_erro'] = 'A data de conclusão não pode ser menor que a data de início da etapa.';
                header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
                exit();
            }
        }

        $pdo = Conexao::connect();
        $etapaDAO = new PaEtapaDAO($pdo);
        $etapaDAO->inserirEtapa($idProcesso, $statusId, $titulo, $dataInicioFinal, $dataFimFinal);

        $_SESSION['msg'] = 'Etapa cadastrada com sucesso.';
        header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
        exit();
    }

    public function atualizar()
    {
        $idEtapa    = (int)($_POST['id_etapa'] ?? 0);
        $idProcesso = (int)($_POST['id_processo'] ?? 0);
        $dataFim    = ($_POST['data_fim'] ?? '');
        $titulo  = trim($_POST['titulo'] ?? '');
        $statusId   = (int)($_POST['id_status'] ?? 1);

        if ($idEtapa <= 0 || $idProcesso <= 0 || empty($titulo)) {
            $_SESSION['mensagem_erro'] = 'Dados inválidos para edição.';
            header("Location: ../html/atendido/processo_aceitacao.php");
            exit();
        }

        $dataFimFinal = (trim($dataFim) !== '') ? trim($dataFim) : null;

        $pdo = Conexao::connect();
        $etapaDAO = new PaEtapaDAO($pdo);

        if ($dataFimFinal !== null) {
            $etapaAtual = $etapaDAO->buscarPorId($idEtapa);

            if (!$etapaAtual || empty($etapaAtual['data_inicio'])) {
                $_SESSION['mensagem_erro'] = 'Não foi possível validar as datas: etapa não encontrada.';
                header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
                exit();
            }

            $dtIni = new DateTime($etapaAtual['data_inicio']);
            $dtFim = new DateTime($dataFimFinal);

            if ($dtFim < $dtIni) {
                $_SESSION['mensagem_erro'] = 'A data de conclusão não pode ser menor que a data de início da etapa.';
                header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
                exit();
            }
        }

        $etapaDAO->atualizar($idEtapa, $statusId, $dataFimFinal, $titulo);

        $_SESSION['msg'] = 'Etapa atualizada com sucesso.';
        header("Location: ../html/atendido/etapa_processo.php?id={$idProcesso}");
        exit();
    }

    public function excluir()
    {
        $idEtapa    = (int)($_POST['id_etapa'] ?? 0);
        $idProcesso = (int)($_POST['id_processo'] ?? 0);

        if ($idEtapa <= 0 || $idProcesso <= 0) {
            $_SESSION['mensagem_erro'] = 'Dados inválidos para exclusão da etapa.';
            header("Location: ../html/atendido/processo_aceitacao.php");
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
