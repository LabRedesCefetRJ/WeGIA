<?php

$config_path = "config.php";
if (file_exists($config_path)) {
    require_once($config_path);
} else {
    while (true) {
        $config_path = "../" . $config_path;
        if (file_exists($config_path)) break;
    }
    require_once($config_path);
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
        $sql = "SELECT al.id, al.inicio, al.fim, al.lembrete, al.lembrete_enviado,
                       e.nome as equipe
                FROM agenda_alocacao al
                INNER JOIN agenda_equipe e ON al.id_equipe = e.id
                WHERE al.id_agenda = :id_agenda";
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

    // -------------------------------------------------------
    // AGENDA EQUIPE
    // -------------------------------------------------------

    public function incluirEquipe(AgendaEquipe $equipe)
    {
        $sql = "INSERT INTO agenda_equipe (nome, id_status, descricao)
                VALUES (:nome, :id_status, :descricao)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome',      $equipe->getNome());
        $stmt->bindValue(':id_status', $equipe->getId_status(), PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $equipe->getDescricao());
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function listarEquipes()
    {
        $sql = "SELECT e.id, e.nome, e.descricao, s.descricao as status
                FROM agenda_equipe e
                INNER JOIN agenda_equipe_status s ON e.id_status = s.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEquipePorId(int $id)
    {
        $sql = "SELECT e.id, e.nome, e.descricao, s.descricao as status
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
                    nome      = :nome,
                    id_status = :id_status,
                    descricao = :descricao
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome',      $equipe->getNome());
        $stmt->bindValue(':id_status', $equipe->getId_status(), PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $equipe->getDescricao());
        $stmt->bindValue(':id',        $equipe->getId(), PDO::PARAM_INT);
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
        $sql = "INSERT INTO agenda_equipe_membro (id_equipe, id_pessoa, data_inicio_plantao, data_fim_plantao, ativo)
                VALUES (:id_equipe, :id_pessoa, :data_inicio_plantao, :data_fim_plantao, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe',          $membro->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':id_pessoa',          $membro->getId_pessoa(), PDO::PARAM_INT);
        $stmt->bindValue(':data_inicio_plantao',$membro->getData_inicio_plantao());
        $stmt->bindValue(':data_fim_plantao',   $membro->getData_fim_plantao());
        $stmt->bindValue(':ativo',              1, PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    // Lista apenas membros ativos da equipe
    public function listarMembrosPorEquipe(int $idEquipe)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, m.data_inicio_plantao, m.data_fim_plantao, m.ativo
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                WHERE m.id_equipe = :id_equipe
                AND m.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista membros de plantão hoje
    public function listarMembrosDePlantaoHoje(int $idEquipe)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, m.data_inicio_plantao, m.data_fim_plantao
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                WHERE m.id_equipe = :id_equipe
                AND m.ativo = 1
                AND m.data_inicio_plantao <= NOW()
                AND m.data_fim_plantao >= NOW()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lista histórico completo
    public function listarHistoricoMembrosPorEquipe(int $idEquipe)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, m.data_inicio_plantao, m.data_fim_plantao, m.ativo
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
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

    public function alterarMembro(AgendaEquipeMembro $membro)
    {
        $sql = "UPDATE agenda_equipe_membro SET
                    data_inicio_plantao = :data_inicio_plantao,
                    data_fim_plantao    = :data_fim_plantao
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':data_inicio_plantao', $membro->getData_inicio_plantao());
        $stmt->bindValue(':data_fim_plantao',    $membro->getData_fim_plantao());
        $stmt->bindValue(':id',                  $membro->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }
}