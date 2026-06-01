<?php

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once $config_path;
}
require_once ROOT . "/dao/Conexao.php";
require_once ROOT . "/classes/Agenda.php";
require_once ROOT . "/classes/AgendaAlocacao.php";
require_once ROOT . "/classes/AgendaEquipe.php";
require_once ROOT . "/classes/AgendaEquipeMembro.php";

class AgendaDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        isset($pdo) ? $this->pdo = $pdo : $this->pdo = Conexao::connect();
    }

    // -------------------------------------------------------
    // AGENDA
    // -------------------------------------------------------

    public function incluirAgenda(Agenda $agenda)
    {
        $sql = "INSERT INTO agenda (descricao, id_status)
                VALUES (:descricao, :id_status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':descricao', $agenda->getDescricao());
        $stmt->bindValue(':id_status', $agenda->getId_status(), PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function listarAgendas()
    {
        $sql = "SELECT a.id, a.descricao, s.descricao as status
                FROM agenda a
                INNER JOIN agenda_status s ON a.id_status = s.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAgendaPorId(int $id)
    {
        $sql = "SELECT a.id, a.descricao, s.descricao as status
                FROM agenda a
                INNER JOIN agenda_status s ON a.id_status = s.id
                WHERE a.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function alterarAgenda(Agenda $agenda)
    {
        $sql = "UPDATE agenda SET
                    descricao = :descricao,
                    id_status = :id_status
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':descricao', $agenda->getDescricao());
        $stmt->bindValue(':id_status', $agenda->getId_status(), PDO::PARAM_INT);
        $stmt->bindValue(':id',        $agenda->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function excluirAgenda(int $id)
    {
        $sql = "DELETE FROM agenda WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listarStatus()
    {
        $sql = "SELECT id, descricao FROM agenda_status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // AGENDA ALOCACAO
    // -------------------------------------------------------

    public function incluirAlocacao(AgendaAlocacao $alocacao)
    {
        $sql = "INSERT INTO agenda_alocacao (id_agenda, id_equipe, inicio, fim, lembrete, lembrete_enviado)
                VALUES (:id_agenda, :id_equipe, :inicio, :fim, :lembrete, :lembrete_enviado)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda',        $alocacao->getId_agenda(), PDO::PARAM_INT);
        $stmt->bindValue(':id_equipe',        $alocacao->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':inicio',           $alocacao->getInicio());
        $stmt->bindValue(':fim',              $alocacao->getFim());
        $stmt->bindValue(':lembrete',         $alocacao->getLembrete());
        $stmt->bindValue(':lembrete_enviado', $alocacao->getLembrete_enviado(), PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function listarAlocacoesPorAgenda(int $idAgenda)
    {
        $sql = "SELECT al.id, DATE(al.inicio) AS start, DATE(al.fim) AS end, DATE(al.fim) AS fim_display, al.lembrete,
                       al.id_agenda, al.id_equipe,
                       a.descricao AS agenda, e.nome AS equipe,
                       e.nome AS title
                FROM agenda_alocacao al
                INNER JOIN agenda a ON al.id_agenda = a.id
                INNER JOIN agenda_equipe e ON al.id_equipe = e.id
                WHERE al.id_agenda = :id_agenda
                ORDER BY al.inicio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda', $idAgenda, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function alterarAlocacao(AgendaAlocacao $alocacao)
    {
        $sql = "UPDATE agenda_alocacao SET
                    id_agenda        = :id_agenda,
                    id_equipe        = :id_equipe,
                    inicio           = :inicio,
                    fim              = :fim,
                    lembrete         = :lembrete,
                    lembrete_enviado = :lembrete_enviado
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda',        $alocacao->getId_agenda(), PDO::PARAM_INT);
        $stmt->bindValue(':id_equipe',        $alocacao->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':inicio',           $alocacao->getInicio());
        $stmt->bindValue(':fim',              $alocacao->getFim());
        $stmt->bindValue(':lembrete',         $alocacao->getLembrete());
        $stmt->bindValue(':lembrete_enviado', $alocacao->getLembrete_enviado(), PDO::PARAM_INT);
        $stmt->bindValue(':id',               $alocacao->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function marcarLembreteEnviado(int $idAlocacao)
    {
        $sql = "UPDATE agenda_alocacao SET lembrete_enviado = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idAlocacao, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listarTodasAlocacoes()
    {
        $sql = "SELECT al.id, DATE(al.inicio) AS start, DATE(al.fim) AS end, DATE(al.fim) AS fim_display, al.lembrete,
                       al.id_agenda, al.id_equipe,
                       a.descricao AS agenda, e.nome AS equipe,
                       e.nome AS title
                FROM agenda_alocacao al
                INNER JOIN agenda a ON al.id_agenda = a.id
                INNER JOIN agenda_equipe e ON al.id_equipe = e.id
                ORDER BY al.inicio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAlocacaoPorId(int $id): array
    {
        $sql = "SELECT al.id, DATE(al.inicio) AS inicio, DATE(al.fim) AS fim,
                       a.descricao AS agenda, e.nome AS equipe,
                       e.inicio_turno, e.fim_turno
                FROM agenda_alocacao al
                INNER JOIN agenda a ON al.id_agenda = a.id
                INNER JOIN agenda_equipe e ON al.id_equipe = e.id
                WHERE al.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    // -------------------------------------------------------
    // AGENDA EQUIPE
    // -------------------------------------------------------

    public function incluirEquipe(AgendaEquipe $equipe)
    {
        $sql = "INSERT INTO agenda_equipe (nome, id_status, descricao, id_agenda, inicio_turno, fim_turno)
                VALUES (:nome, :id_status, :descricao, :id_agenda, :inicio_turno, :fim_turno)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome',         $equipe->getNome());
        $stmt->bindValue(':id_status',    $equipe->getId_status(), PDO::PARAM_INT);
        $stmt->bindValue(':descricao',    $equipe->getDescricao());
        $stmt->bindValue(':id_agenda',    $equipe->getId_agenda(), PDO::PARAM_INT);
        $stmt->bindValue(':inicio_turno', $equipe->getInicio_turno());
        $stmt->bindValue(':fim_turno',    $equipe->getFim_turno());
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function listarEquipes(?int $idAgenda = null)
    {
        $sql = "SELECT e.id, e.nome, e.descricao, e.id_agenda, e.id_status,
                       e.inicio_turno, e.fim_turno,
                       s.descricao as status, a.descricao as agenda_descricao
                FROM agenda_equipe e
                INNER JOIN agenda_equipe_status s ON e.id_status = s.id
                INNER JOIN agenda a ON e.id_agenda = a.id";
        if ($idAgenda !== null) {
            $sql .= " WHERE e.id_agenda = :id_agenda";
        }
        $stmt = $this->pdo->prepare($sql);
        if ($idAgenda !== null) {
            $stmt->bindValue(':id_agenda', $idAgenda, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEquipePorId(int $id)
    {
        $sql = "SELECT e.id, e.nome, e.descricao, e.id_status, e.id_agenda,
                       e.inicio_turno, e.fim_turno, s.descricao as status
                FROM agenda_equipe e
                INNER JOIN agenda_equipe_status s ON e.id_status = s.id
                WHERE e.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function alterarEquipe(AgendaEquipe $equipe)
    {
        $sql = "UPDATE agenda_equipe SET
                    nome         = :nome,
                    id_status    = :id_status,
                    descricao    = :descricao,
                    id_agenda    = :id_agenda,
                    inicio_turno = :inicio_turno,
                    fim_turno    = :fim_turno
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome',         $equipe->getNome());
        $stmt->bindValue(':id_status',    $equipe->getId_status(), PDO::PARAM_INT);
        $stmt->bindValue(':descricao',    $equipe->getDescricao());
        $stmt->bindValue(':id_agenda',    $equipe->getId_agenda(), PDO::PARAM_INT);
        $stmt->bindValue(':inicio_turno', $equipe->getInicio_turno());
        $stmt->bindValue(':fim_turno',    $equipe->getFim_turno());
        $stmt->bindValue(':id',           $equipe->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function excluirEquipe(int $id)
    {
        $sql = "DELETE FROM agenda_equipe WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listarEquipeStatus()
    {
        $sql = "SELECT id, descricao FROM agenda_equipe_status";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function incluirMembro(AgendaEquipeMembro $membro)
    {
        $sql = "INSERT INTO agenda_equipe_membro (id_equipe, id_pessoa, ativo)
                VALUES (:id_equipe, :id_pessoa, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $membro->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':id_pessoa', $membro->getId_pessoa(), PDO::PARAM_INT);
        $stmt->bindValue(':ativo',     1, PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    // Lista apenas membros ativos da equipe
    public function listarMembrosPorEquipe(int $idEquipe)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, m.ativo,
                       e.inicio_turno, e.fim_turno,
                       CASE
                           WHEN f.id_pessoa IS NOT NULL THEN COALESCE(c.cargo, 'Funcionário')
                           WHEN v.id_pessoa IS NOT NULL THEN 'Voluntário'
                           WHEN a.pessoa_id_pessoa IS NOT NULL THEN 'Atendido'
                           ELSE NULL
                       END AS cargo
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                INNER JOIN agenda_equipe e ON m.id_equipe = e.id
                LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
                LEFT JOIN cargo c ON c.id_cargo = f.id_cargo
                LEFT JOIN voluntario v ON v.id_pessoa = p.id_pessoa
                LEFT JOIN atendido a ON a.pessoa_id_pessoa = p.id_pessoa
                WHERE m.id_equipe = :id_equipe
                AND m.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista membros do turno de hoje (horário vem da equipe)
    public function listarMembrosDeTurnoHoje(int $idEquipe)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, e.inicio_turno, e.fim_turno
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                INNER JOIN agenda_equipe e ON m.id_equipe = e.id
                WHERE m.id_equipe = :id_equipe
                AND m.ativo = 1
                AND e.inicio_turno <= CURTIME()
                AND (e.fim_turno IS NULL OR e.fim_turno >= CURTIME())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista histórico completo
    public function listarHistoricoMembrosPorEquipe(int $idEquipe)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, e.inicio_turno, e.fim_turno, m.ativo
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                INNER JOIN agenda_equipe e ON m.id_equipe = e.id
                WHERE m.id_equipe = :id_equipe";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Inativa membro
    public function inativarMembro(int $id)
    {
        $sql = "UPDATE agenda_equipe_membro SET ativo = 0 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Reativa membro
    public function reativarMembro(int $id)
    {
        $sql = "UPDATE agenda_equipe_membro SET ativo = 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listarTodosMembrosAtivos()
    {
        $sql = "SELECT m.id, m.id_equipe, e.inicio_turno, e.fim_turno,
                       CONCAT(p.nome, ' ', COALESCE(p.sobrenome, '')) AS nome_completo,
                       CASE
                           WHEN f.id_pessoa IS NOT NULL THEN COALESCE(c.cargo, 'Funcionário')
                           WHEN v.id_pessoa IS NOT NULL THEN 'Voluntário'
                           WHEN a.pessoa_id_pessoa IS NOT NULL THEN 'Atendido'
                           ELSE NULL
                       END AS cargo
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                INNER JOIN agenda_equipe e ON m.id_equipe = e.id
                LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
                LEFT JOIN cargo c ON c.id_cargo = f.id_cargo
                LEFT JOIN voluntario v ON v.id_pessoa = p.id_pessoa
                LEFT JOIN atendido a ON a.pessoa_id_pessoa = p.id_pessoa
                WHERE m.ativo = 1
                ORDER BY m.id_equipe, p.nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluirMembro(int $id)
    {
        $sql = "DELETE FROM agenda_equipe_membro WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function excluirAlocacao(int $id)
    {
        $sql = "DELETE FROM agenda_alocacao WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function salvarLembrete(int $id, ?string $lembrete)
    {
        $sql = "UPDATE agenda_alocacao SET lembrete = :lembrete WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':lembrete', $lembrete);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listarPessoas()
    {
        $sql = "SELECT id_pessoa, CONCAT(nome, ' ', sobrenome) AS nome_completo
                FROM pessoa
                WHERE nome IS NOT NULL
                ORDER BY nome, sobrenome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}