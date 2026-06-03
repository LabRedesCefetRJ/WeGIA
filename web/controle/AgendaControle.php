<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';

require_once ROOT . '/classes/Agenda.php';
require_once ROOT . '/classes/AgendaAlocacao.php';
require_once ROOT . '/classes/AgendaEquipe.php';
require_once ROOT . '/classes/AgendaEquipeMembro.php';
require_once ROOT . '/dao/AgendaDAO.php';
require_once ROOT . '/classes/Util.php';
require_once ROOT . '/classes/Notificacao.php';
require_once ROOT . '/dao/NotificacaoDAO.php';

class AgendaControle
{
    // -------------------------------------------------------
    // AGENDA
    // -------------------------------------------------------

    public function incluirAgenda()
    {
        header('Content-Type: application/json');

        try {
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_status = filter_input(INPUT_POST, 'id_status', FILTER_SANITIZE_NUMBER_INT);

            if (empty($descricao))
                throw new InvalidArgumentException('A descrição da agenda não pode ser vazia.', 412);

            if (!$id_status || $id_status < 1)
                throw new InvalidArgumentException('O status informado não é válido.', 412);

            $agenda = new Agenda();
            $agenda->setDescricao($descricao);
            $agenda->setId_status($id_status);

            $dao = new AgendaDAO();
            $dao->incluirAgenda($agenda);

            http_response_code(200);
            echo json_encode(['msg' => 'Agenda cadastrada com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarAgendas()
    {
        header('Content-Type: application/json');

        try {
            $dao = new AgendaDAO();
            echo json_encode($dao->listarAgendas());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarAgendaPorId()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            echo json_encode($dao->listarAgendaPorId($id));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function alterarAgenda()
    {
        header('Content-Type: application/json');

        try {
            $id        = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_status = filter_input(INPUT_POST, 'id_status', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            if (empty($descricao))
                throw new InvalidArgumentException('A descrição da agenda não pode ser vazia.', 412);

            if (!$id_status || $id_status < 1)
                throw new InvalidArgumentException('O status informado não é válido.', 412);

            $agenda = new Agenda();
            $agenda->setId($id);
            $agenda->setDescricao($descricao);
            $agenda->setId_status($id_status);

            $dao = new AgendaDAO();
            $dao->alterarAgenda($agenda);

            http_response_code(200);
            echo json_encode(['msg' => 'Agenda alterada com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function excluirAgenda()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            $dao->excluirAgenda($id);

            http_response_code(200);
            echo json_encode(['msg' => 'Agenda excluída com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarStatus()
    {
        header('Content-Type: application/json');

        try {
            $dao = new AgendaDAO();
            echo json_encode($dao->listarStatus());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    // -------------------------------------------------------
    // AGENDA ALOCACAO
    // -------------------------------------------------------

    public function incluirAlocacao()
    {
        header('Content-Type: application/json');

        try {
            $id_agenda        = filter_input(INPUT_POST, 'id_agenda', FILTER_SANITIZE_NUMBER_INT);
            $id_equipe        = filter_input(INPUT_POST, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);
            $inicio           = filter_input(INPUT_POST, 'inicio', FILTER_SANITIZE_SPECIAL_CHARS);
            $fim              = filter_input(INPUT_POST, 'fim', FILTER_SANITIZE_SPECIAL_CHARS);
            $lembrete         = filter_input(INPUT_POST, 'lembrete', FILTER_SANITIZE_SPECIAL_CHARS);
            $intervalo        = filter_input(INPUT_POST, 'intervalo', FILTER_SANITIZE_NUMBER_INT) ?? 0;
            $lembrete_enviado = 0;

            if (!$id_agenda || $id_agenda < 1)
                throw new InvalidArgumentException('A agenda informada não é válida.', 412);

            if (!$id_equipe || $id_equipe < 1)
                throw new InvalidArgumentException('A equipe informada não é válida.', 412);

            if (empty($inicio))
                throw new InvalidArgumentException('A data de início não pode ser vazia.', 412);

            if (empty($fim))
                throw new InvalidArgumentException('A data de fim não pode ser vazia.', 412);

            if ($inicio > $fim)
                throw new InvalidArgumentException('A data de início não pode ser maior que a data de fim.', 412);

            $alocacao = new AgendaAlocacao();
            $alocacao->setId_agenda($id_agenda);
            $alocacao->setId_equipe($id_equipe);
            $alocacao->setInicio($inicio);
            $alocacao->setFim($fim);
            $alocacao->setLembrete(!empty($lembrete) ? $lembrete : null);
            $alocacao->setLembrete_enviado($lembrete_enviado);
            $alocacao->setIntervalo((int)$intervalo);

            $dao = new AgendaDAO();

            if ($dao->existeAlocacaoSobreposta((int)$id_agenda, (int)$id_equipe, $inicio, $fim))
                throw new InvalidArgumentException('Já existe uma alocação desta equipe no período informado.', 409);

            $id = $dao->incluirAlocacao($alocacao);

            http_response_code(200);
            echo json_encode(['msg' => 'Alocação cadastrada com sucesso!', 'id' => (int)$id]);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarAlocacoesPorAgenda()
    {
        header('Content-Type: application/json');

        try {
            $id_agenda = filter_input(INPUT_GET, 'id_agenda', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_agenda || $id_agenda < 1)
                throw new InvalidArgumentException('O id da agenda informado não é válido.', 412);

            $dao = new AgendaDAO();
            echo json_encode($dao->listarAlocacoesPorAgenda($id_agenda));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarTodasAlocacoes()
    {
        header('Content-Type: application/json');

        try {
            $dao = new AgendaDAO();
            echo json_encode($dao->listarTodasAlocacoes());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function excluirAlocacao()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            $dao->excluirAlocacao($id);

            http_response_code(200);
            echo json_encode(['msg' => 'Alocação excluída com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function salvarLembrete()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $id       = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $lembrete = filter_input(INPUT_POST, 'lembrete', FILTER_SANITIZE_SPECIAL_CHARS);
            $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_SPECIAL_CHARS);
            $idPessoa = (int) ($_SESSION['id_pessoa'] ?? 0);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            if (!$idPessoa)
                throw new InvalidArgumentException('Usuário inválido.', 412);

            $agendaDao = new AgendaDAO();
            $alocacao  = $agendaDao->listarAlocacaoPorId((int) $id);

            if (empty($alocacao))
                throw new InvalidArgumentException('Alocação não encontrada.', 412);

            // Atualiza o campo lembrete na alocação (mantido para exibição no calendário)
            $agendaDao->salvarLembrete((int) $id, !empty($lembrete) ? $lembrete : null);

            $linkAlocacao = 'html/agenda/cadastrar_agenda.php';
            $notifDao     = new NotificacaoDAO();

            if (!empty($lembrete)) {
                // Formata data do lembrete para exibição
                $dtLembrete = new DateTime($lembrete);
                $dtFormatada = $dtLembrete->format('d/m/Y \à\s H:i');

                // Formata datas da alocação
                $dtInicio = (new DateTime($alocacao['inicio']))->format('d/m/Y');
                $dtFim    = (new DateTime($alocacao['fim']))->format('d/m/Y');

                $msgBase = 'Alocação da equipe "' . $alocacao['equipe'] . '" de ' . $dtInicio . ' a ' . $dtFim . '. Lembrete agendado para ' . $dtFormatada . '.';

                $msgFinal = !empty($mensagem) ? $msgBase . ' Mensagem: ' . $mensagem : $msgBase;

                $notificacao = new Notificacao(
                    10, // Módulo Agenda
                    'Lembrete: ' . $alocacao['equipe'],
                    $msgFinal,
                    'lembrete',
                    $linkAlocacao
                );

                $notifDao->criar($notificacao, [$idPessoa]);
            } else {
                // Lembrete removido: marca notificações pendentes desta alocação como visualizadas
                $notifDao->marcarPendentesComoVisualizadasPorReferencia(10, 'lembrete', $linkAlocacao);
            }

            http_response_code(200);
            echo json_encode(['msg' => 'Lembrete salvo com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarTodosMembrosAtivos()
    {
        header('Content-Type: application/json');

        try {
            $dao = new AgendaDAO();
            echo json_encode($dao->listarTodosMembrosAtivos());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function excluirMembro()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            $dao->excluirMembro($id);

            http_response_code(200);
            echo json_encode(['msg' => 'Membro removido com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarPessoas()
    {
        header('Content-Type: application/json');

        try {
            $idEquipe = filter_input(INPUT_GET, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);
            $dao = new AgendaDAO();
            echo json_encode($dao->listarPessoas($idEquipe ? (int)$idEquipe : null));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function alterarAlocacao()
    {
        header('Content-Type: application/json');

        try {
            $id               = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $id_agenda        = filter_input(INPUT_POST, 'id_agenda', FILTER_SANITIZE_NUMBER_INT);
            $id_equipe        = filter_input(INPUT_POST, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);
            $inicio           = filter_input(INPUT_POST, 'inicio', FILTER_SANITIZE_SPECIAL_CHARS);
            $fim              = filter_input(INPUT_POST, 'fim', FILTER_SANITIZE_SPECIAL_CHARS);
            $lembrete         = filter_input(INPUT_POST, 'lembrete', FILTER_SANITIZE_SPECIAL_CHARS);
            $lembrete_enviado = (int)(filter_input(INPUT_POST, 'lembrete_enviado', FILTER_SANITIZE_NUMBER_INT) ?? 0);
            $intervalo        = filter_input(INPUT_POST, 'intervalo', FILTER_SANITIZE_NUMBER_INT) ?? 0;

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            if ($inicio > $fim)
                throw new InvalidArgumentException('A data de início não pode ser maior que a data de fim.', 412);

            $alocacao = new AgendaAlocacao();
            $alocacao->setId($id);
            $alocacao->setId_agenda($id_agenda);
            $alocacao->setId_equipe($id_equipe);
            $alocacao->setInicio($inicio);
            $alocacao->setFim($fim);
            $alocacao->setLembrete(!empty($lembrete) ? $lembrete : null);
            $alocacao->setLembrete_enviado($lembrete_enviado);
            $alocacao->setIntervalo((int)$intervalo);

            $dao = new AgendaDAO();

            if ($dao->existeAlocacaoSobreposta((int)$id_agenda, (int)$id_equipe, $inicio, $fim, (int)$id))
                throw new InvalidArgumentException('Já existe uma alocação desta equipe no período informado.', 409);

            $dao->alterarAlocacao($alocacao);

            http_response_code(200);
            echo json_encode(['msg' => 'Alocação alterada com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    // -------------------------------------------------------
    // AGENDA EQUIPE
    // -------------------------------------------------------

    public function incluirEquipe()
    {
        header('Content-Type: application/json');

        try {
            $nome         = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_status    = filter_input(INPUT_POST, 'id_status', FILTER_SANITIZE_NUMBER_INT);
            $descricao    = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_agenda    = filter_input(INPUT_POST, 'id_agenda', FILTER_SANITIZE_NUMBER_INT);
            $inicio_turno = filter_input(INPUT_POST, 'inicio_turno', FILTER_SANITIZE_SPECIAL_CHARS);
            $fim_turno    = filter_input(INPUT_POST, 'fim_turno', FILTER_SANITIZE_SPECIAL_CHARS);

            if (empty($nome))
                throw new InvalidArgumentException('O nome da equipe não pode ser vazio.', 412);

            if (!$id_status || $id_status < 1)
                throw new InvalidArgumentException('O status informado não é válido.', 412);

            if (!$id_agenda || $id_agenda < 1)
                throw new InvalidArgumentException('A agenda informada não é válida.', 412);

            if (empty($inicio_turno))
                throw new InvalidArgumentException('O horário de início do turno não pode ser vazio.', 412);

            if (empty($fim_turno))
                throw new InvalidArgumentException('O horário de fim do turno não pode ser vazio.', 412);

            $equipe = new AgendaEquipe();
            $equipe->setNome($nome);
            $equipe->setId_status($id_status);
            $equipe->setDescricao($descricao);
            $equipe->setId_agenda($id_agenda);
            $equipe->setInicio_turno($inicio_turno);
            $equipe->setFim_turno($fim_turno);

            $dao = new AgendaDAO();
            $dao->incluirEquipe($equipe);

            http_response_code(200);
            echo json_encode(['msg' => 'Equipe cadastrada com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarEquipes()
    {
        header('Content-Type: application/json');

        try {
            $id_agenda = filter_input(INPUT_GET, 'id_agenda', FILTER_SANITIZE_NUMBER_INT);
            $dao = new AgendaDAO();
            echo json_encode($dao->listarEquipes($id_agenda ? (int)$id_agenda : null));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function alterarEquipe()
    {
        header('Content-Type: application/json');

        try {
            $id           = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $nome         = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_status    = filter_input(INPUT_POST, 'id_status', FILTER_SANITIZE_NUMBER_INT);
            $descricao    = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
            $id_agenda    = filter_input(INPUT_POST, 'id_agenda', FILTER_SANITIZE_NUMBER_INT);
            $inicio_turno = filter_input(INPUT_POST, 'inicio_turno', FILTER_SANITIZE_SPECIAL_CHARS);
            $fim_turno    = filter_input(INPUT_POST, 'fim_turno', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            if (empty($nome))
                throw new InvalidArgumentException('O nome da equipe não pode ser vazio.', 412);

            if (!$id_agenda || $id_agenda < 1)
                throw new InvalidArgumentException('A agenda informada não é válida.', 412);

            if (empty($inicio_turno))
                throw new InvalidArgumentException('O horário de início do turno não pode ser vazio.', 412);

            if (empty($fim_turno))
                throw new InvalidArgumentException('O horário de fim do turno não pode ser vazio.', 412);

            $equipe = new AgendaEquipe();
            $equipe->setId($id);
            $equipe->setNome($nome);
            $equipe->setId_status($id_status);
            $equipe->setDescricao($descricao);
            $equipe->setId_agenda($id_agenda);
            $equipe->setInicio_turno($inicio_turno);
            $equipe->setFim_turno($fim_turno);

            $dao = new AgendaDAO();
            $dao->alterarEquipe($equipe);

            http_response_code(200);
            echo json_encode(['msg' => 'Equipe alterada com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function excluirEquipe()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            $dao->excluirEquipe($id);

            http_response_code(200);
            echo json_encode(['msg' => 'Equipe excluída com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarEquipeStatus()
    {
        header('Content-Type: application/json');

        try {
            $dao = new AgendaDAO();
            echo json_encode($dao->listarEquipeStatus());
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    // -------------------------------------------------------
    // AGENDA EQUIPE MEMBRO
    // -------------------------------------------------------

    public function incluirMembro()
    {
        header('Content-Type: application/json');

        try {
            $id_equipe = filter_input(INPUT_POST, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);
            $id_pessoa = filter_input(INPUT_POST, 'id_pessoa', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_equipe || $id_equipe < 1)
                throw new InvalidArgumentException('A equipe informada não é válida.', 412);

            if (!$id_pessoa || $id_pessoa < 1)
                throw new InvalidArgumentException('A pessoa informada não é válida.', 412);

            $membro = new AgendaEquipeMembro();
            $membro->setId_equipe($id_equipe);
            $membro->setId_pessoa($id_pessoa);
            $membro->setAtivo(1);

            $dao = new AgendaDAO();
            $dao->incluirMembro($membro);

            http_response_code(200);
            echo json_encode(['msg' => 'Membro adicionado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarMembrosPorEquipe()
    {
        header('Content-Type: application/json');

        try {
            $id_equipe = filter_input(INPUT_GET, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_equipe || $id_equipe < 1)
                throw new InvalidArgumentException('O id da equipe informado não é válido.', 412);

            $dao = new AgendaDAO();
            echo json_encode($dao->listarMembrosPorEquipe($id_equipe));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarMembrosDeTurnoHoje()
    {
        header('Content-Type: application/json');

        try {
            $id_equipe = filter_input(INPUT_GET, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_equipe || $id_equipe < 1)
                throw new InvalidArgumentException('O id da equipe informado não é válido.', 412);

            $dao = new AgendaDAO();
            echo json_encode($dao->listarMembrosDeTurnoHoje($id_equipe));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function listarHistoricoMembrosPorEquipe()
    {
        header('Content-Type: application/json');

        try {
            $id_equipe = filter_input(INPUT_GET, 'id_equipe', FILTER_SANITIZE_NUMBER_INT);

            if (!$id_equipe || $id_equipe < 1)
                throw new InvalidArgumentException('O id da equipe informado não é válido.', 412);

            $dao = new AgendaDAO();
            echo json_encode($dao->listarHistoricoMembrosPorEquipe($id_equipe));
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function inativarMembro()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            $dao->inativarMembro($id);

            http_response_code(200);
            echo json_encode(['msg' => 'Membro inativado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function reativarMembro()
    {
        header('Content-Type: application/json');

        try {
            $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            $dao = new AgendaDAO();
            $dao->reativarMembro($id);

            http_response_code(200);
            echo json_encode(['msg' => 'Membro reativado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }

    public function alterarMembro()
    {
        header('Content-Type: application/json');

        try {
            $id                = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $inicio_turno = filter_input(INPUT_POST, 'inicio_turno', FILTER_SANITIZE_SPECIAL_CHARS);
            $fim_turno    = filter_input(INPUT_POST, 'fim_turno', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$id || $id < 1)
                throw new InvalidArgumentException('O id informado não é válido.', 412);

            if (empty($inicio_turno))
                throw new InvalidArgumentException('O horário de início do turno não pode ser vazio.', 412);

            if (empty($fim_turno))
                throw new InvalidArgumentException('O horário de fim do turno não pode ser vazio.', 412);

            if ($inicio_turno >= $fim_turno)
                throw new InvalidArgumentException('O horário de início deve ser menor que o horário de fim.', 412);

            $membro = new AgendaEquipeMembro();
            $membro->setId($id);
            $membro->setInicio_turno($inicio_turno);
            $membro->setFim_turno($fim_turno);

            $dao = new AgendaDAO();
            $dao->alterarMembro($membro);

            http_response_code(200);
            echo json_encode(['msg' => 'Membro alterado com sucesso!']);
        } catch (Exception $e) {
            Util::tratarException($e);
        }
        exit;
    }
}