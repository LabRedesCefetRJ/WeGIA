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
                    'INSERT INTO saude_medicacao (
                        id_atendimento,
                        medicamento,
                        dosagem,
                        horario,
                        duracao,
                        saude_medicacao_status_idsaude_medicacao_status
                    ) VALUES (
                        :id_atendimento,
                        :medicamento,
                        :dosagem,
                        :horario,
                        :duracao,
                        :status
                    )'
                );

                foreach ($medicacoes as $medicacao) {
                    if (!is_array($medicacao)) {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $this->registrarErro('Formato de medicação inválido: item não é array.', null);
                        return 0;
                    }

                    $medicamento = trim((string)($medicacao['nome_medicacao'] ?? ''));
                    $dosagem = trim((string)($medicacao['dosagem'] ?? ''));
                    $horario = trim((string)($medicacao['horario'] ?? ''));
                    $duracao = trim((string)($medicacao['tempo'] ?? ''));

                    if ($medicamento === '' || $dosagem === '' || $horario === '' || $duracao === '') {
                        if ($this->pdo->inTransaction()) {
                            $this->pdo->rollBack();
                        }
                        $this->registrarErro('Campos de medicação incompletos durante inserção.', null);
                        return 0;
                    }

                    $stmtMedicacao->bindValue(':id_atendimento', $idAtendimento, PDO::PARAM_INT);
                    $stmtMedicacao->bindValue(':medicamento', $medicamento);
                    $stmtMedicacao->bindValue(':dosagem', $dosagem);
                    $stmtMedicacao->bindValue(':horario', $horario);
                    $stmtMedicacao->bindValue(':duracao', $duracao);
                    $stmtMedicacao->bindValue(':status', 1, PDO::PARAM_INT);
                    $stmtMedicacao->execute();
                }
            }

            $this->pdo->commit();

            return $idAtendimento;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $this->registrarErro('Erro ao inserir atendimento e medicações.', $e);
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
}
