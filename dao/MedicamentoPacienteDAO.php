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

     public function listarMedicamentosPorIdDaFichaMedica($id){
        try{
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
        } catch (PDOException $e){
            echo 'Erro ao procurar uma intercorrência com o id da ficha médica fornecida: '.$e->getMessage();
        }
        
    }
}
