<?php
require_once dirname(__DIR__) . '/dao/Conexao.php';
require_once dirname(__DIR__) . '/dao/EnfermidadeDAO.php';

class EnfermidadeControle{
    private PDO $pdo;

    public function __construct(PDO $pdo = null)
    {
        if(!is_null($pdo)){
            $this->pdo = $pdo;
        }else{
            $this->pdo = Conexao::connect();
        }
    }

    public function getEnfermidadesAtivasPorFichaMedica(){
        $idFichaMedica = trim(filter_input(INPUT_GET, 'id_fichamedica', FILTER_SANITIZE_NUMBER_INT));

        if(!$idFichaMedica || $idFichaMedica < 1){
            http_response_code(400);
            echo json_encode(['erro' => 'O parâmetro informado não possuí valor válido.']);
            exit();
        }

        try{
            $enfermidadeDao = new EnfermidadeDAO($this->pdo);
            $enfermidades = $enfermidadeDao->getEnfermidadesAtivasPorFichaMedica($idFichaMedica);

            echo json_encode($enfermidades);
        }catch(PDOException $e){
            http_response_code(500);
            echo json_encode(['erro' => 'Problema no servidor ao buscar enfermidades']);
        }
    }
}