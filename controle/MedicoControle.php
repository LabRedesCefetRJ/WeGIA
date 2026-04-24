<?php

    $config_path = "config.php";
    if (file_exists($config_path)) {
        require_once($config_path);
    } else {
        $dir = __DIR__;
        for($i=0; $i <= 10; $i++){
            $path = $dir . "/" . $config_path;
            if(file_exists($path)){
                $config_path = $path;
                break;
            }
            $dir = dirname($dir);
        }
        require_once($config_path);
    }

    require_once ROOT . '/dao/MedicoDAO.php';
    require_once ROOT . '/classes/Util.php';

    class MedicoControle
    {
        public function inserirMedico()
        {
            header('Content-Type: application/json');
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos"]);
                exit;
            }

            $crm = $dados['crm'] ?? null;
            $nome = $dados['nome'] ?? null;

            if (!$crm || !$nome) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios ausentes"]);
                exit;
            }

            try {
                $MedicoDAO = new MedicoDAO();
                $resposta = $MedicoDAO->inserirMedico($crm, $nome);

                http_response_code(201);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Médico registrado com sucesso",
                    "medico" => $resposta
                ]);
                exit;
            } catch (Throwable $e) {
                Util::tratarException($e);
            }
        }

        public function listarTodosOsMedicos(){
            header('Content-Type: application/json');
            try {
                $MedicoDAO = new MedicoDAO();
                $medicos = $MedicoDAO->listarTodosOsMedicos();

                echo json_encode($medicos);
                exit;
            } catch (Throwable $e) {
                Util::tratarException($e);
            }
        }
    }
