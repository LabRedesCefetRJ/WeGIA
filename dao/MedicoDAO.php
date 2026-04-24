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
            'id_medico' => (int)$this->pdo->lastInsertId(),
            'crm' => $crm,
            'nome' => $nome
        ];
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
