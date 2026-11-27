<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Conexao.php';

class AtendimentoPacienteDAO
{
    private PDO $pdo;
    private ?array $colunasSaudeAtendimentoCache = null;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Conexao::connect();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function obterIdFuncionarioPorPessoaId(int $idPessoa): ?int
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id_funcionario FROM funcionario WHERE id_pessoa = :id_pessoa LIMIT 1'
            );
            $stmt->bindValue(':id_pessoa', $idPessoa, PDO::PARAM_INT);
            $stmt->execute();

            $idFuncionario = $stmt->fetchColumn();

            return $idFuncionario ? (int)$idFuncionario : null;
        } catch (Throwable $e) {
            $this->registrarErro('Erro ao buscar id_funcionario por id_pessoa.', $e);
            return null;
        }
    }

    public function inserirAtendimentoComMedicacoes(
        int $idFichaMedica,
        int $idFuncionario,
        string $dataAtendimento,
        string $descricao,
        int $idMedico,
        string $dataRegistro,
        array $medicacoes
    ): int {
        $this->pdo->beginTransaction();

        try {
            $colunasDisponiveis = $this->obterColunasSaudeAtendimento();

            $campos = [
                'id_fichamedica',
                'id_funcionario',
                'data_atendimento',
                'descricao',
                'id_medico',
                'data_registro'
            ];

            $placeholders = [
                ':id_fichamedica',
                ':id_funcionario',
                ':data_atendimento',
                ':descricao',
                ':id_medico',
                ':data_registro'
            ];

            $parametros = [
                ':id_fichamedica' => [$idFichaMedica, PDO::PARAM_INT],
                ':id_funcionario' => [$idFuncionario, PDO::PARAM_INT],
                ':data_atendimento' => [$dataAtendimento, PDO::PARAM_STR],
                ':descricao' => [$descricao, PDO::PARAM_STR],
                ':id_medico' => [$idMedico, PDO::PARAM_INT],
                ':data_registro' => [$dataRegistro, PDO::PARAM_STR]
            ];

            // Compatibilidade com esquemas que já possuem campos de anulação.
            if (in_array('anulado', $colunasDisponiveis, true)) {
                $campos[] = 'anulado';
                $placeholders[] = ':anulado';
                $parametros[':anulado'] = [0, PDO::PARAM_INT];
            }

            if (in_array('data_anulacao', $colunasDisponiveis, true)) {
                $campos[] = 'data_anulacao';
                $placeholders[] = ':data_anulacao';
                $parametros[':data_anulacao'] = [null, PDO::PARAM_NULL];
            }

            if (in_array('motivo_anulacao', $colunasDisponiveis, true)) {
                $campos[] = 'motivo_anulacao';
                $placeholders[] = ':motivo_anulacao';
                $parametros[':motivo_anulacao'] = [null, PDO::PARAM_NULL];
            }

            if (in_array('id_funcionario_anulacao', $colunasDisponiveis, true)) {
                $campos[] = 'id_funcionario_anulacao';
                $placeholders[] = ':id_funcionario_anulacao';
                $parametros[':id_funcionario_anulacao'] = [null, PDO::PARAM_NULL];
            }

            $stmtAtendimento = $this->pdo->prepare(
                'INSERT INTO saude_atendimento (' . implode(', ', $campos) . ')
                 VALUES (' . implode(', ', $placeholders) . ')'
            );

            foreach ($parametros as $placeholder => [$valor, $tipo]) {
                $stmtAtendimento->bindValue($placeholder, $valor, $tipo);
            }

            $stmtAtendimento->execute();

            $idAtendimento = (int)$this->pdo->lastInsertId();

            if ($idAtendimento < 1) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                $this->registrarErro('Falha ao obter id_atendimento após inserção.', null);
                return 0;
            }

            if (!empty($medicacoes)) {
                $stmtMedicacao = $this->pdo->prepare(
                    'INSERT INTO saude_medicacao (id_atendimento, id_tipo_medicacao, medicamento, dosagem, duracao, id_tipo_uso, data_termino_medicacao, saude_medicacao_status_idsaude_medicacao_status) VALUES (
                        :id_atendimento,
                        :id_tipo_medicacao,
                        :medicamento,
                        :dosagem,
                        :duracao,
                        :id_tipo_uso,
                        :data_termino,
                        :status
                    )'
                );

                $stmtHorario = $this->pdo->prepare(
                    'INSERT INTO saude_medicacao_horario (id_medicacao, horario) VALUES (:id_medicacao, :horario)'
                );

                foreach ($medicacoes as $medicacao) {
                    if (!is_array($medicacao)) {
                        throw new InvalidArgumentException('Formato de medicação inválido.');
                    }

                    $idTipoMedicacao = (int)($medicacao['id_tipo_medicacao'] ?? $medicacao['tipo_medicacao'] ?? 0);
                    $medicamento = trim((string)($medicacao['nome_medicacao'] ?? ''));
                    $dosagem = trim((string)($medicacao['dosagem'] ?? ''));

                    $duracao = trim((string)($medicacao['tempo'] ?? ''));
                    $idTipoUso = $medicacao['id_tipo_uso'] ?? $medicacao['tipo_uso'] ?? null;
                    $dataTermino = trim((string)($medicacao['data_termino'] ?? ''));

                    $horarios = $medicacao['horarios'] ?? [];

                    if (is_string($horarios)) {
                        $horarios = array_filter(
                            array_map('trim', explode(',', $horarios))
                        );
                    }

                    if (!is_array($horarios)) {
                        throw new InvalidArgumentException('Formato de horários inválido.');
                    }

                    // Toda medicação precisa ter tipo, nome e dosagem.
                    if (!in_array($idTipoMedicacao, [1, 2], true) || $medicamento === '' || $dosagem === '') {
                            throw new InvalidArgumentException('Dados básicos da medicação incompletos.');
                    }

                    // Tipo SOS = 1, não utiliza os campos comuns.
                    if ($idTipoMedicacao === 1) {
                        $duracao = '';
                        $idTipoUso = null;
                        $dataTermino = '';
                        $horarios = [];
                    } else {
                        $idTipoUso = filter_var($idTipoUso, FILTER_VALIDATE_INT);

                        if ($idTipoUso === false || !in_array($idTipoUso, [1, 2], true) || empty($horarios)) {
                            throw new InvalidArgumentException('Campos da medicação prescrita incompletos.');
                        }

                        if ($idTipoUso === 2 && $dataTermino === '') {
                            throw new InvalidArgumentException('Informe a data de término da medicação temporária.');
                        }

                        if ($idTipoUso === 1) {
                            $dataTermino = '';
                        }
                    }

                    // Converte a data para o formato DATETIME.
                    if ($dataTermino !== null && $dataTermino !== '') {
                        $data = DateTime::createFromFormat('!Y-m-d', $dataTermino);

                        if (!$data || $data->format('Y-m-d') !== $dataTermino) {
                            throw new InvalidArgumentException('Data de término inválida.');
                        }
                        $dataTermino .= ' 00:00:00';
                    }

                    $stmtMedicacao->bindValue(':id_atendimento', $idAtendimento, PDO::PARAM_INT);
                    $stmtMedicacao->bindValue(':id_tipo_medicacao', $idTipoMedicacao, PDO::PARAM_INT);
                    $stmtMedicacao->bindValue(':medicamento', $medicamento, PDO::PARAM_STR);
                    $stmtMedicacao->bindValue(':dosagem', $dosagem, PDO::PARAM_STR);
                    $stmtMedicacao->bindValue(':duracao',
                        $duracao !== null && $duracao !== '' ? $duracao : null,
                        $duracao !== null && $duracao !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmtMedicacao->bindValue(':id_tipo_uso',
                        $idTipoUso !== null ? (int)$idTipoUso : null,
                        $idTipoUso !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmtMedicacao->bindValue(':data_termino',
                        $dataTermino !== '' ? $dataTermino : null,
                        $dataTermino !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmtMedicacao->bindValue(':status', 1, PDO::PARAM_INT);

                    $stmtMedicacao->execute();

                    $idMedicacao = (int)$this->pdo->lastInsertId();

                    if ($idMedicacao < 1) {
                        throw new RuntimeException('Não foi possível obter o ID da medicação cadastrada.');
                    }

                    foreach ($horarios as $horario) {
                        $horario = trim((string)$horario);

                        if ($horario !== '') {
                            $stmtHorario->execute([':id_medicacao' => $idMedicacao, ':horario' => $horario]);
                        }
                    }
                }
            }


            $this->pdo->commit();

            return $idAtendimento;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->registrarErro('Erro ao inserir atendimento e medicações.', $e);

            // Erros de validação são do usuário: devem chegar ao controller.
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            return 0;
        }
    }

    private function obterColunasSaudeAtendimento(): array
    {
        if ($this->colunasSaudeAtendimentoCache !== null) {
            return $this->colunasSaudeAtendimentoCache;
        }

        try {
            $stmt = $this->pdo->query('SHOW COLUMNS FROM saude_atendimento');
            $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->colunasSaudeAtendimentoCache = array_map(
                static fn(array $coluna): string => (string)($coluna['Field'] ?? ''),
                $colunas
            );

            return $this->colunasSaudeAtendimentoCache;
        } catch (Throwable $e) {
            $this->registrarErro('Erro ao consultar colunas da tabela saude_atendimento.', $e);
            $this->colunasSaudeAtendimentoCache = [];
            return $this->colunasSaudeAtendimentoCache;
        }
    }

    private function registrarErro(string $contexto, ?Throwable $erro): void
    {
        if ($erro instanceof Throwable) {
            error_log(
                '[AtendimentoPacienteDAO] ' . $contexto .
                ' Mensagem: ' . $erro->getMessage() .
                ' Arquivo: ' . $erro->getFile() .
                ' Linha: ' . $erro->getLine()
            );
            return;
        }

        error_log('[AtendimentoPacienteDAO] ' . $contexto);
    }

    public function listarAtendimentosPorFichaMedica(int $idFichaMedica): array
    {
        $sql = "SELECT a.id_atendimento, a.descricao, a.data_atendimento, m.nome AS medicoNome, TRIM(CONCAT(COALESCE(p.nome, ''), ' ', COALESCE(p.sobrenome, ''))) AS registro, a.anulado, a.data_anulacao, a.motivo_anulacao, TRIM(CONCAT(COALESCE(pAnulador.nome, ''), ' ', COALESCE(pAnulador.sobrenome, ''))) AS anulado_por
        FROM saude_atendimento a JOIN funcionario f ON(a.id_funcionario = f.id_funcionario) JOIN pessoa p ON(p.id_pessoa = f.id_pessoa) JOIN saude_medicos m ON(a.id_medico = m.id_medico) LEFT JOIN funcionario fAnulador ON (a.id_funcionario_anulacao = fAnulador.id_funcionario) LEFT JOIN pessoa pAnulador ON(pAnulador.id_pessoa = fAnulador.id_pessoa)
        WHERE a.id_fichamedica = :idFichaMedica ORDER BY a.data_atendimento DESC, a.id_atendimento DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idFichaMedica', $idFichaMedica, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}