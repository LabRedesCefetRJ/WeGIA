<?php

require_once dirname(__DIR__) . '/dao/AtendimentoPacienteDAO.php';
require_once dirname(__DIR__) . '/dao/AtendidoDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AtendimentoPacienteControle
{
    private AtendimentoPacienteDAO $atendimentoPacienteDAO;
    private AtendidoDAO $atendidoDAO;

    public function __construct(
        ?AtendimentoPacienteDAO $atendimentoPacienteDAO = null,
        ?AtendidoDAO $atendidoDAO = null
    ) {
        $this->atendimentoPacienteDAO = $atendimentoPacienteDAO ?? new AtendimentoPacienteDAO();
        $this->atendidoDAO = $atendidoDAO ?? new AtendidoDAO();
    }

    public function cadastrarAtendimentoPaciente(): void
    {
        $idFichaMedica = null;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['msg_e'] = 'Requisição inválida para cadastro de atendimento.';
            header('Location: ../html/saude/profile_paciente.php');
            exit();
        }

        try {
            $idFichaMedica = filter_input(INPUT_POST, 'id_fichamedica', FILTER_VALIDATE_INT);
            $idMedicoBruto = isset($_POST['medicos']) ? trim((string)$_POST['medicos']) : '';
            if ($idMedicoBruto === '') {
                throw new InvalidArgumentException('Selecione um médico. Se necessário, escolha "Sem médico definido".', 400);
            }
            $idMedico = filter_var(
                $idMedicoBruto,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 0]]
            );
            $dataAtendimento = trim((string)($_POST['data_atendimento'] ?? ''));
            $descricao = trim((string)($_POST['texto'] ?? ''));
            $acervo = (string)($_POST['acervo'] ?? '');

            if (!$idFichaMedica || $idFichaMedica < 1) {
                throw new InvalidArgumentException('O id da ficha médica informado é inválido.', 400);
            }

            if ($idMedico === false) {
                throw new InvalidArgumentException('O médico informado é inválido.', 400);
            }

            $descricaoSemHtml = trim(strip_tags(html_entity_decode($descricao)));
            if ($descricaoSemHtml === '') {
                throw new InvalidArgumentException('A descrição do atendimento é obrigatória.', 400);
            }

            if (!isset($_SESSION['id_pessoa'])) {
                throw new LogicException('Operação negada: Cliente não autorizado', 401);
            }

            $idFuncionario = $this->atendimentoPacienteDAO->obterIdFuncionarioPorPessoaId((int)$_SESSION['id_pessoa']);
            if (!$idFuncionario) {
                throw new RuntimeException('Funcionário não encontrado.', 404);
            }

            $this->validarDataAtendimento($idFichaMedica, $dataAtendimento);
            $medicacoes = $this->normalizarMedicacoes($acervo);
            $this->validarMedicacoes($medicacoes);

            $dataRegistro = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))
                ->format('Y-m-d');

            $idAtendimento = $this->atendimentoPacienteDAO->inserirAtendimentoComMedicacoes(
                $idFichaMedica,
                $idFuncionario,
                $dataAtendimento,
                $descricao,
                $idMedico,
                $dataRegistro,
                $medicacoes
            );

            if (!$idAtendimento || $idAtendimento < 1) {
                throw new RuntimeException('Não foi possível cadastrar o atendimento e as medicações.', 500);
            }

            $_SESSION['msg'] = 'Atendimento cadastrado com sucesso.';
            header('Location: ../html/saude/profile_paciente.php?id_fichamedica=' . $idFichaMedica);
            exit();
        } catch (Exception $e) {
            $codigo = ($e->getCode() >= 400 && $e->getCode() < 600) ? (int)$e->getCode() : 500;
            if ($codigo >= 500) {
                error_log(
                    '[AtendimentoPacienteControle] Erro interno ao cadastrar atendimento. ' .
                    'Mensagem: ' . $e->getMessage() .
                    ' Arquivo: ' . $e->getFile() .
                    ' Linha: ' . $e->getLine()
                );
                $_SESSION['msg_e'] = 'Não foi possível cadastrar o atendimento. Tente novamente.';
            } else {
                $_SESSION['msg_e'] = $e->getMessage();
            }

            $url = '../html/saude/profile_paciente.php';
            if (is_numeric($idFichaMedica) && (int)$idFichaMedica > 0) {
                $url .= '?id_fichamedica=' . (int)$idFichaMedica;
            }

            header('Location: ' . $url);
            exit();
        }
    }

    private function normalizarMedicacoes(string $acervo): array
    {
        if ($acervo === '' || strtolower(trim($acervo)) === 'null') {
            return [];
        }

        $medicacoes = json_decode($acervo, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($medicacoes)) {
            throw new InvalidArgumentException('Formato inválido para a lista de medicações.', 400);
        }

        return $medicacoes;
    }

    private function validarDataAtendimento(int $idFichaMedica, string $dataAtendimento): void
    {
        if ($dataAtendimento === '') {
            throw new InvalidArgumentException('Data de atendimento inválida.', 400);
        }

        $idPaciente = $this->atendidoDAO->obterPessoaIdPorFichaMedica($idFichaMedica);
        if (!$idPaciente) {
            throw new RuntimeException('Paciente não encontrado.', 404);
        }

        $dataNascimentoPaciente = $this->atendidoDAO->obterDataNascimentoPorPessoaId($idPaciente) ?: '1900-01-01';
        $timezone = new DateTimeZone('America/Sao_Paulo');

        $dataAtendimentoObj = DateTime::createFromFormat('Y-m-d', $dataAtendimento, $timezone);
        if (!$dataAtendimentoObj) {
            throw new InvalidArgumentException('Data de atendimento inválida.', 400);
        }
        $dataAtendimentoObj->setTime(0, 0, 0);

        $dataNascimentoObj = DateTime::createFromFormat('Y-m-d', $dataNascimentoPaciente, $timezone);
        if (!$dataNascimentoObj) {
            $dataNascimentoObj = new DateTime('1900-01-01', $timezone);
        }
        $dataNascimentoObj->setTime(0, 0, 0);

        $dataAtualObj = new DateTime('now', $timezone);
        $dataAtualObj->setTime(0, 0, 0);

        if ($dataAtendimentoObj < $dataNascimentoObj) {
            throw new InvalidArgumentException('Data inválida: não pode ser anterior à data de nascimento.', 400);
        }

        if ($dataAtendimentoObj > $dataAtualObj) {
            throw new InvalidArgumentException('A data do atendimento não pode ser no futuro.', 400);
        }
    }

    private function validarMedicacoes(array $medicacoes): void
    {
        foreach ($medicacoes as $medicacao) {
            if (!is_array($medicacao)) {
                throw new InvalidArgumentException('Formato de medicação inválido.', 400);
            }

            $medicamento = trim((string)($medicacao['nome_medicacao'] ?? ''));
            $dosagem = trim((string)($medicacao['dosagem'] ?? ''));
            $horario = trim((string)($medicacao['horario'] ?? ''));
            $duracao = trim((string)($medicacao['tempo'] ?? ''));

            if ($medicamento === '' || $dosagem === '' || $horario === '' || $duracao === '') {
                throw new InvalidArgumentException('Campos da medicação estão incompletos.', 400);
            }
        }
    }
}
