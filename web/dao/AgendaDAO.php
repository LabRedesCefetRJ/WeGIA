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
require_once ROOT . "/classes/AgendaEquipeDivisao.php";

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

    public function existeAlocacaoSobreposta(int $idAgenda, int $idEquipe, string $inicio, string $fim, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM agenda_alocacao
                WHERE id_agenda = :id_agenda
                  AND id_equipe = :id_equipe
                  AND inicio   <= :fim
                  AND fim      >= :inicio";
        if ($excludeId) $sql .= " AND id != :exclude_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda', $idAgenda, PDO::PARAM_INT);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->bindValue(':inicio',    $inicio);
        $stmt->bindValue(':fim',       $fim);
        if ($excludeId) $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    public function incluirAlocacao(AgendaAlocacao $alocacao)
    {
        $sql = "INSERT INTO agenda_alocacao (id_agenda, id_equipe, inicio, fim, lembrete, lembrete_enviado, intervalo)
                VALUES (:id_agenda, :id_equipe, :inicio, :fim, :lembrete, :lembrete_enviado, :intervalo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda',        $alocacao->getId_agenda(), PDO::PARAM_INT);
        $stmt->bindValue(':id_equipe',        $alocacao->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':inicio',           $alocacao->getInicio());
        $stmt->bindValue(':fim',              $alocacao->getFim());
        $stmt->bindValue(':lembrete',         $alocacao->getLembrete());
        $stmt->bindValue(':lembrete_enviado', $alocacao->getLembrete_enviado(), PDO::PARAM_INT);
        $stmt->bindValue(':intervalo',        $alocacao->getIntervalo(), PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function listarAlocacoesPorAgenda(int $idAgenda)
    {
        $sql = "SELECT al.id, DATE(al.inicio) AS inicio_raw, DATE(al.fim) AS fim_raw,
                       al.lembrete, al.id_agenda, al.id_equipe, al.intervalo,
                       e.inicio_turno, e.fim_turno,
                       a.descricao AS agenda, e.nome AS equipe, e.nome AS title
                FROM agenda_alocacao al
                INNER JOIN agenda a ON al.id_agenda = a.id
                INNER JOIN agenda_equipe e ON al.id_equipe = e.id
                WHERE al.id_agenda = :id_agenda
                ORDER BY al.inicio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda', $idAgenda, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        foreach ($rows as $row) {
            $intervalo   = (int)($row['intervalo'] ?? 0);
            $inicioTurno = $row['inicio_turno'] ?: '00:00:00';
            $fimTurno    = $row['fim_turno']    ?: '00:00:00';
            // Quando o fim do turno é <= o início, o plantão vira o dia (ex.: 19:00 -> 07:00)
            $overnight   = ($fimTurno <= $inicioTurno);

            $step    = $intervalo > 0 ? $intervalo + 1 : 1;
            $inicio  = new DateTime($row['inicio_raw']);
            $fim     = new DateTime($row['fim_raw']);
            $current = clone $inicio;

            // Gera um evento por dia de plantão, com horário do turno da equipe.
            // O plantão noturno termina no dia seguinte, ocupando os dois dias no calendário.
            while ($current <= $fim) {
                $startDt = new DateTime($current->format('Y-m-d') . ' ' . $inicioTurno);
                $endDt   = new DateTime($current->format('Y-m-d') . ' ' . $fimTurno);
                if ($overnight) {
                    $endDt->modify('+1 day');
                }

                $events[] = [
                    'id'              => $row['id'],
                    'title'           => $row['title'],
                    'start'           => $startDt->format('Y-m-d\TH:i:s'),
                    'end'             => $endDt->format('Y-m-d\TH:i:s'),
                    'allDay'          => false,
                    'fim_display'     => $endDt->format('Y-m-d\TH:i:s'),
                    'inicio_original' => $row['inicio_raw'],
                    'fim_original'    => $row['fim_raw'],
                    'inicio_turno'    => substr($inicioTurno, 0, 5),
                    'fim_turno'       => substr($fimTurno, 0, 5),
                    'overnight'       => $overnight,
                    'lembrete'        => $row['lembrete'],
                    'id_agenda'       => $row['id_agenda'],
                    'id_equipe'       => $row['id_equipe'],
                    'agenda'          => $row['agenda'],
                    'equipe'          => $row['equipe'],
                    'intervalo'       => $intervalo,
                ];

                $current->modify('+' . $step . ' days');
            }
        }
        return $events;
    }

    public function alterarAlocacao(AgendaAlocacao $alocacao)
    {
        $sql = "UPDATE agenda_alocacao SET
                    id_agenda        = :id_agenda,
                    id_equipe        = :id_equipe,
                    inicio           = :inicio,
                    fim              = :fim,
                    lembrete         = :lembrete,
                    lembrete_enviado = :lembrete_enviado,
                    intervalo        = :intervalo
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_agenda',        $alocacao->getId_agenda(), PDO::PARAM_INT);
        $stmt->bindValue(':id_equipe',        $alocacao->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':inicio',           $alocacao->getInicio());
        $stmt->bindValue(':fim',              $alocacao->getFim());
        $stmt->bindValue(':lembrete',         $alocacao->getLembrete());
        $stmt->bindValue(':lembrete_enviado', $alocacao->getLembrete_enviado(), PDO::PARAM_INT);
        $stmt->bindValue(':intervalo',        $alocacao->getIntervalo(), PDO::PARAM_INT);
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
        $sql = "SELECT al.id, DATE(al.inicio) AS start, DATE(al.fim) AS end, DATE(al.fim) AS fim_display,
                       al.lembrete, al.id_agenda, al.id_equipe, al.intervalo,
                       e.inicio_turno, e.fim_turno,
                       a.descricao AS agenda, e.nome AS equipe, e.nome AS title
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
        $sqlMembros = "DELETE FROM agenda_equipe_membro WHERE id_equipe = :id";
        $stmtMembros = $this->pdo->prepare($sqlMembros);
        $stmtMembros->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtMembros->execute();

        $sqlDivisao = "DELETE FROM agenda_equipe_divisao WHERE id_equipe = :id";
        $stmtDivisao = $this->pdo->prepare($sqlDivisao);
        $stmtDivisao->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtDivisao->execute();

        $sqlAlocacoes = "DELETE FROM agenda_alocacao WHERE id_equipe = :id";
        $stmtAlocacoes = $this->pdo->prepare($sqlAlocacoes);
        $stmtAlocacoes->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtAlocacoes->execute();

        $sqlEquipe = "DELETE FROM agenda_equipe WHERE id = :id";
        $stmtEquipe = $this->pdo->prepare($sqlEquipe);
        $stmtEquipe->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtEquipe->execute();
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
        $sql = "INSERT INTO agenda_equipe_membro (id_equipe, id_divisao, id_pessoa, ativo)
                VALUES (:id_equipe, :id_divisao, :id_pessoa, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe',  $membro->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':id_divisao', $membro->getId_divisao(), PDO::PARAM_INT);
        $stmt->bindValue(':id_pessoa',  $membro->getId_pessoa(), PDO::PARAM_INT);
        $stmt->bindValue(':ativo',      1, PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    // Lista apenas membros ativos da equipe
    public function listarMembrosPorEquipe(int $idEquipe)
    {
        $sql = "SELECT m.id, m.id_divisao, p.nome, p.sobrenome, m.ativo,
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
        $sql = "SELECT m.id, m.id_equipe, d.nome AS nome_divisao, e.inicio_turno, e.fim_turno,
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
                LEFT JOIN agenda_equipe_divisao d ON m.id_divisao = d.id
                LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
                LEFT JOIN cargo c ON c.id_cargo = f.id_cargo
                LEFT JOIN voluntario v ON v.id_pessoa = p.id_pessoa
                LEFT JOIN atendido a ON a.pessoa_id_pessoa = p.id_pessoa
                WHERE m.ativo = 1
                ORDER BY m.id_equipe, d.nome, p.nome";

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

    public function listarPessoas(?int $idEquipe = null)
    {
        $sql = "SELECT p.id_pessoa,
                       CONCAT(p.nome, ' ', p.sobrenome) AS nome_completo,
                       CASE
                           WHEN f.id_pessoa IS NOT NULL THEN COALESCE(c.cargo, 'Funcionário')
                           WHEN v.id_pessoa IS NOT NULL THEN 'Voluntário'
                           WHEN a.pessoa_id_pessoa IS NOT NULL THEN 'Atendido'
                           ELSE NULL
                       END AS cargo
                FROM pessoa p
                LEFT JOIN funcionario f ON f.id_pessoa = p.id_pessoa
                LEFT JOIN cargo c ON c.id_cargo = f.id_cargo
                LEFT JOIN voluntario v ON v.id_pessoa = p.id_pessoa
                LEFT JOIN atendido a ON a.pessoa_id_pessoa = p.id_pessoa
                WHERE p.nome IS NOT NULL";
        if ($idEquipe) {
            $sql .= " AND p.id_pessoa NOT IN (
                          SELECT id_pessoa FROM agenda_equipe_membro
                          WHERE id_equipe = :id_equipe AND ativo = 1
                      )";
        }
        $sql .= " ORDER BY p.nome, p.sobrenome";
        $stmt = $this->pdo->prepare($sql);
        if ($idEquipe) $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obterLogo()
    {
        $sql  = "SELECT `imagem`, `tipo` FROM `imagem` WHERE `id_imagem` = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // -------------------------------------------------------
    // AGENDA EQUIPE DIVISAO
    // -------------------------------------------------------

    public function incluirDivisao(AgendaEquipeDivisao $divisao)
    {
        $sql = "INSERT INTO agenda_equipe_divisao (id_equipe, nome, ativo)
                VALUES (:id_equipe, :nome, :ativo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $divisao->getId_equipe(), PDO::PARAM_INT);
        $stmt->bindValue(':nome',      $divisao->getNome());
        $stmt->bindValue(':ativo',     1, PDO::PARAM_INT);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function listarDivisoesPorEquipe(int $idEquipe)
    {
        $sql = "SELECT d.id, d.nome, d.ativo
                FROM agenda_equipe_divisao d
                WHERE d.id_equipe = :id_equipe
                ORDER BY d.nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_equipe', $idEquipe, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarDivisaoPorId(int $id)
    {
        $sql = "SELECT d.id, d.id_equipe, d.nome, d.ativo
                FROM agenda_equipe_divisao d
                WHERE d.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarMembrosPorDivisao(int $idDivisao)
    {
        $sql = "SELECT m.id, p.nome, p.sobrenome, m.ativo
                FROM agenda_equipe_membro m
                INNER JOIN pessoa p ON m.id_pessoa = p.id_pessoa
                WHERE m.id_divisao = :id_divisao
                AND m.ativo = 1
                ORDER BY p.nome";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_divisao', $idDivisao, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function alterarDivisao(AgendaEquipeDivisao $divisao)
    {
        $sql = "UPDATE agenda_equipe_divisao SET
                    nome  = :nome,
                    ativo = :ativo
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome',  $divisao->getNome());
        $stmt->bindValue(':ativo', $divisao->getAtivo(), PDO::PARAM_INT);
        $stmt->bindValue(':id',    $divisao->getId(), PDO::PARAM_INT);
        $stmt->execute();
    }

    public function excluirDivisao(int $id)
    {
        // Desvincula membros da divisão antes de excluir
        $sql = "UPDATE agenda_equipe_membro SET id_divisao = NULL WHERE id_divisao = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $sql = "DELETE FROM agenda_equipe_divisao WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function atribuirDivisaoMembro(int $idMembro, ?int $idDivisao)
    {
        $sql = "UPDATE agenda_equipe_membro SET id_divisao = :id_divisao WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_divisao', $idDivisao, PDO::PARAM_INT);
        $stmt->bindValue(':id',         $idMembro,  PDO::PARAM_INT);
        $stmt->execute();
    }
}