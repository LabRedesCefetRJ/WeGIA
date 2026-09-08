<?php

require_once 'Conexao.php';

class MedicoDAO
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

     public function inserirMedico($crm, $nome)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO saude_medicos (
                crm, nome
                ) VALUES (
                :crm, :nome
                )
            ");

            $stmt->execute([
                ':crm' => $crm,
                ':nome' => $nome
            ]);

            return [
                'sucesso' => true,
                'id_medico' => (int)$this->pdo->lastInsertId(),
                'crm' => $crm,
                'nome' => $nome
            ];
        } catch (PDOException $e) {
            if ($e->getCode() === '22001') {
                return [
                    'sucesso' => false,
                    'erro' => 'Erro: O CRM ou o nome digitado excede o limite de caracteres permitido.'
                ];
            }
            return [
                'sucesso' => false,
                'erro' => 'Erro interno ao acessar o banco de dados.'
            ];
        }
    } 
    
     public function listarTodosOsMedicos(){
        $stmt = $this->pdo->prepare("
            SELECT id_medico, nome
            FROM saude_medicos 
            ORDER BY nome ASC
        ");
        $stmt->execute();
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $medicos;
    }
}
