<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'dao' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoDAO.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantao.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudeEscalaMensal.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SaudeLogEquipePlantao.php';

class SaudeEquipePlantaoService
{
    private const TURNOS = ['DIA', 'NOITE'];

    private SaudeEquipePlantaoDAO $dao;

    public function __construct(?SaudeEquipePlantaoDAO $dao = null)
    {
        $this->dao = $dao ?? new SaudeEquipePlantaoDAO();
    }

    private function escalaEstaBloqueada(?array $escala): bool
    {
        return !empty($escala) && (int) ($escala['bloqueada'] ?? 0) === 1;
    }

    private function garantirEscalaEditavel(?array $escala): void
    {
        if ($this->escalaEstaBloqueada($escala)) {
            throw new InvalidArgumentException('A escala deste mês está bloqueada. Clique em Editar escala para liberar alterações.', 409);
        }
    }

    public function listarTecnicosEnfermagem(?string $filtro = null): array
    {
        return $this->dao->listarTecnicosEnfermagem($filtro);
    }

    public function listarEquipes(?bool $somenteAtivas = null): array
    {
        return $this->dao->listarEquipes($somenteAtivas);
    }

    public function buscarEquipePorId(int $idEquipePlantao): ?array
    {
        return $this->dao->buscarEquipePorId($idEquipePlantao);
    }

    private function garantirEquipeAtivaParaEscala(int $idEquipePlantao): void
    {
        $equipe = $this->dao->buscarEquipePorId($idEquipePlantao);

        if (!$equipe) {
            throw new InvalidArgumentException('Equipe não encontrada.', 404);
        }

        if ((int) ($equipe['ativo'] ?? 0) !== 1) {
            throw new InvalidArgumentException('Equipe inativa não pode ser usada em novas escalas.', 409);
        }
    }

    public function salvarEquipe(array $dadosEquipe, array $membros, int $idUsuario): array
    {
        $idEquipePlantao = null;
        if (array_key_exists('id_equipe_plantao', $dadosEquipe) && $dadosEquipe['id_equipe_plantao'] !== '' && !is_null($dadosEquipe['id_equipe_plantao'])) {
            $idEquipePlantao = filter_var($dadosEquipe['id_equipe_plantao'], FILTER_VALIDATE_INT);

            if ($idEquipePlantao === false || $idEquipePlantao < 1) {
                throw new InvalidArgumentException('Identificador de equipe inválido.', 400);
            }
        }

        $nome = trim((string) ($dadosEquipe['nome'] ?? ''));
        $descricao = $dadosEquipe['descricao'] ?? null;
        $ativo = isset($dadosEquipe['ativo']) ? (bool) $dadosEquipe['ativo'] : true;

        $equipe = new SaudeEquipePlantao($nome, $descricao, $ativo);
        $equipe->setIdEquipePlantao($idEquipePlantao ?: null);

        $equipeAntes = null;
        if (!is_null($idEquipePlantao) && $idEquipePlantao > 0) {
            $equipeAntes = $this->dao->buscarEquipePorId((int) $idEquipePlantao);

            if (!$equipeAntes) {
                throw new InvalidArgumentException('Equipe não encontrada para edição.', 404);
            }
        }

        $idEquipeSalva = $this->dao->salvarEquipe($equipe, $membros, $idUsuario);

        $equipeDepois = $this->dao->buscarEquipePorId($idEquipeSalva);

        if (is_null($equipeAntes)) {
            $this->registrarLog(
                $idUsuario,
                'EQUIPE_CRIADA',
                sprintf('Equipe "%s" criada.', $nome),
                $idEquipeSalva,
                null,
                null,
                null,
                [
                    'equipe' => $equipeDepois
                ]
            );

            $membrosCriados = array_map('intval', array_column($equipeDepois['membros'] ?? [], 'id_funcionario'));
            foreach ($membrosCriados as $idFuncionario) {
                $this->registrarLog(
                    $idUsuario,
                    'MEMBRO_FIXO_ADICIONADO',
                    sprintf('Técnico #%d adicionado como membro fixo da equipe "%s".', $idFuncionario, $nome),
                    $idEquipeSalva,
                    $idFuncionario
                );
            }
        } else {
            $this->registrarLog(
                $idUsuario,
                'EQUIPE_EDITADA',
                sprintf('Equipe "%s" atualizada.', $nome),
                $idEquipeSalva,
                null,
                null,
                null,
                [
                    'antes' => $equipeAntes,
                    'depois' => $equipeDepois
                ]
            );

            $membrosAntes = array_map('intval', array_column($equipeAntes['membros'] ?? [], 'id_funcionario'));
            $membrosDepois = array_map('intval', array_column($equipeDepois['membros'] ?? [], 'id_funcionario'));

            $adicionados = array_values(array_diff($membrosDepois, $membrosAntes));
            $removidos = array_values(array_diff($membrosAntes, $membrosDepois));

            foreach ($adicionados as $idFuncionario) {
                $this->registrarLog(
                    $idUsuario,
                    'MEMBRO_FIXO_ADICIONADO',
                    sprintf('Técnico #%d adicionado como membro fixo da equipe "%s".', $idFuncionario, $nome),
                    $idEquipeSalva,
                    $idFuncionario
                );
            }

            foreach ($removidos as $idFuncionario) {
                $this->registrarLog(
                    $idUsuario,
                    'MEMBRO_FIXO_REMOVIDO',
                    sprintf('Técnico #%d removido dos membros fixos da equipe "%s".', $idFuncionario, $nome),
                    $idEquipeSalva,
                    $idFuncionario
                );
            }
        }

        return $equipeDepois ?? [];
    }

    public function alterarStatusEquipe(int $idEquipePlantao, bool $ativo, int $idUsuario): bool
    {
        $equipe = $this->dao->buscarEquipePorId($idEquipePlantao);

        if (!$equipe) {
            throw new InvalidArgumentException('Equipe não encontrada.', 404);
        }

        $resultado = $this->dao->alterarStatusEquipe($idEquipePlantao, $ativo, $idUsuario);

        if ($resultado) {
            $this->registrarLog(
                $idUsuario,
                'EQUIPE_STATUS_ALTERADO',
                sprintf('Equipe "%s" marcada como %s.', $equipe['nome'], $ativo ? 'ativa' : 'inativa'),
                $idEquipePlantao,
                null,
                null,
                null,
                [
                    'ativo' => $ativo
                ]
            );
        }

        return $resultado;
    }

    public function listarEscalaMensal(int $ano, int $mes, ?int $idEquipeFiltro = null, ?int $idTecnicoFiltro = null): array
    {
        $escalaMensal = $this->dao->obterEscalaMensal($ano, $mes);
        $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

        $diasEscala = [];
        if ($escalaMensal) {
            $diasEscala = $this->dao->listarDiasEscalaPorIdEscalaMensal((int) $escalaMensal['id_escala_mensal']);
        }

        $mapaDias = [];
        foreach ($diasEscala as $diaEscala) {
            $dia = (int) ($diaEscala['dia'] ?? 0);
            $turno = $this->normalizarTurno((string) ($diaEscala['turno'] ?? 'DIA'));
            $mapaDias[$dia][$turno] = $diaEscala;
        }

        $resultadoDias = [];

        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
            $data = DateTime::createFromFormat('Y-n-j', sprintf('%04d-%d-%d', $ano, $mes, $dia));
            $nomeSemana = $this->nomeDiaSemana($data ?: null);
            $turnos = [];
            $atendeEquipeFiltro = is_null($idEquipeFiltro) || $idEquipeFiltro <= 0;
            $atendeTecnicoFiltro = is_null($idTecnicoFiltro) || $idTecnicoFiltro <= 0;

            foreach (self::TURNOS as $turno) {
                $registroTurno = $mapaDias[$dia][$turno] ?? null;
                $composicao = $this->composicaoVazia();

                if ($registroTurno && !empty($registroTurno['id_escala_dia'])) {
                    $composicao = $this->obterComposicaoPlantaoPorEscalaDia((int) $registroTurno['id_escala_dia']);
                }

                $turnos[$turno] = [
                    'turno' => $turno,
                    'turno_label' => $this->rotuloTurno($turno),
                    'faixa_horario' => $this->faixaHorarioTurno($turno),
                    'id_escala_dia' => $registroTurno['id_escala_dia'] ?? null,
                    'id_equipe_plantao' => $registroTurno['id_equipe_plantao'] ?? null,
                    'equipe_nome' => $registroTurno['equipe_nome'] ?? null,
                    'equipe_ativa' => isset($registroTurno['equipe_ativa']) ? (int) $registroTurno['equipe_ativa'] : null,
                    'observacao' => $registroTurno['observacao'] ?? null,
                    'quantidade_membros' => $composicao['quantidade_membros_plantao'],
                    'membros_plantao' => $composicao['membros_plantao']
                ];

                if (!$atendeEquipeFiltro && (int) ($turnos[$turno]['id_equipe_plantao'] ?? 0) === $idEquipeFiltro) {
                    $atendeEquipeFiltro = true;
                }

                if (!$atendeTecnicoFiltro && $idTecnicoFiltro > 0) {
                    $idsTecnicosNoTurno = array_map('intval', array_column($composicao['membros_plantao'], 'id_funcionario'));
                    if (in_array($idTecnicoFiltro, $idsTecnicosNoTurno, true)) {
                        $atendeTecnicoFiltro = true;
                    }
                }
            }

            if (!$atendeEquipeFiltro || !$atendeTecnicoFiltro) {
                continue;
            }

            $resultadoDias[] = [
                'dia' => $dia,
                'nome_dia_semana' => $nomeSemana,
                'turnos' => $turnos
            ];
        }

        return [
            'ano' => $ano,
            'mes' => $mes,
            'id_escala_mensal' => $escalaMensal['id_escala_mensal'] ?? null,
            'bloqueada' => isset($escalaMensal['bloqueada']) ? (int) $escalaMensal['bloqueada'] : 0,
            'observacao' => $escalaMensal['observacao'] ?? null,
            'dias' => $resultadoDias
        ];
    }

    public function salvarEscalaMensal(
        int $ano,
        int $mes,
        array $dias,
        ?string $observacao,
        int $idUsuario
    ): array {
        $escalaExistente = $this->dao->obterEscalaMensal($ano, $mes);
        $this->garantirEscalaEditavel($escalaExistente);
        $observacaoEscala = !is_null($observacao) ? $observacao : ($escalaExistente['observacao'] ?? null);

        $escalaMensal = new SaudeEscalaMensal($ano, $mes, $observacaoEscala);
        $idEscalaMensal = $this->dao->obterOuCriarEscalaMensal($escalaMensal, $idUsuario);

        $diasAtuais = $this->dao->listarDiasEscalaPorIdEscalaMensal($idEscalaMensal);
        $mapaAtual = [];

        foreach ($diasAtuais as $diaAtual) {
            $mapaAtual[(int) $diaAtual['dia']][$this->normalizarTurno((string) ($diaAtual['turno'] ?? 'DIA'))] = $diaAtual;
        }

        $totalDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

        for ($dia = 1; $dia <= $totalDiasMes; $dia++) {
            $configNovoDia = $dias[$dia] ?? $dias[(string) $dia] ?? null;

            foreach (self::TURNOS as $turno) {
                [$idEquipeNova, $observacaoTurno] = $this->extrairConfiguracaoTurno($configNovoDia, $turno);
                $turnoAtual = $mapaAtual[$dia][$turno] ?? null;

                if ($idEquipeNova && $idEquipeNova > 0) {
                    $this->garantirEquipeAtivaParaEscala((int) $idEquipeNova);
                    $idEscalaDia = $this->dao->upsertEscalaDia($idEscalaMensal, $dia, $turno, $idEquipeNova, $idUsuario, $observacaoTurno);

                    $houveMudanca = true;

                    if ($turnoAtual) {
                        $idEquipeAtual = (int) $turnoAtual['id_equipe_plantao'];
                        $observacaoAtual = $turnoAtual['observacao'] ?? null;

                        $houveMudanca = ($idEquipeAtual !== $idEquipeNova) || ((string) $observacaoAtual !== (string) $observacaoTurno);
                    }

                    if ($houveMudanca) {
                        $this->registrarLog(
                            $idUsuario,
                            'ESCALA_TURNO_ATUALIZADO',
                            sprintf('%s de %02d/%02d/%04d atualizado na escala mensal.', $this->rotuloTurno($turno), $dia, $mes, $ano),
                            $idEquipeNova,
                            null,
                            $idEscalaMensal,
                            $idEscalaDia,
                            [
                                'turno' => $turno,
                                'id_equipe_nova' => $idEquipeNova,
                                'id_equipe_anterior' => $turnoAtual['id_equipe_plantao'] ?? null,
                                'observacao' => $observacaoTurno
                            ]
                        );
                    }

                    continue;
                }

                if ($turnoAtual) {
                    $this->dao->removerEscalaDia($idEscalaMensal, $dia, $turno);

                    $this->registrarLog(
                        $idUsuario,
                        'ESCALA_TURNO_REMOVIDO',
                        sprintf('%s de %02d/%02d/%04d removido da escala mensal.', $this->rotuloTurno($turno), $dia, $mes, $ano),
                        (int) $turnoAtual['id_equipe_plantao'],
                        null,
                        $idEscalaMensal,
                        (int) $turnoAtual['id_escala_dia'],
                        [
                            'turno' => $turno
                        ]
                    );
                }
            }
        }

        $this->registrarLog(
            $idUsuario,
            'ESCALA_MENSAL_SALVA',
            sprintf('Escala mensal %02d/%04d salva.', $mes, $ano),
            null,
            null,
            $idEscalaMensal
        );

        $this->dao->alterarBloqueioEscalaMensal($idEscalaMensal, true, $idUsuario);

        $this->registrarLog(
            $idUsuario,
            'ESCALA_MENSAL_BLOQUEADA',
            sprintf('Escala mensal %02d/%04d bloqueada após salvamento.', $mes, $ano),
            null,
            null,
            $idEscalaMensal
        );

        return $this->listarEscalaMensal($ano, $mes);
    }

    public function alterarBloqueioEscalaMensal(int $ano, int $mes, bool $bloqueada, int $idUsuario): array
    {
        $escala = $this->dao->obterEscalaMensal($ano, $mes);

        if (!$escala) {
            throw new InvalidArgumentException('Não existe escala mensal criada para este mês.', 404);
        }

        $idEscalaMensal = (int) $escala['id_escala_mensal'];
        $this->dao->alterarBloqueioEscalaMensal($idEscalaMensal, $bloqueada, $idUsuario);

        $this->registrarLog(
            $idUsuario,
            $bloqueada ? 'ESCALA_MENSAL_BLOQUEADA' : 'ESCALA_MENSAL_EDICAO_LIBERADA',
            $bloqueada
                ? sprintf('Escala mensal %02d/%04d bloqueada manualmente.', $mes, $ano)
                : sprintf('Edição da escala mensal %02d/%04d liberada.', $mes, $ano),
            null,
            null,
            $idEscalaMensal,
            null,
            [
                'bloqueada' => $bloqueada ? 1 : 0
            ]
        );

        return $this->listarEscalaMensal($ano, $mes);
    }

    public function definirEquipeDia(
        int $ano,
        int $mes,
        int $dia,
        string $turno,
        int $idEquipePlantao,
        ?string $observacao,
        int $idUsuario
    ): array {
        $turno = $this->normalizarTurno($turno);
        $escalaExistente = $this->dao->obterEscalaMensal($ano, $mes);
        $this->garantirEscalaEditavel($escalaExistente);
        $this->garantirEquipeAtivaParaEscala($idEquipePlantao);
        $observacaoEscala = $escalaExistente['observacao'] ?? null;

        $escalaMensal = new SaudeEscalaMensal($ano, $mes, $observacaoEscala);
        $idEscalaMensal = $this->dao->obterOuCriarEscalaMensal($escalaMensal, $idUsuario);

        $antes = $this->dao->obterEscalaDia($idEscalaMensal, $dia, $turno);

        $idEscalaDia = $this->dao->upsertEscalaDia($idEscalaMensal, $dia, $turno, $idEquipePlantao, $idUsuario, $observacao);

        $this->registrarLog(
            $idUsuario,
            'EQUIPE_TURNO_DEFINIDA',
            sprintf('Equipe do %s de %02d/%02d/%04d definida/atualizada.', $this->rotuloTurno($turno), $dia, $mes, $ano),
            $idEquipePlantao,
            null,
            $idEscalaMensal,
            $idEscalaDia,
            [
                'turno' => $turno,
                'anterior' => $antes,
                'id_equipe_nova' => $idEquipePlantao,
                'observacao' => $observacao
            ]
        );

        return $this->detalharDia($ano, $mes, $dia, $turno);
    }

    public function copiarEscalaMesAnterior(int $ano, int $mes, int $idUsuario): array
    {
        $escalaDestino = $this->dao->obterEscalaMensal($ano, $mes);
        $this->garantirEscalaEditavel($escalaDestino);

        $dataAtual = DateTime::createFromFormat('Y-n-j', sprintf('%04d-%d-01', $ano, $mes));

        if (!$dataAtual) {
            throw new InvalidArgumentException('Mês/ano inválidos para cópia de escala.', 400);
        }

        $dataAnterior = clone $dataAtual;
        $dataAnterior->modify('-1 month');

        $origemAno = (int) $dataAnterior->format('Y');
        $origemMes = (int) $dataAnterior->format('m');

        $copiado = $this->dao->copiarEscalaMensal($origemAno, $origemMes, $ano, $mes, $idUsuario);

        $this->registrarLog(
            $idUsuario,
            'ESCALA_MENSAL_COPIADA',
            sprintf('Escala mensal %02d/%04d copiada de %02d/%04d.', $mes, $ano, $origemMes, $origemAno),
            null,
            null,
            $copiado['id_escala_mensal_destino'] ?? null,
            null,
            [
                'origem' => [
                    'ano' => $origemAno,
                    'mes' => $origemMes,
                    'id_escala_mensal' => $copiado['id_escala_mensal_origem'] ?? null
                ],
                'destino' => [
                    'ano' => $ano,
                    'mes' => $mes,
                    'id_escala_mensal' => $copiado['id_escala_mensal_destino'] ?? null
                ]
            ]
        );

        return $this->listarEscalaMensal($ano, $mes);
    }

    public function limparEscalaMensal(int $ano, int $mes, int $idUsuario): array
    {
        $escala = $this->dao->obterEscalaMensal($ano, $mes);
        $this->garantirEscalaEditavel($escala);

        if (!$escala) {
            return $this->listarEscalaMensal($ano, $mes);
        }

        $idEscalaMensal = (int) $escala['id_escala_mensal'];

        $this->dao->limparEscalaMensal($idEscalaMensal);

        $this->registrarLog(
            $idUsuario,
            'ESCALA_MENSAL_LIMPA',
            sprintf('Escala mensal %02d/%04d foi limpa.', $mes, $ano),
            null,
            null,
            $idEscalaMensal
        );

        return $this->listarEscalaMensal($ano, $mes);
    }

    public function detalharDia(int $ano, int $mes, int $dia, ?string $turno = null): array
    {
        $turno = $this->normalizarTurno($turno ?? 'DIA');
        $escala = $this->dao->obterEscalaMensal($ano, $mes);

        if (!$escala) {
            return [
                'ano' => $ano,
                'mes' => $mes,
                'dia' => $dia,
                'turno' => $turno,
                'turno_label' => $this->rotuloTurno($turno),
                'faixa_horario' => $this->faixaHorarioTurno($turno),
                'id_escala_mensal' => null,
                'bloqueada' => 0,
                'id_escala_dia' => null,
                'id_equipe_plantao' => null,
                'equipe_nome' => null,
                'observacao' => null,
                'membros_fixos' => [],
                'adicionados' => [],
                'removidos' => [],
                'membros_plantao' => [],
                'logs' => []
            ];
        }

        $idEscalaMensal = (int) $escala['id_escala_mensal'];
        $escalaDia = $this->dao->obterEscalaDia($idEscalaMensal, $dia, $turno);

        if (!$escalaDia) {
            return [
                'ano' => $ano,
                'mes' => $mes,
                'dia' => $dia,
                'turno' => $turno,
                'turno_label' => $this->rotuloTurno($turno),
                'faixa_horario' => $this->faixaHorarioTurno($turno),
                'id_escala_mensal' => $idEscalaMensal,
                'bloqueada' => isset($escala['bloqueada']) ? (int) $escala['bloqueada'] : 0,
                'id_escala_dia' => null,
                'id_equipe_plantao' => null,
                'equipe_nome' => null,
                'observacao' => null,
                'membros_fixos' => [],
                'adicionados' => [],
                'removidos' => [],
                'membros_plantao' => [],
                'logs' => []
            ];
        }

        $composicao = $this->obterComposicaoPlantaoPorEscalaDia((int) $escalaDia['id_escala_dia']);

        return [
            'ano' => $ano,
            'mes' => $mes,
            'dia' => $dia,
            'turno' => $turno,
            'turno_label' => $this->rotuloTurno($turno),
            'faixa_horario' => $this->faixaHorarioTurno($turno),
            'id_escala_mensal' => $idEscalaMensal,
            'bloqueada' => isset($escala['bloqueada']) ? (int) $escala['bloqueada'] : 0,
            'id_escala_dia' => (int) $escalaDia['id_escala_dia'],
            'id_equipe_plantao' => (int) $escalaDia['id_equipe_plantao'],
            'equipe_nome' => $escalaDia['equipe_nome'],
            'observacao' => $escalaDia['observacao'] ?? null,
            'membros_fixos' => $composicao['membros_fixos'],
            'adicionados' => $composicao['adicionados'],
            'removidos' => $composicao['removidos'],
            'membros_plantao' => $composicao['membros_plantao'],
            'logs' => $this->dao->listarLogs([
                'id_escala_dia' => (int) $escalaDia['id_escala_dia']
            ])
        ];
    }

    public function salvarAjusteMembroDia(
        int $ano,
        int $mes,
        int $dia,
        string $turno,
        int $idFuncionario,
        string $ajuste,
        ?string $observacao,
        int $idUsuario
    ): array {
        $turno = $this->normalizarTurno($turno);
        $escala = $this->dao->obterEscalaMensal($ano, $mes);
        $this->garantirEscalaEditavel($escala);

        if (!$escala) {
            throw new InvalidArgumentException('Não existe escala mensal criada para este mês.', 404);
        }

        $escalaDia = $this->dao->obterEscalaDia((int) $escala['id_escala_mensal'], $dia, $turno);

        if (!$escalaDia) {
            throw new InvalidArgumentException(sprintf('Não existe equipe principal definida para o %s.', mb_strtolower($this->rotuloTurno($turno), 'UTF-8')), 404);
        }

        $idEscalaDia = (int) $escalaDia['id_escala_dia'];

        $this->dao->salvarAjusteMembroDia($idEscalaDia, $idFuncionario, $ajuste, $idUsuario, $observacao);

        $this->registrarLog(
            $idUsuario,
            strtoupper(trim($ajuste)) === 'ADICIONAR' ? 'MEMBRO_DIA_ADICIONADO' : 'MEMBRO_DIA_REMOVIDO',
            sprintf(
                'Técnico #%d %s no %s de %02d/%02d/%04d.',
                $idFuncionario,
                strtoupper(trim($ajuste)) === 'ADICIONAR' ? 'adicionado' : 'removido',
                mb_strtolower($this->rotuloTurno($turno), 'UTF-8'),
                $dia,
                $mes,
                $ano
            ),
            (int) $escalaDia['id_equipe_plantao'],
            $idFuncionario,
            (int) $escala['id_escala_mensal'],
            $idEscalaDia,
            [
                'turno' => $turno,
                'ajuste' => strtoupper(trim($ajuste)),
                'observacao' => $observacao
            ]
        );

        return $this->detalharDia($ano, $mes, $dia, $turno);
    }

    public function removerAjusteMembroDia(int $ano, int $mes, int $dia, string $turno, int $idFuncionario, int $idUsuario): array
    {
        $turno = $this->normalizarTurno($turno);
        $escala = $this->dao->obterEscalaMensal($ano, $mes);
        $this->garantirEscalaEditavel($escala);

        if (!$escala) {
            throw new InvalidArgumentException('Não existe escala mensal criada para este mês.', 404);
        }

        $escalaDia = $this->dao->obterEscalaDia((int) $escala['id_escala_mensal'], $dia, $turno);

        if (!$escalaDia) {
            throw new InvalidArgumentException(sprintf('Não existe equipe principal definida para o %s.', mb_strtolower($this->rotuloTurno($turno), 'UTF-8')), 404);
        }

        $idEscalaDia = (int) $escalaDia['id_escala_dia'];
        $this->dao->removerAjusteMembroDia($idEscalaDia, $idFuncionario);

        $this->registrarLog(
            $idUsuario,
            'AJUSTE_DIA_REMOVIDO',
            sprintf('Ajuste do técnico #%d removido do %s de %02d/%02d/%04d.', $idFuncionario, mb_strtolower($this->rotuloTurno($turno), 'UTF-8'), $dia, $mes, $ano),
            (int) $escalaDia['id_equipe_plantao'],
            $idFuncionario,
            (int) $escala['id_escala_mensal'],
            $idEscalaDia,
            [
                'turno' => $turno
            ]
        );

        return $this->detalharDia($ano, $mes, $dia, $turno);
    }

    public function resolverPlantaoPorData(?DateTimeInterface $data = null): ?array
    {
        $data = $data ?: new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

        $escalaDia = $this->dao->resolverEscalaDiaPorData($data);

        if (!$escalaDia) {
            return null;
        }

        if ((int) ($escalaDia['equipe_ativa'] ?? 0) !== 1) {
            return null;
        }

        $composicao = $this->obterComposicaoPlantaoPorEscalaDia((int) $escalaDia['id_escala_dia']);

        return [
            'data' => $data->format('Y-m-d'),
            'data_plantao' => (string) ($escalaDia['data_plantao_resolvida'] ?? $data->format('Y-m-d')),
            'id_escala_dia' => (int) $escalaDia['id_escala_dia'],
            'id_escala_mensal' => (int) $escalaDia['id_escala_mensal'],
            'id_equipe_plantao' => (int) $escalaDia['id_equipe_plantao'],
            'turno' => $this->normalizarTurno((string) ($escalaDia['turno'] ?? 'DIA')),
            'turno_label' => $this->rotuloTurno((string) ($escalaDia['turno'] ?? 'DIA')),
            'faixa_horario' => $this->faixaHorarioTurno((string) ($escalaDia['turno'] ?? 'DIA')),
            'equipe_nome' => $escalaDia['equipe_nome'],
            'membros_plantao' => $composicao['membros_plantao'],
            'membros_fixos' => $composicao['membros_fixos'],
            'adicionados' => $composicao['adicionados'],
            'removidos' => $composicao['removidos'],
            'quantidade_membros' => $composicao['quantidade_membros_plantao']
        ];
    }

    public function enriquecerIntercorrenciasComEquipe(array $intercorrencias): array
    {
        $cacheComposicao = [];

        foreach ($intercorrencias as $indice => $intercorrencia) {
            $idEscalaDia = isset($intercorrencia['id_saude_escala_dia'])
                ? (int) $intercorrencia['id_saude_escala_dia']
                : 0;

            if ($idEscalaDia > 0) {
                if (!isset($cacheComposicao[$idEscalaDia])) {
                    $cacheComposicao[$idEscalaDia] = $this->obterComposicaoPlantaoPorEscalaDia($idEscalaDia);
                }

                $composicao = $cacheComposicao[$idEscalaDia];
                $intercorrencias[$indice]['equipe_membros'] = implode(', ', array_column($composicao['membros_plantao'], 'nome_completo'));
                $intercorrencias[$indice]['equipe_qtd_membros'] = $composicao['quantidade_membros_plantao'];
            } else {
                $intercorrencias[$indice]['equipe_membros'] = '';
                $intercorrencias[$indice]['equipe_qtd_membros'] = 0;
            }

            if (empty($intercorrencia['equipe_nome'])) {
                $intercorrencias[$indice]['equipe_nome'] = 'Não definida';
            }

            $turno = (string) ($intercorrencia['turno_plantao'] ?? 'DIA');
            if (!in_array($turno, self::TURNOS, true)) {
                $turno = 'DIA';
            }

            $intercorrencias[$indice]['turno_plantao'] = $turno;
            $intercorrencias[$indice]['turno_label'] = $this->rotuloTurno($turno);
            $intercorrencias[$indice]['faixa_horario'] = $this->faixaHorarioTurno($turno);
        }

        return $intercorrencias;
    }

    public function listarLogs(array $filtros = []): array
    {
        return $this->dao->listarLogs($filtros);
    }

    private function composicaoVazia(): array
    {
        return [
            'membros_plantao' => [],
            'membros_fixos' => [],
            'ajustes' => [],
            'adicionados' => [],
            'removidos' => [],
            'quantidade_membros_plantao' => 0
        ];
    }

    private function extrairConfiguracaoTurno($configNovoDia, string $turno): array
    {
        $turno = $this->normalizarTurno($turno);
        $idEquipeNova = null;
        $observacaoTurno = null;

        if (is_array($configNovoDia) && isset($configNovoDia['turnos']) && is_array($configNovoDia['turnos'])) {
            $configTurno = $configNovoDia['turnos'][$turno] ?? null;
        } elseif ($turno === 'DIA') {
            $configTurno = $configNovoDia;
        } else {
            $configTurno = null;
        }

        if (is_array($configTurno)) {
            $idEquipeNova = isset($configTurno['id_equipe_plantao'])
                ? filter_var($configTurno['id_equipe_plantao'], FILTER_VALIDATE_INT)
                : null;
            $observacaoTurno = $configTurno['observacao'] ?? null;
        } elseif (!is_array($configTurno)) {
            $idEquipeNova = filter_var($configTurno, FILTER_VALIDATE_INT);
        }

        return [
            $idEquipeNova === false ? null : $idEquipeNova,
            $observacaoTurno
        ];
    }

    private function normalizarTurno(string $turno): string
    {
        $turno = strtoupper(trim($turno));

        if (!in_array($turno, self::TURNOS, true)) {
            throw new InvalidArgumentException('Turno inválido.', 400);
        }

        return $turno;
    }

    private function rotuloTurno(string $turno): string
    {
        return $this->normalizarTurno($turno) === 'DIA'
            ? 'Plantão do dia'
            : 'Plantão da noite';
    }

    private function faixaHorarioTurno(string $turno): string
    {
        return $this->normalizarTurno($turno) === 'DIA'
            ? '07:00 às 19:00'
            : '19:00 às 07:00';
    }

    private function obterComposicaoPlantaoPorEscalaDia(int $idEscalaDia): array
    {
        $membrosFixos = $this->dao->listarMembrosFixosPorEscalaDia($idEscalaDia);
        $ajustes = $this->dao->listarAjustesDia($idEscalaDia);

        $membrosPlantao = [];
        foreach ($membrosFixos as $membroFixo) {
            $membrosPlantao[(int) $membroFixo['id_funcionario']] = $membroFixo;
        }

        $adicionados = [];
        $removidos = [];

        foreach ($ajustes as $ajuste) {
            $idFuncionario = (int) $ajuste['id_funcionario'];
            $tipoAjuste = strtoupper((string) $ajuste['ajuste']);

            if ($tipoAjuste === 'ADICIONAR') {
                $membrosPlantao[$idFuncionario] = $ajuste;
                $adicionados[$idFuncionario] = $ajuste;
                unset($removidos[$idFuncionario]);
                continue;
            }

            if ($tipoAjuste === 'REMOVER') {
                unset($membrosPlantao[$idFuncionario]);
                $removidos[$idFuncionario] = $ajuste;
                unset($adicionados[$idFuncionario]);
            }
        }

        $membrosPlantao = array_values($membrosPlantao);
        usort($membrosPlantao, [$this, 'compararNome']);

        $membrosFixosOrdenados = $membrosFixos;
        usort($membrosFixosOrdenados, [$this, 'compararNome']);

        $adicionados = array_values($adicionados);
        usort($adicionados, [$this, 'compararNome']);

        $removidos = array_values($removidos);
        usort($removidos, [$this, 'compararNome']);

        return [
            'membros_fixos' => $membrosFixosOrdenados,
            'ajustes' => $ajustes,
            'adicionados' => $adicionados,
            'removidos' => $removidos,
            'membros_plantao' => $membrosPlantao,
            'quantidade_membros_plantao' => count($membrosPlantao)
        ];
    }

    private function registrarLog(
        int $idUsuario,
        string $acao,
        string $descricao,
        ?int $idEquipePlantao = null,
        ?int $idFuncionario = null,
        ?int $idEscalaMensal = null,
        ?int $idEscalaDia = null,
        ?array $dados = null
    ): void {
        $log = new SaudeLogEquipePlantao(
            $idUsuario,
            $acao,
            $descricao,
            $idEquipePlantao,
            $idFuncionario,
            $idEscalaMensal,
            $idEscalaDia,
            $dados
        );

        $this->dao->registrarLog($log);
    }

    private function compararNome(array $a, array $b): int
    {
        return strcmp(
            mb_strtolower((string) ($a['nome_completo'] ?? ''), 'UTF-8'),
            mb_strtolower((string) ($b['nome_completo'] ?? ''), 'UTF-8')
        );
    }

    private function nomeDiaSemana(?DateTime $data): string
    {
        if (is_null($data)) {
            return '';
        }

        $dias = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $numeroDia = (int) $data->format('N');
        return $dias[$numeroDia] ?? '';
    }
}
