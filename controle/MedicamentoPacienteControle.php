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
    require_once ROOT . '/dao/FuncionarioDAO.php';


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
            $id_pessoa_funcionario = $dados['id_pessoa_funcionario'] ?? null;

            if (!$id_medicacao || !$id_pessoa || !$aplicacao || !$id_pessoa_funcionario) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios ausentes"]);
                exit;
            }
             date_default_timezone_set('America/Sao_Paulo');
            $registro = date('Y-m-d H:i:s');

            try{
                $FuncionarioDAO = new FuncionarioDAO;
                $id_funcionario = $FuncionarioDAO->getIdFuncionarioComIdPessoa($id_pessoa_funcionario);
                
                if(!$id_funcionario){
                    http_response_code(400);
                    echo json_encode([
                        "status" => "erro",
                        "mensagem" => "Erro ao registrar aplicação: Funcionário não encontrado."
                    ]);
                    exit;
                }

                $MedicamentosPacienteDAO = new MedicamentoPacienteDAO;
                $MedicamentosPacienteDAO->inserirAplicacao($registro, $aplicacao, $id_funcionario, $id_pessoa, $id_medicacao);
                
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Aplicação registrada com sucesso"
                ]);

            } catch (Exception $e){
                $codigo = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
                http_response_code($codigo);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => $e->getMessage()
                ]);
            }
            exit;
        }

        public function listarMedicamentosAplicadosPorIdDaFichaMedica(){
            header('Content-Type: application/json');
            try{
                $id = $_GET['id_fichamedica'] ?? null;
                if(empty($id)) {
                    throw new InvalidArgumentException("ID da ficha médica não fornecido.", 400);
                }

                $MedicamentosPacienteDAO = new MedicamentoPacienteDAO();
                $aplicacoes = $MedicamentosPacienteDAO->listarMedicamentosPorIdDaFichaMedica($id);

                foreach($aplicacoes as $key => $value){
                    $data = new DateTime($value['aplicacao']);
                    $aplicacoes[$key]['aplicacao_formatada'] = $data->format('d/m/Y H:i:s');
                }
                echo json_encode($aplicacoes); 

            } catch (Exception $e) {
                $codigo = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
                http_response_code($codigo);
                echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
            }
            exit;
        }

        public function cadastrarMedicacaoSOS()
        {
            header('Content-Type: application/json');
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Dados JSON inválidos"]);
                exit;
            }

            
            $id_pessoa_paciente = $dados['id_pessoa_paciente'] ?? null;
            $id_pessoa_funcionario = $dados['id_pessoa_funcionario'] ?? null; 
            $medicamento = $dados['medicamento'] ?? null;
            $dosagem = $dados['dosagem'] ?? null;
            $horario = $dados['horario'] ?? null;
            $duracao = $dados['duracao'] ?? null;
            $status_id = $dados['status_id'] ?? 1;

            $campos_obrigatorios = [
                'id_pessoa_paciente' => $id_pessoa_paciente,
                'id_pessoa_funcionario' => $id_pessoa_funcionario,
                'medicamento' => $medicamento
            ];

            foreach ($campos_obrigatorios as $campo => $valor) {
                if (empty($valor)) {
                    http_response_code(400);
                    echo json_encode(["status" => "erro", "mensagem" => "Campo obrigatório ausente: " . $campo]);
                    exit;
                }
            }

            $MedicamentosPacienteDAO = new MedicamentoPacienteDAO();
            $FuncionarioDAO = new FuncionarioDAO();

            try {
                $MedicamentosPacienteDAO->beginTransaction();

                $id_funcionario = $FuncionarioDAO->getIdFuncionarioComIdPessoa($id_pessoa_funcionario);
                if (!$id_funcionario) {
                    throw new Exception("Funcionário (pessoa ID: $id_pessoa_funcionario) não encontrado.", 404);
                }

                $novo_id_atendimento = $MedicamentosPacienteDAO->criarAtendimentoAvulso(
                    $id_funcionario, 
                    $id_pessoa_paciente
                );
                
                if (!is_numeric($novo_id_atendimento)) {
                    throw new Exception("Erro ao criar atendimento: " . $novo_id_atendimento, 500);
                }

                $sucesso_medicacao = $MedicamentosPacienteDAO->cadastrarMedicamentoSos(
                    $novo_id_atendimento,
                    $medicamento,
                    $dosagem,
                    $horario,
                    $duracao,
                    (int)$status_id
                );
                
                if ($sucesso_medicacao !== true) {
                    throw new Exception("Erro ao cadastrar medicação: " . $sucesso_medicacao, 500);
                }

                $MedicamentosPacienteDAO->commit();

                http_response_code(201); 
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Medicação SOS registrada com sucesso",
                    "id_atendimento_criado" => $novo_id_atendimento
                ]);

            } catch (Exception $e) {
                $MedicamentosPacienteDAO->rollBack();
                
                $codigo = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
                http_response_code($codigo);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => $e->getMessage()
                ]);
            }
            exit;
        }
    }