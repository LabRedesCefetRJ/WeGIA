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

            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

     public function listarTodosOsMedicos(){
        try{
            $stmt = $this->pdo->prepare("
                SELECT id_medico, nome
                FROM saude_medicos 
                ORDER BY nome ASC
            ");
            $stmt->execute();
            $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $medicos;
        } catch (PDOException $e){
            throw new PDOException('Erro ao procurar os médicos: ' . $e->getMessage(), 500);
        }
    }
}
