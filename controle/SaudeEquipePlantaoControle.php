<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . 'SaudeEquipePlantaoService.php';
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Util.php';

class SaudeEquipePlantaoControle
{
    private SaudeEquipePlantaoService $service;

    public function __construct(?SaudeEquipePlantaoService $service = null)
    {
        $this->service = $service ?? new SaudeEquipePlantaoService();
    }

    public function listarPainel(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $idEquipe = $this->inteiroOpcional($dados, 'id_equipe_plantao');
            $idTecnico = $this->inteiroOpcional($dados, 'id_funcionario');

            $resposta = [
                'ano' => $ano,
                'mes' => $mes,
                'equipes' => $this->service->listarEquipes(),
                'tecnicos' => $this->service->listarTecnicosEnfermagem(),
                'escala' => $this->service->listarEscalaMensal($ano, $mes, $idEquipe, $idTecnico)
            ];

            $this->jsonSucesso($resposta);
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function buscarEquipe(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idEquipePlantao = $this->inteiro($dados, 'id_equipe_plantao');
            $equipe = $this->service->buscarEquipePorId($idEquipePlantao);

            if (!$equipe) {
                throw new InvalidArgumentException('Equipe não encontrada.', 404);
            }

            $this->jsonSucesso($equipe);
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function salvarEquipe(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $membros = $dados['membros'] ?? [];

            if (!is_array($membros)) {
                throw new InvalidArgumentException('Lista de membros inválida.', 400);
            }

            $resultado = $this->service->salvarEquipe($dados, $membros, $idUsuario);
            $this->jsonSucesso($resultado, 'Equipe salva com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function alterarStatusEquipe(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idEquipePlantao = $this->inteiro($dados, 'id_equipe_plantao');
            $ativo = isset($dados['ativo']) ? (bool) $dados['ativo'] : false;
            $idUsuario = $this->idUsuarioLogado();

            $this->service->alterarStatusEquipe($idEquipePlantao, $ativo, $idUsuario);

            $this->jsonSucesso([], 'Status da equipe atualizado com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function salvarEscalaMensal(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $dias = $dados['dias'] ?? [];
            $observacao = $dados['observacao'] ?? null;

            if (!is_array($dias)) {
                throw new InvalidArgumentException('Formato dos dias da escala inválido.', 400);
            }

            $escala = $this->service->salvarEscalaMensal($ano, $mes, $dias, $observacao, $idUsuario);

            $this->jsonSucesso($escala, 'Escala mensal salva com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function alterarBloqueioEscalaMensal(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $bloqueada = isset($dados['bloqueada']) ? (bool) $dados['bloqueada'] : true;

            $escala = $this->service->alterarBloqueioEscalaMensal($ano, $mes, $bloqueada, $idUsuario);

            $this->jsonSucesso($escala, $bloqueada ? 'Escala bloqueada com sucesso.' : 'Edição da escala liberada com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function definirEquipeDia(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $dia = $this->inteiro($dados, 'dia');
            $turno = strtoupper(trim((string) ($dados['turno'] ?? 'DIA')));
            $idEquipePlantao = $this->inteiro($dados, 'id_equipe_plantao');
            $observacao = $dados['observacao'] ?? null;

            $detalhes = $this->service->definirEquipeDia($ano, $mes, $dia, $turno, $idEquipePlantao, $observacao, $idUsuario);

            $this->jsonSucesso($detalhes, 'Equipe do turno definida com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function copiarEscalaMesAnterior(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));

            $escala = $this->service->copiarEscalaMesAnterior($ano, $mes, $idUsuario);
            $this->jsonSucesso($escala, 'Escala copiada do mês anterior com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function limparEscalaMensal(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));

            $escala = $this->service->limparEscalaMensal($ano, $mes, $idUsuario);
            $this->jsonSucesso($escala, 'Escala mensal limpa com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function detalharDia(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $dia = $this->inteiro($dados, 'dia');
            $turno = strtoupper(trim((string) ($dados['turno'] ?? 'DIA')));

            $detalhes = $this->service->detalharDia($ano, $mes, $dia, $turno);
            $this->jsonSucesso($detalhes);
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function salvarAjusteMembroDia(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $dia = $this->inteiro($dados, 'dia');
            $turno = strtoupper(trim((string) ($dados['turno'] ?? 'DIA')));
            $idFuncionario = $this->inteiro($dados, 'id_funcionario');
            $ajuste = trim((string) ($dados['ajuste'] ?? ''));
            $observacao = $dados['observacao'] ?? null;

            $detalhes = $this->service->salvarAjusteMembroDia(
                $ano,
                $mes,
                $dia,
                $turno,
                $idFuncionario,
                $ajuste,
                $observacao,
                $idUsuario
            );

            $this->jsonSucesso($detalhes, 'Ajuste do plantão salvo com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function removerAjusteMembroDia(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $idUsuario = $this->idUsuarioLogado();
            $ano = $this->inteiro($dados, 'ano', (int) date('Y'));
            $mes = $this->inteiro($dados, 'mes', (int) date('m'));
            $dia = $this->inteiro($dados, 'dia');
            $turno = strtoupper(trim((string) ($dados['turno'] ?? 'DIA')));
            $idFuncionario = $this->inteiro($dados, 'id_funcionario');

            $detalhes = $this->service->removerAjusteMembroDia($ano, $mes, $dia, $turno, $idFuncionario, $idUsuario);

            $this->jsonSucesso($detalhes, 'Ajuste removido com sucesso.');
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    public function listarLogs(): void
    {
        $dados = $this->dadosRequisicao();

        try {
            $filtros = [
                'ano' => $this->inteiroOpcional($dados, 'ano'),
                'mes' => $this->inteiroOpcional($dados, 'mes'),
                'id_escala_dia' => $this->inteiroOpcional($dados, 'id_escala_dia'),
                'id_equipe_plantao' => $this->inteiroOpcional($dados, 'id_equipe_plantao')
            ];

            $logs = $this->service->listarLogs($filtros);
            $this->jsonSucesso($logs);
        } catch (Throwable $e) {
            Util::tratarException($e);
        }
    }

    private function dadosRequisicao(): array
    {
        $dados = [];

        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $dados = json_decode($json, true) ?: [];
        }

        if (!empty($_REQUEST)) {
            $dados = array_merge($dados, $_REQUEST);
        }

        return $dados;
    }

    private function inteiro(array $dados, string $campo, ?int $padrao = null): int
    {
        $valor = $dados[$campo] ?? $padrao;
        $valor = filter_var($valor, FILTER_VALIDATE_INT);

        if ($valor === false || is_null($valor)) {
            throw new InvalidArgumentException(sprintf('Campo "%s" inválido.', $campo), 400);
        }

        return (int) $valor;
    }

    private function inteiroOpcional(array $dados, string $campo): ?int
    {
        if (!isset($dados[$campo]) || $dados[$campo] === '' || is_null($dados[$campo])) {
            return null;
        }

        $valor = filter_var($dados[$campo], FILTER_VALIDATE_INT);

        if ($valor === false) {
            return null;
        }

        return (int) $valor;
    }

    private function idUsuarioLogado(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idPessoa = $_SESSION['id_pessoa'] ?? null;

        if (is_null($idPessoa) || !filter_var($idPessoa, FILTER_VALIDATE_INT)) {
            throw new LogicException('Usuário não autenticado.', 401);
        }

        return (int) $idPessoa;
    }

    private function jsonSucesso($dados, string $mensagem = 'OK'): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'ok',
            'mensagem' => $mensagem,
            'dados' => $dados
        ]);
        exit();
    }
}
