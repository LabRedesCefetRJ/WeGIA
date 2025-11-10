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
                http_response_code(400);
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
                    throw new Exception("Funcionário não encontrado. ID pessoa: $id_pessoa_funcionario", 404);
                }

                $MedicamentosPacienteDAO = new MedicamentoPacienteDAO;
                $MedicamentosPacienteDAO->inserirAplicacao($registro, $aplicacao, $id_funcionario, $id_pessoa, $id_medicacao);
                
                http_response_code(200);
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Aplicação registrada com sucesso"
                ]);

            } catch (InvalidArgumentException $e) {
                http_response_code(400);
                echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
            } catch (PDOException $e){
                // Erro de Banco de Dados
                http_response_code(500); 
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => "Erro ao registrar aplicação (BD): " . $e->getMessage()
                ]);
            } catch (Exception $e){
                // Erro de lógica ou outro tipo de exceção
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

            } catch (InvalidArgumentException $e) {
                http_response_code(400);
                echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
            } catch (PDOException $e){
                // Erro de Banco de Dados
                http_response_code(500);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => 'Erro ao listar medicamentos aplicados (BD): ' . $e->getMessage()
                ]);
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
                
                if (!is_numeric($novo_id_atendimento) || $novo_id_atendimento <= 0) {
                    throw new Exception("Falha ao obter ID do atendimento recém-criado.", 500);
                }

                $MedicamentosPacienteDAO->cadastrarMedicamentoSos(
                    $novo_id_atendimento,
                    $medicamento,
                    $dosagem,
                    $horario,
                    $duracao,
                    (int)$status_id
                );
                
                $MedicamentosPacienteDAO->commit();

                http_response_code(201); 
                echo json_encode([
                    "status" => "sucesso",
                    "mensagem" => "Medicação SOS registrada com sucesso",
                    "id_atendimento_criado" => $novo_id_atendimento
                ]);

            } catch (InvalidArgumentException $e) {
                $MedicamentosPacienteDAO->rollBack();
                http_response_code(400);
                echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
            } catch (PDOException $e) {
                $MedicamentosPacienteDAO->rollBack();
                // Erro de Banco de Dados
                http_response_code(500);
                echo json_encode([
                    'status' => 'erro',
                    'mensagem' => 'Erro de BD durante transação: ' . $e->getMessage()
                ]);
            } catch (Exception $e) {
                $MedicamentosPacienteDAO->rollBack();
                // Erro de lógica ou outro tipo de exceção
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