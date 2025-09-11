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

    require_once ROOT . '/dao/ExameDAO.php';

    class ExameControle
    {
        public function inserirTipoExame()
        {
            header('Content-Type: application/json');
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$dados) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Dados inválidos"]);
                exit;
            }

            $exame = $dados['exame'] ?? null;

            if (!$exame) {
                http_response_code(400);
                echo json_encode(["status" => "erro", "mensagem" => "Campos obrigatórios ausentes"]);
                exit;
            }

            try{
                $ExameDAO = new ExameDAO;
                $resposta = $ExameDAO->inserirTipoExame($exame);
                if($resposta === true){
                    http_response_code(201);
                    echo json_encode([
                        "status" => "sucesso",
                        "mensagem" => "Tipo de exame registrado com sucesso"
                    ]);
                } else{
                    http_response_code(400);
                    echo json_encode([
                        "status" => "erro",
                        "mensagem" => "Erro de banco de dados ao registrar tipo de exame"
                    ]);
                }
            } catch (Exception $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' => "Erro de servidor ao inserir tipo de exame"]);
            }
        }

        public function listarTodosTiposDeExame(){
            header('Content-Type: application/json');
            try{
                $ExameDAO = new ExameDAO;
                $resposta = $ExameDAO->listarTodosTiposDeExame();
                if($resposta){
                    echo json_encode($resposta);
                }
            }catch (PDOException $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' =>"Erro de Banco de Dados ao listar tipos de exame"]);
            }
            
            catch (Exception $e) {
                http_response_code($e->getCode());
                echo json_encode(['erro' => "Erro de servidor ao listar tipos de exame"]);
            }
        }

        public function listarExamesPorId(){
            header("Content-Type: application/json");

            $id_fichamedica = isset($_GET['id_fichamedica']) ? $_GET['id_fichamedica'] : null;

            try{
                $ExameDAO = new ExameDAO;
                $resposta = $ExameDAO->listarExamesPorId($id_fichamedica);
                if($resposta){
                    foreach ($resposta as $key => $value) {
                        $resposta[$key]["arquivo"] = gzuncompress($value["arquivo"]);

                        $data = new DateTime($value['data']);
                        $resposta[$key]['data'] = $data->format('d/m/Y');
                    }
                    echo json_encode($resposta);
                }
            }catch(PDOException $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' =>"Erro de Banco de Dados ao listar exames"]);
            }catch (Exception $e) {
                http_response_code($e->getCode());
                echo json_encode(['erro' => "Erro de servidor ao listar exames"]);
            }
        }

        public function inserirExame() {
            if (!isset($_FILES["arquivo"]) || $_FILES["arquivo"]["error"] === UPLOAD_ERR_NO_FILE) {
                http_response_code(400);
                echo json_encode(["erro" => "É necessário enviar um arquivo"]);
                exit;
            }

            $arquivo = $_FILES["arquivo"];
            $id_fichamedica = htmlspecialchars($_POST["id_fichamedica"] ?? '');
            $tipoDocumento = htmlspecialchars($_POST["tipoDocumento"] ?? '');

            if (!$id_fichamedica || !$tipoDocumento) {
                http_response_code(400);
                echo json_encode(["erro" => "Campos obrigatórios ausentes"]);
                exit;
            }

            $dataExame = date('Y/m/d');
            $extensoes_permitidas = ['jpg','jpeg','png','pdf'];

            if ($arquivo['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(["erro" => "Erro no upload do arquivo. Código: " . $arquivo['error']]);
                exit;
            }

            $arquivo_nome = htmlspecialchars($arquivo["name"]);
            $extensao_nome = strtolower(pathinfo($arquivo_nome, PATHINFO_EXTENSION));

            if (!in_array($extensao_nome, $extensoes_permitidas)) {
                http_response_code(400);
                echo json_encode(["erro" => "Formato inválido. Permitidos: " . implode(", ", $extensoes_permitidas)]);
                exit;
            }

            $arquivo_b64 = base64_encode(file_get_contents($arquivo['tmp_name']));

            try {
                $exameDAO = new ExameDAO();
                $resposta = $exameDAO->inserirExame(
                    $id_fichamedica,
                    $tipoDocumento,
                    $dataExame,
                    $arquivo_nome,
                    $extensao_nome,
                    $arquivo_b64
                );

                if ($resposta) {
                    http_response_code(201);
                    echo json_encode([
                        "status" => "sucesso",
                        "mensagem" => "Exame registrado com sucesso"
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        "status" => "erro",
                        "mensagem" => "Erro de banco de dados ao registrar exame"
                    ]);
                }

            } catch (Exception $e) {
                http_response_code($e->getCode() ?: 500);
                echo json_encode(['erro' => "Erro de servidor ao inserir exame"]);
            }

            exit;
        }

        public function removerExame(){
            $id_exame = isset($_GET['id_exame']) ? $_GET['id_exame'] : null;

            if(!$id_exame || !isset($id_exame)){
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(["erro" => "Campos obrigatórios ausentes"]);
                exit;
            }
            try{
                $ExameDAO = new ExameDAO;
                $resposta = $ExameDAO->removerExame($id_exame);
                if($resposta === true){
                    http_response_code(204);
                } else{
                    http_response_code(400);
                    echo json_encode([
                        "status" => "erro",
                        "mensagem" => "Erro de banco de dados ao remover um exame"
                    ]);
                }
            } catch (Exception $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' => "Erro de servidor ao remover um exame"]);
            }
            exit;
        }

        public function retornaArquivoPorId(){
            $id_exame = isset($_GET['id_exame']) ? $_GET['id_exame'] : null;


            if(!$id_exame || !isset($id_exame)){
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(["erro" => "Campos obrigatórios ausentes"]);
                exit;
            }

            define("TYPEOF_EXTENSION", [
                'jpg' => 'image/jpg',
                'png' => 'image/png',
                'jpeg' => 'image/jpeg',
                'pdf' => 'application/pdf',
                'docx' => 'application/docx',
                'doc' => 'application/doc',
                'odp' => 'application/odp',
            ]);

            try{
                $ExameDAO = new ExameDAO;
                $resposta = $ExameDAO->retornaArquivoPorId($id_exame);
                if($resposta){
                    $arquivo_b64 = gzuncompress($resposta[0]["arquivo"]);
                    $dados = [
                        "arquivo_nome" => $resposta[0]["arquivo_nome"],
                        "arquivo_extensao" => $resposta[0]["arquivo_extensao"],
                        "arquivo" => $arquivo_b64
                    ];
                    header("Content-Type: ".TYPEOF_EXTENSION[$dados["arquivo_extensao"]]);
                    header("Content-Disposition: attachment; filename=\"".$dados["arquivo_nome"]."\"");
                    ob_clean();
                    flush();

                    echo base64_decode($dados["arquivo"]);
                }
            }catch (Exception $e){
                http_response_code($e->getCode());
                echo json_encode(['erro' => "Erro ao retornar um arquivo"]);
            }
            exit;
        }
    }
