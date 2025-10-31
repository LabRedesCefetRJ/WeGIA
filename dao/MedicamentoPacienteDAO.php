<?php

require_once 'Conexao.php';

class MedicamentoPacienteDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    public function inserirAplicacao($registro, $aplicacao, $id_funcionario, $id_pessoa, $id_medicacao)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO saude_medicamento_administracao (
                    aplicacao, registro, saude_medicacao_id_medicacao, 
                    pessoa_id_pessoa, funcionario_id_funcionario
                ) VALUES (
                    :aplicacao, :registro, :saude_medicacao_id_medicacao, 
                    :pessoa_id_pessoa, :funcionario_id_funcionario
                )
            ");

            $stmt->execute([
                ':aplicacao' => $aplicacao,
                ':registro' => $registro,
                ':saude_medicacao_id_medicacao' => $id_medicacao,
                ':pessoa_id_pessoa' => $id_pessoa,
                ':funcionario_id_funcionario' => $id_funcionario
            ]);

            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    public function listarMedicamentosPorIdDaFichaMedica($id)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT medicamento, aplicacao, p.nome as nomeFuncionario 
                FROM saude_medicacao sm 
                JOIN saude_medicamento_administracao sa ON (sm.id_medicacao = sa.saude_medicacao_id_medicacao) 
                JOIN saude_atendimento saa ON (saa.id_atendimento = sm.id_atendimento)
                JOIN funcionario f ON (sa.funcionario_id_funcionario = f.id_funcionario) 
                JOIN pessoa p ON (p.id_pessoa = f.id_pessoa) 
                WHERE saa.id_fichamedica = :id 
                ORDER BY aplicacao DESC
            ");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $medaplicadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $medaplicadas;
        } catch (PDOException $e) {
            echo 'Erro ao procurar uma intercorrência com o id da ficha médica fornecida: ' . $e->getMessage();
        }
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    private function getOrCreateFichaMedicaId($id_pessoa_paciente)
    {
        try {

            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO saude_fichamedica (id_pessoa) VALUES (:id_pessoa)
            ");
            $stmt->execute([':id_pessoa' => $id_pessoa_paciente]);

            $stmt = $this->pdo->prepare("
                SELECT id_fichamedica FROM saude_fichamedica WHERE id_pessoa = :id_pessoa
            ");
            $stmt->execute([':id_pessoa' => $id_pessoa_paciente]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && isset($result['id_fichamedica'])) {
                return (int)$result['id_fichamedica'];
            } else {
                throw new Exception("Não foi possível encontrar ou criar a ficha médica para a pessoa ID: " . $id_pessoa_paciente);
            }
        } catch (PDOException $e) {
            throw new Exception("Erro de BD no getOrCreateFichaMedicaId: " . $e->getMessage());
        }
    }

    public function criarAtendimentoAvulso($id_funcionario_logado, $id_pessoa_paciente)
    {

        $ID_MEDICO_PADRAO = 0;
        $DESCRICAO_PADRAO = "Atendimento avulso para registro de medicação SOS.";

        try {
            $id_fichamedica = $this->getOrCreateFichaMedicaId($id_pessoa_paciente);
            $stmt = $this->pdo->prepare("
                INSERT INTO saude_atendimento (
                    id_fichamedica, id_funcionario, id_medico,
                    data_registro, data_atendimento, descricao
                ) VALUES (
                    :id_fichamedica, :id_funcionario, :id_medico,
                    CURDATE(), CURDATE(), :descricao
                )
            ");

            $stmt->execute([
                ':id_fichamedica' => $id_fichamedica,
                ':id_funcionario' => $id_funcionario_logado,
                ':id_medico'      => $ID_MEDICO_PADRAO,
                ':descricao'      => $DESCRICAO_PADRAO
            ]);

            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function cadastrarMedicamentoSos($id_atendimento, $medicamento, $dosagem, $horario, $duracao, $status_id)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO saude_medicacao (
                    id_atendimento, medicamento, dosagem, horario, duracao,
                    saude_medicacao_status_idsaude_medicacao_status
                ) VALUES (
                    :id_atendimento, :medicamento, :dosagem, :horario, :duracao, :status_id
                )
            ");
            $stmt->execute([
                ':id_atendimento' => $id_atendimento,
                ':medicamento'    => $medicamento,
                ':dosagem'        => $dosagem,
                ':horario'        => $horario,
                ':duracao'        => $duracao,
                ':status_id'      => $status_id
            ]);
            return true; // Sucesso
        } catch (PDOException $e) {
            throw new Exception("Erro de BD ao cadastrar medicação SOS: " . $e->getMessage());
        }
    }
}
