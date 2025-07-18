<?php

    $config_path = "config.php";
    if (file_exists($config_path)) {
        require_once($config_path);
    } else {
        while (true) {
            $config_path = "../" . $config_path;
            if (file_exists($config_path)) break;
        }
        require_once($config_path);
    }

    require_once ROOT . '/dao/MedicamentoPacienteDAO.php';

    class MedicamentoPacienteControle
    {
        public function inserirAplicacao()
        {
            header('Content-Type: application/json');
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados) {
                echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos"]);
                exit;
            }

            $id_medicacao = $dados['id_medicacao'] ?? null;
            $id_pessoa = $dados['id_pessoa'] ?? null;
            $aplicacao = $dados['dataHora'] ?? null;
            $id_funcionario = $dados['id_funcionario'] ?? null;

            if (!$id_medicacao || !$id_pessoa || !$aplicacao || !$id_funcionario) {
                echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios ausentes"]);
                exit;
            }
             date_default_timezone_set('America/Sao_Paulo');
            $registro = date('Y-m-d H:i:s');

            try{
                $MedicamentosPacienteDAO = new MedicamentoPacienteDAO;
                $resposta = $MedicamentosPacienteDAO->inserirAplicacao($registro, $aplicacao, $id_funcionario, $id_pessoa, $id_medicacao);
                if($resposta === true){
                    echo json_encode([
                        "status" => "sucesso",
                        "mensagem" => "Aplicação registrada com sucesso"
                    ]);
                } else{
                    echo json_encode([
                        "status" => "erro",
                        "mensagem" => "Erro ao registrar aplicação: " . $resposta
                    ]);
                }
            } catch (Exception $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' => $e->getMessage()]);
            }
        }

        public function listarMedicamentosAplicadosPorIdDaFichaMedica(){
            header('Content-Type: application/json');
            try{
                $id = $_GET['id_fichamedica'];

                $MedicamentosPacienteDAO = new MedicamentoPacienteDAO();
                $aplicacoes = $MedicamentosPacienteDAO->listarMedicamentosPorIdDaFichaMedica($id);

                foreach($aplicacoes as $key => $value){
                    $data = new DateTime($value['aplicacao']);
                    $medaplicadas[$key]['aplicacao'] = $data->format('d/m/Y H:i:s');
                }

                echo json_encode($aplicacoes);
            } catch (Exception $e) {
                http_response_code($e->getCode());
                echo json_encode(['erro' => $e->getMessage()]);
            }
            
        }
    }
