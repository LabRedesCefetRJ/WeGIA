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

    class MedicoControle
    {
        public function inserirMedico()
        {
            header('Content-Type: application/json');
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados) {
                echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos"]);
                exit;
            }

            $crm = $dados['crm'] ?? null;
            $nome = $dados['nome'] ?? null;

            if (!$crm || !$nome) {
                echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios ausentes"]);
                exit;
            }

            try{
                $MedicoDAO = new MedicoDAO;
                $resposta = $MedicoDAO->inserirMedico($crm, $nome);
                if($resposta === true){
                    echo json_encode([
                        "status" => "sucesso",
                        "mensagem" => "Médico registrado com sucesso"
                    ]);
                } else{
                    echo json_encode([
                        "status" => "erro",
                        "mensagem" => "Erro ao registrar Médico: " . $resposta
                    ]);
                }
            } catch (Exception $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' => $e->getMessage()]);
            }
        }

        public function listarTodosOsMedicos(){
            header('Content-Type: application/json');
            try{
                $MedicoDAO = new MedicoDAO();
                $medicos = $MedicoDAO->listarTodosOsMedicos();
                
                echo json_encode($medicos);
                exit;
            } catch(PDOException $e){
                http_response_code(500);
                echo json_encode(['erro' => "Erro ao Buscar Médicos no Banco de Dados"]);
                exit;

            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(['erro' => "Erro interno do Servidor"]);
                exit;
            }
        }
    }
