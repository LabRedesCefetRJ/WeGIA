<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudeEscalaMensal.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudeLogEquipePlantao.php';

class SaudeEquipePlantaoDAO
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
    }

    public function listarTecnicosEnfermagem(?string $filtro = null): array
    {
        $params = [];
        $whereFiltro = '';

        if (!is_null($filtro) && trim($filtro) !== '') {
            $whereFiltro = ' AND (p.nome LIKE :filtro OR p.sobrenome LIKE :filtro OR c.cargo LIKE :filtro)';
            $params[':filtro'] = '%' . trim($filtro) . '%';
        }

        $sql = "SELECT
                    f.id_funcionario,
                    f.id_pessoa,
                    p.nome,
                    p.sobrenome,
                    c.cargo,
                    CONCAT(TRIM(p.nome), ' ', TRIM(COALESCE(p.sobrenome, ''))) AS nome_completo
                FROM funcionario f
                INNER JOIN pessoa p ON p.id_pessoa = f.id_pessoa
                INNER JOIN cargo c ON c.id_cargo = f.id_cargo
                WHERE f.id_situacao = 1
                  AND (
                    LOWER(c.cargo) LIKE '%enferm%'
                    OR LOWER(c.cargo) LIKE '%tecnic%'
                    OR LOWER(c.cargo) LIKE '%técnic%'
                  )
                  {$whereFiltro}
                ORDER BY p.nome ASC, p.sobrenome ASC";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }

        $stmt->execute();
        $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($tecnicos)) {
            return $tecnicos;
        }

        $sqlFallback = "SELECT
                            f.id_funcionario,
                            f.id_pessoa,
                            p.nome,
                            p.sobrenome,
                            c.cargo,
                            CONCAT(TRIM(p.nome), ' ', TRIM(COALESCE(p.sobrenome, ''))) AS nome_completo
                        FROM funcionario f
                        INNER JOIN pessoa p ON p.id_pessoa = f.id_pessoa
                        INNER JOIN cargo c ON c.id_cargo = f.id_cargo
                        INNER JOIN permissao pe ON pe.id_cargo = c.id_cargo
                        WHERE f.id_situacao = 1
                          AND pe.id_recurso = 5
                          AND pe.id_acao >= 5
                          {$whereFiltro}
                        GROUP BY f.id_funcionario, f.id_pessoa, p.nome, p.sobrenome, c.cargo
                        ORDER BY p.nome ASC, p.sobrenome ASC";

        $stmt = $this->pdo->prepare($sqlFallback);

        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarEquipes(?bool $somenteAtivas = null): array
    {
        $where = '';

        if (!is_null($somenteAtivas)) {
            $where = 'WHERE sep.ativo = :ativo';
        }

        $sql = "SELECT
                    sep.id_equipe_plantao,
                    sep.nome,
                    sep.descricao,
                    sep.ativo,
                    sep.data_criacao,
                    sep.data_atualizacao,
                    COUNT(sem.id_equipe_membro) AS quantidade_membros
                FROM saude_equipe_plantao sep
                LEFT JOIN saude_equipe_membro sem ON sem.id_equipe_plantao = sep.id_equipe_plantao
                {$where}
                GROUP BY
                    sep.id_equipe_plantao,
                    sep.nome,
                    sep.descricao,
                    sep.ativo,
                    sep.data_criacao,
                    sep.data_atualizacao
                ORDER BY sep.nome ASC";

        $stmt = $this->pdo->prepare($sql);

        if (!is_null($somenteAtivas)) {
            $stmt->bindValue(':ativo', $somenteAtivas ? 1 : 0, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarEquipePorId(int $idEquipePlantao): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM saude_equipe_plantao WHERE id_equipe_plantao = :id');
        $stmt->bindValue(':id', $idEquipePlantao, PDO::PARAM_INT);
        $stmt->execute();

        $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$equipe) {
            return null;
        }

        $equipe['membros'] = $this->listarMembrosFixosDaEquipe($idEquipePlantao);
        return $equipe;
    }

    public function listarMembrosFixosDaEquipe(int $idEquipePlantao): array
    {
        $sql = "SELECT
                    sem.id_equipe_membro,
                    sem.id_funcionario,
                    p.id_pessoa,
                    p.nome,
                    p.sobrenome,
                    c.cargo,
                    CONCAT(TRIM(p.nome), ' ', TRIM(COALESCE(p.sobrenome, ''))) AS nome_completo
                FROM saude_equipe_membro sem
                INNER JOIN funcionario f ON f.id_funcionario = sem.id_funcionario
                INNER JOIN pessoa p ON p.id_pessoa = f.id_pessoa
                INNER JOIN cargo c ON c.id_cargo = f.id_cargo
                WHERE sem.id_equipe_plantao = :idEquipePlantao
                ORDER BY p.nome ASC, p.sobrenome ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvarEquipe(SaudeEquipePlantao $equipe, array $idsFuncionarios, int $idUsuario): int
    {
        $idsFuncionarios = $this->sanitizarListaIds($idsFuncionarios);

        $this->pdo->beginTransaction();

        try {
            $idEquipePlantao = $equipe->getIdEquipePlantao();

            if (is_null($idEquipePlantao)) {
                $sqlInserirEquipe = 'INSERT INTO saude_equipe_plantao (nome, descricao, ativo, id_usuario_criacao, data_criacao, id_usuario_atualizacao, data_atualizacao)
                                    VALUES (:nome, :descricao, :ativo, :idUsuarioCriacao, :dataCriacao, :idUsuarioAtualizacao, :dataAtualizacao)';

                $stmt = $this->pdo->prepare($sqlInserirEquipe);
                $stmt->bindValue(':nome', $equipe->getNome());
                $stmt->bindValue(':descricao', $equipe->getDescricao());
                $stmt->bindValue(':ativo', $equipe->isAtivo() ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':idUsuarioCriacao', $idUsuario, PDO::PARAM_INT);
                $stmt->bindValue(':dataCriacao', $this->agora());
                $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
                $stmt->bindValue(':dataAtualizacao', $this->agora());
                $stmt->execute();

                $idEquipePlantao = (int) $this->pdo->lastInsertId();
            } else {
                $sqlAtualizarEquipe = 'UPDATE saude_equipe_plantao
                                      SET nome = :nome,
                                          descricao = :descricao,
                                          ativo = :ativo,
                                          id_usuario_atualizacao = :idUsuarioAtualizacao,
                                          data_atualizacao = :dataAtualizacao
                                      WHERE id_equipe_plantao = :idEquipePlantao';

                $stmt = $this->pdo->prepare($sqlAtualizarEquipe);
                $stmt->bindValue(':nome', $equipe->getNome());
                $stmt->bindValue(':descricao', $equipe->getDescricao());
                $stmt->bindValue(':ativo', $equipe->isAtivo() ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
                $stmt->bindValue(':dataAtualizacao', $this->agora());
                $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->sincronizarMembrosEquipe($idEquipePlantao, $idsFuncionarios, $idUsuario);

            $this->pdo->commit();
            return $idEquipePlantao;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function sincronizarMembrosEquipe(int $idEquipePlantao, array $idsFuncionarios, int $idUsuario): void
    {
        $stmtExistentes = $this->pdo->prepare('SELECT id_funcionario FROM saude_equipe_membro WHERE id_equipe_plantao = :idEquipePlantao');
        $stmtExistentes->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
        $stmtExistentes->execute();

        $existentes = array_map('intval', array_column($stmtExistentes->fetchAll(PDO::FETCH_ASSOC), 'id_funcionario'));

        $inserir = array_diff($idsFuncionarios, $existentes);
        $remover = array_diff($existentes, $idsFuncionarios);

        if (!empty($inserir)) {
            $sqlInserirMembro = 'INSERT INTO saude_equipe_membro (id_equipe_plantao, id_funcionario, id_usuario_criacao, data_criacao)
                                VALUES (:idEquipePlantao, :idFuncionario, :idUsuarioCriacao, :dataCriacao)';
            $stmtInserir = $this->pdo->prepare($sqlInserirMembro);

            foreach ($inserir as $idFuncionario) {
                $stmtInserir->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
                $stmtInserir->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
                $stmtInserir->bindValue(':idUsuarioCriacao', $idUsuario, PDO::PARAM_INT);
                $stmtInserir->bindValue(':dataCriacao', $this->agora());
                $stmtInserir->execute();
            }
        }

        if (!empty($remover)) {
            $placeholders = implode(',', array_fill(0, count($remover), '?'));
            $sqlRemover = "DELETE FROM saude_equipe_membro WHERE id_equipe_plantao = ? AND id_funcionario IN ({$placeholders})";
            $stmtRemover = $this->pdo->prepare($sqlRemover);

            $stmtRemover->bindValue(1, $idEquipePlantao, PDO::PARAM_INT);

            $indice = 2;
            foreach ($remover as $idFuncionario) {
                $stmtRemover->bindValue($indice, $idFuncionario, PDO::PARAM_INT);
                $indice++;
            }

            $stmtRemover->execute();
        }
    }

    public function alterarStatusEquipe(int $idEquipePlantao, bool $ativo, int $idUsuario): bool
    {
        $sql = 'UPDATE saude_equipe_plantao
                SET ativo = :ativo,
                    id_usuario_atualizacao = :idUsuarioAtualizacao,
                    data_atualizacao = :dataAtualizacao
                WHERE id_equipe_plantao = :idEquipePlantao';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ativo', $ativo ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':dataAtualizacao', $this->agora());
        $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function obterEscalaMensal(int $ano, int $mes): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM saude_escala_mensal WHERE ano = :ano AND mes = :mes LIMIT 1');
        $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->execute();

        $escala = $stmt->fetch(PDO::FETCH_ASSOC);
        return $escala ?: null;
    }

    public function alterarBloqueioEscalaMensal(int $idEscalaMensal, bool $bloqueada, int $idUsuario): bool
    {
        $sql = 'UPDATE saude_escala_mensal
                SET bloqueada = :bloqueada,
                    id_usuario_atualizacao = :idUsuarioAtualizacao,
                    data_atualizacao = :dataAtualizacao
                WHERE id_escala_mensal = :idEscalaMensal';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':bloqueada', $bloqueada ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':dataAtualizacao', $this->agora());
        $stmt->bindValue(':idEscalaMensal', $idEscalaMensal, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function obterOuCriarEscalaMensal(SaudeEscalaMensal $escala, int $idUsuario): int
    {
        $existente = $this->obterEscalaMensal($escala->getAno(), $escala->getMes());

        if ($existente) {
            $sqlAtualizaEscala = 'UPDATE saude_escala_mensal
                                 SET observacao = :observacao,
                                     id_usuario_atualizacao = :idUsuarioAtualizacao,
                                     data_atualizacao = :dataAtualizacao
                                 WHERE id_escala_mensal = :idEscalaMensal';
            $stmt = $this->pdo->prepare($sqlAtualizaEscala);
            $stmt->bindValue(':observacao', $escala->getObservacao());
            $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':dataAtualizacao', $this->agora());
            $stmt->bindValue(':idEscalaMensal', $existente['id_escala_mensal'], PDO::PARAM_INT);
            $stmt->execute();

            return (int) $existente['id_escala_mensal'];
        }

        $sql = 'INSERT INTO saude_escala_mensal (ano, mes, observacao, bloqueada, id_usuario_criacao, data_criacao, id_usuario_atualizacao, data_atualizacao)
                VALUES (:ano, :mes, :observacao, :bloqueada, :idUsuarioCriacao, :dataCriacao, :idUsuarioAtualizacao, :dataAtualizacao)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ano', $escala->getAno(), PDO::PARAM_INT);
        $stmt->bindValue(':mes', $escala->getMes(), PDO::PARAM_INT);
        $stmt->bindValue(':observacao', $escala->getObservacao());
        $stmt->bindValue(':bloqueada', 0, PDO::PARAM_INT);
        $stmt->bindValue(':idUsuarioCriacao', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':dataCriacao', $this->agora());
        $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':dataAtualizacao', $this->agora());
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function listarDiasEscalaPorAnoMes(int $ano, int $mes): array
    {
        $sql = "SELECT
                    sed.id_escala_dia,
                    sed.id_escala_mensal,
                    sed.dia,
                    sed.turno,
                    sed.id_equipe_plantao,
                    sed.observacao,
                    sep.nome AS equipe_nome,
                    sep.ativo AS equipe_ativa,
                    sem.ano,
                    sem.mes
                FROM saude_escala_mensal sem
                LEFT JOIN saude_escala_dia sed ON sed.id_escala_mensal = sem.id_escala_mensal
                LEFT JOIN saude_equipe_plantao sep ON sep.id_equipe_plantao = sed.id_equipe_plantao
                WHERE sem.ano = :ano AND sem.mes = :mes
                ORDER BY sed.dia ASC, FIELD(sed.turno, 'DIA', 'NOITE') ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarDiasEscalaPorIdEscalaMensal(int $idEscalaMensal): array
    {
        $sql = "SELECT
                    sed.id_escala_dia,
                    sed.id_escala_mensal,
                    sed.dia,
                    sed.turno,
                    sed.id_equipe_plantao,
                    sed.observacao,
                    sep.nome AS equipe_nome,
                    sep.ativo AS equipe_ativa
                FROM saude_escala_dia sed
                INNER JOIN saude_equipe_plantao sep ON sep.id_equipe_plantao = sed.id_equipe_plantao
                WHERE sed.id_escala_mensal = :idEscalaMensal
                ORDER BY sed.dia ASC, FIELD(sed.turno, 'DIA', 'NOITE') ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEscalaMensal', $idEscalaMensal, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterEscalaDia(int $idEscalaMensal, int $dia, ?string $turno = null): ?array
    {
        $whereTurno = '';
        $turnoNormalizado = null;

        if (!is_null($turno)) {
            $turnoNormalizado = $this->normalizarTurno($turno);
            $whereTurno = ' AND sed.turno = :turno ';
        }

        $sql = "SELECT
                    sed.*,
                    sep.nome AS equipe_nome,
                    sep.ativo AS equipe_ativa,
                    sem.ano,
                    sem.mes
                FROM saude_escala_dia sed
                INNER JOIN saude_equipe_plantao sep ON sep.id_equipe_plantao = sed.id_equipe_plantao
                INNER JOIN saude_escala_mensal sem ON sem.id_escala_mensal = sed.id_escala_mensal
                WHERE sed.id_escala_mensal = :idEscalaMensal
                  AND sed.dia = :dia
                  {$whereTurno}
                ORDER BY FIELD(sed.turno, 'DIA', 'NOITE') ASC
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEscalaMensal', $idEscalaMensal, PDO::PARAM_INT);
        $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
        if (!is_null($turnoNormalizado)) {
            $stmt->bindValue(':turno', $turnoNormalizado);
        }
        $stmt->execute();

        $escalaDia = $stmt->fetch(PDO::FETCH_ASSOC);
        return $escalaDia ?: null;
    }

    public function upsertEscalaDia(int $idEscalaMensal, int $dia, string $turno, int $idEquipePlantao, int $idUsuario, ?string $observacao = null): int
    {
        $turno = $this->normalizarTurno($turno);
        $existente = $this->obterEscalaDia($idEscalaMensal, $dia, $turno);

        if ($existente) {
            $sqlAtualiza = 'UPDATE saude_escala_dia
                            SET id_equipe_plantao = :idEquipePlantao,
                                observacao = :observacao,
                                id_usuario_atualizacao = :idUsuarioAtualizacao,
                                data_atualizacao = :dataAtualizacao
                            WHERE id_escala_dia = :idEscalaDia';

            $stmt = $this->pdo->prepare($sqlAtualiza);
            $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
            $stmt->bindValue(':observacao', $observacao);
            $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':dataAtualizacao', $this->agora());
            $stmt->bindValue(':idEscalaDia', $existente['id_escala_dia'], PDO::PARAM_INT);
            $stmt->execute();

            return (int) $existente['id_escala_dia'];
        }

        $sqlInserir = 'INSERT INTO saude_escala_dia (id_escala_mensal, dia, turno, id_equipe_plantao, observacao, id_usuario_atualizacao, data_atualizacao)
                       VALUES (:idEscalaMensal, :dia, :turno, :idEquipePlantao, :observacao, :idUsuarioAtualizacao, :dataAtualizacao)';

        $stmt = $this->pdo->prepare($sqlInserir);
        $stmt->bindValue(':idEscalaMensal', $idEscalaMensal, PDO::PARAM_INT);
        $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
        $stmt->bindValue(':turno', $turno);
        $stmt->bindValue(':idEquipePlantao', $idEquipePlantao, PDO::PARAM_INT);
        $stmt->bindValue(':observacao', $observacao);
        $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':dataAtualizacao', $this->agora());
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function removerEscalaDia(int $idEscalaMensal, int $dia, ?string $turno = null): bool
    {
        $turnoSql = '';
        $turnoNormalizado = null;

        if (!is_null($turno)) {
            $turnoNormalizado = $this->normalizarTurno($turno);
            $turnoSql = ' AND turno = :turno ';
        }

        $sql = "DELETE FROM saude_escala_dia WHERE id_escala_mensal = :idEscalaMensal AND dia = :dia {$turnoSql}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEscalaMensal', $idEscalaMensal, PDO::PARAM_INT);
        $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
        if (!is_null($turnoNormalizado)) {
            $stmt->bindValue(':turno', $turnoNormalizado);
        }

        return $stmt->execute();
    }

    public function limparEscalaMensal(int $idEscalaMensal): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM saude_escala_dia WHERE id_escala_mensal = :idEscalaMensal');
        $stmt->bindValue(':idEscalaMensal', $idEscalaMensal, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function salvarAjusteMembroDia(int $idEscalaDia, int $idFuncionario, string $ajuste, int $idUsuario, ?string $observacao = null): int
    {
        $ajuste = strtoupper(trim($ajuste));

        if (!in_array($ajuste, ['ADICIONAR', 'REMOVER'], true)) {
            throw new InvalidArgumentException('Tipo de ajuste inválido.', 400);
        }

        $sqlBusca = 'SELECT id_plantao_membro_dia
                     FROM saude_plantao_membro_dia
                     WHERE id_escala_dia = :idEscalaDia
                       AND id_funcionario = :idFuncionario
                     LIMIT 1';

        $stmtBusca = $this->pdo->prepare($sqlBusca);
        $stmtBusca->bindValue(':idEscalaDia', $idEscalaDia, PDO::PARAM_INT);
        $stmtBusca->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
        $stmtBusca->execute();

        $existente = $stmtBusca->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $sqlUpdate = 'UPDATE saude_plantao_membro_dia
                          SET ajuste = :ajuste,
                              observacao = :observacao,
                              id_usuario_atualizacao = :idUsuarioAtualizacao,
                              data_atualizacao = :dataAtualizacao
                          WHERE id_plantao_membro_dia = :idPlantaoMembroDia';

            $stmt = $this->pdo->prepare($sqlUpdate);
            $stmt->bindValue(':ajuste', $ajuste);
            $stmt->bindValue(':observacao', $observacao);
            $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':dataAtualizacao', $this->agora());
            $stmt->bindValue(':idPlantaoMembroDia', $existente['id_plantao_membro_dia'], PDO::PARAM_INT);
            $stmt->execute();

            return (int) $existente['id_plantao_membro_dia'];
        }

        $sqlInsert = 'INSERT INTO saude_plantao_membro_dia (id_escala_dia, id_funcionario, ajuste, observacao, id_usuario_atualizacao, data_atualizacao)
                      VALUES (:idEscalaDia, :idFuncionario, :ajuste, :observacao, :idUsuarioAtualizacao, :dataAtualizacao)';

        $stmt = $this->pdo->prepare($sqlInsert);
        $stmt->bindValue(':idEscalaDia', $idEscalaDia, PDO::PARAM_INT);
        $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);
        $stmt->bindValue(':ajuste', $ajuste);
        $stmt->bindValue(':observacao', $observacao);
        $stmt->bindValue(':idUsuarioAtualizacao', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':dataAtualizacao', $this->agora());
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function removerAjusteMembroDia(int $idEscalaDia, int $idFuncionario): bool
    {
        $sql = 'DELETE FROM saude_plantao_membro_dia WHERE id_escala_dia = :idEscalaDia AND id_funcionario = :idFuncionario';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEscalaDia', $idEscalaDia, PDO::PARAM_INT);
        $stmt->bindValue(':idFuncionario', $idFuncionario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function listarAjustesDia(int $idEscalaDia): array
    {
        $sql = "SELECT
                    spmd.id_plantao_membro_dia,
                    spmd.id_escala_dia,
                    spmd.id_funcionario,
                    spmd.ajuste,
                    spmd.observacao,
                    spmd.id_usuario_atualizacao,
                    spmd.data_atualizacao,
                    p.nome,
                    p.sobrenome,
                    c.cargo,
                    CONCAT(TRIM(p.nome), ' ', TRIM(COALESCE(p.sobrenome, ''))) AS nome_completo
                FROM saude_plantao_membro_dia spmd
                INNER JOIN funcionario f ON f.id_funcionario = spmd.id_funcionario
                INNER JOIN pessoa p ON p.id_pessoa = f.id_pessoa
                INNER JOIN cargo c ON c.id_cargo = f.id_cargo
                WHERE spmd.id_escala_dia = :idEscalaDia
                ORDER BY p.nome ASC, p.sobrenome ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEscalaDia', $idEscalaDia, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarMembrosFixosPorEscalaDia(int $idEscalaDia): array
    {
        $sql = "SELECT
                    sem.id_equipe_membro,
                    sem.id_funcionario,
                    p.id_pessoa,
                    p.nome,
                    p.sobrenome,
                    c.cargo,
                    CONCAT(TRIM(p.nome), ' ', TRIM(COALESCE(p.sobrenome, ''))) AS nome_completo
                FROM saude_escala_dia sed
                INNER JOIN saude_equipe_membro sem ON sem.id_equipe_plantao = sed.id_equipe_plantao
                INNER JOIN funcionario f ON f.id_funcionario = sem.id_funcionario
                INNER JOIN pessoa p ON p.id_pessoa = f.id_pessoa
                INNER JOIN cargo c ON c.id_cargo = f.id_cargo
                WHERE sed.id_escala_dia = :idEscalaDia
                ORDER BY p.nome ASC, p.sobrenome ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idEscalaDia', $idEscalaDia, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function resolverEscalaDiaPorData(DateTimeInterface $data): ?array
    {
        $referencia = DateTimeImmutable::createFromInterface($data)
            ->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        $horario = (int) $referencia->format('Hi');
        $dataPlantao = $referencia;
        $turno = 'DIA';

        if ($horario >= 700 && $horario < 1900) {
            $turno = 'DIA';
        } elseif ($horario >= 1900) {
            $turno = 'NOITE';
        } else {
            $turno = 'NOITE';
            $dataPlantao = $referencia->modify('-1 day');
        }

        $dia = (int) $dataPlantao->format('d');
        $mes = (int) $dataPlantao->format('m');
        $ano = (int) $dataPlantao->format('Y');

        $sql = "SELECT
                    sed.id_escala_dia,
                    sed.id_escala_mensal,
                    sed.id_equipe_plantao,
                    sed.dia,
                    sed.turno,
                    sem.ano,
                    sem.mes,
                    sep.nome AS equipe_nome,
                    sep.ativo AS equipe_ativa
                FROM saude_escala_mensal sem
                INNER JOIN saude_escala_dia sed ON sed.id_escala_mensal = sem.id_escala_mensal
                INNER JOIN saude_equipe_plantao sep ON sep.id_equipe_plantao = sed.id_equipe_plantao
                WHERE sem.ano = :ano
                  AND sem.mes = :mes
                  AND sed.dia = :dia
                  AND sed.turno = :turno
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);
        $stmt->bindValue(':dia', $dia, PDO::PARAM_INT);
        $stmt->bindValue(':turno', $turno);
        $stmt->execute();

        $escalaDia = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$escalaDia) {
            return null;
        }

        $escalaDia['data_plantao_resolvida'] = $dataPlantao->format('Y-m-d');
        $escalaDia['turno'] = $turno;
        return $escalaDia;
    }

    public function copiarEscalaMensal(int $origemAno, int $origemMes, int $destinoAno, int $destinoMes, int $idUsuario): array
    {
        $origem = $this->obterEscalaMensal($origemAno, $origemMes);

        if (!$origem) {
            throw new InvalidArgumentException('Não existe escala no mês anterior para copiar.', 404);
        }

        $escalaDestino = new SaudeEscalaMensal($destinoAno, $destinoMes, $origem['observacao'] ?? null);

        $this->pdo->beginTransaction();

        try {
            $idEscalaDestino = $this->obterOuCriarEscalaMensal($escalaDestino, $idUsuario);

            $this->limparEscalaMensal($idEscalaDestino);

            $diasOrigem = $this->listarDiasEscalaPorIdEscalaMensal((int) $origem['id_escala_mensal']);
            $quantidadeDiasDestino = cal_days_in_month(CAL_GREGORIAN, $destinoMes, $destinoAno);

            foreach ($diasOrigem as $diaOrigem) {
                $dia = (int) $diaOrigem['dia'];

                if ($dia > $quantidadeDiasDestino) {
                    continue;
                }

                $this->upsertEscalaDia(
                    $idEscalaDestino,
                    $dia,
                    (string) ($diaOrigem['turno'] ?? 'DIA'),
                    (int) $diaOrigem['id_equipe_plantao'],
                    $idUsuario,
                    $diaOrigem['observacao'] ?? null
                );
            }

            $this->pdo->commit();

            return [
                'id_escala_mensal_origem' => (int) $origem['id_escala_mensal'],
                'id_escala_mensal_destino' => $idEscalaDestino
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function registrarLog(SaudeLogEquipePlantao $log): bool
    {
        $sql = 'INSERT INTO saude_log_equipe_plantao
                (id_usuario, data_hora, acao, descricao, id_equipe_plantao, id_funcionario, id_escala_mensal, id_escala_dia, dados_json)
                VALUES
                (:idUsuario, :dataHora, :acao, :descricao, :idEquipePlantao, :idFuncionario, :idEscalaMensal, :idEscalaDia, :dadosJson)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idUsuario', $log->getIdUsuario(), PDO::PARAM_INT);
        $stmt->bindValue(':dataHora', $log->getDataHora()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':acao', $log->getAcao());
        $stmt->bindValue(':descricao', $log->getDescricao());
        $stmt->bindValue(':idEquipePlantao', $log->getIdEquipePlantao(), is_null($log->getIdEquipePlantao()) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':idFuncionario', $log->getIdFuncionario(), is_null($log->getIdFuncionario()) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':idEscalaMensal', $log->getIdEscalaMensal(), is_null($log->getIdEscalaMensal()) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':idEscalaDia', $log->getIdEscalaDia(), is_null($log->getIdEscalaDia()) ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':dadosJson', $log->getDadosJson(), is_null($log->getDadosJson()) ? PDO::PARAM_NULL : PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function listarLogs(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filtros['id_escala_dia'])) {
            $where[] = 'slp.id_escala_dia = :idEscalaDia';
            $params[':idEscalaDia'] = (int) $filtros['id_escala_dia'];
        }

        if (!empty($filtros['id_equipe_plantao'])) {
            $where[] = 'slp.id_equipe_plantao = :idEquipePlantao';
            $params[':idEquipePlantao'] = (int) $filtros['id_equipe_plantao'];
        }

        if (!empty($filtros['ano']) && !empty($filtros['mes'])) {
            $where[] = 'sem.ano = :ano AND sem.mes = :mes';
            $params[':ano'] = (int) $filtros['ano'];
            $params[':mes'] = (int) $filtros['mes'];
        }

        $whereSql = '';

        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $sql = "SELECT
                    slp.*,
                    CONCAT(TRIM(p.nome), ' ', TRIM(COALESCE(p.sobrenome, ''))) AS usuario_nome,
                    sep.nome AS equipe_nome,
                    CONCAT(TRIM(pf.nome), ' ', TRIM(COALESCE(pf.sobrenome, ''))) AS tecnico_nome,
                    sem.ano,
                    sem.mes,
                    sed.dia,
                    sed.turno
                FROM saude_log_equipe_plantao slp
                INNER JOIN pessoa p ON p.id_pessoa = slp.id_usuario
                LEFT JOIN saude_equipe_plantao sep ON sep.id_equipe_plantao = slp.id_equipe_plantao
                LEFT JOIN funcionario f ON f.id_funcionario = slp.id_funcionario
                LEFT JOIN pessoa pf ON pf.id_pessoa = f.id_pessoa
                LEFT JOIN saude_escala_mensal sem ON sem.id_escala_mensal = slp.id_escala_mensal
                LEFT JOIN saude_escala_dia sed ON sed.id_escala_dia = slp.id_escala_dia
                {$whereSql}
                ORDER BY slp.data_hora DESC, slp.id_log_equipe_plantao DESC";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function sanitizarListaIds(array $ids): array
    {
        $idsSanitizados = [];

        foreach ($ids as $id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);

            if ($id && $id > 0) {
                $idsSanitizados[] = (int) $id;
            }
        }

        $idsSanitizados = array_values(array_unique($idsSanitizados));
        sort($idsSanitizados);

        return $idsSanitizados;
    }

    private function normalizarTurno(string $turno): string
    {
        $turno = strtoupper(trim($turno));

        if (!in_array($turno, ['DIA', 'NOITE'], true)) {
            throw new InvalidArgumentException('Turno inválido.', 400);
        }

        return $turno;
    }

    private function agora(): string
    {
        return (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');
    }
}
