<?php
require_once dirname(__DIR__) . '/dao/Conexao.php';
require_once dirname(__DIR__) . '/dao/EnfermidadeDAO.php';
require_once dirname(__DIR__) . '/dao/AtendidoDAO.php';
require_once dirname(__DIR__) . '/classes/Util.php';

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

    public function cadastrarEnfermidadeNaFichaMedica(){
        header('Content-Type: application/json');

        $dados = json_decode(file_get_contents('php://input'), true);

        if(empty($dados["id_fichamedica"]) || empty($dados["id_CID"]) || empty($dados["data_diagnostico"]) || !isset($dados["intStatus"])){
            http_response_code(400);
            echo json_encode(["erro" => "Campos invalidos ou ausentes"]);
            exit();
        }

        $id_fichamedica = trim($dados["id_fichamedica"]);
        $id_CID = trim($dados["id_CID"]);
        $data_diagnostico = trim($dados["data_diagnostico"]);
        $intStatus = trim($dados["intStatus"]);

        $atendidoDAO = new AtendidoDAO();
        $resultado_banco = $atendidoDAO->obterDataNascimentoPorFichaMedica((int)$id_fichamedica);
        $idPessoa = $atendidoDAO->obterPessoaIdPorFichaMedica((int)$id_fichamedica);
        if(!$idPessoa){
            http_response_code(404);
            echo json_encode(["erro" => "Ficha médica não encontrada"]);
            exit();
        }
        if ($resultado_banco === null) {
            $resultado_banco = $atendidoDAO->obterDataNascimentoPorPessoaId((int)$idPessoa) ?: '1900-01-01';
        }

        $data_nascimento = new DateTime($resultado_banco);
        $data_diagnostico_obj = new DateTime($data_diagnostico);
        $data_agora_obj = new DateTime();

        $data_nascimento->setTime(0, 0, 0);
        $data_diagnostico_obj->setTime(0, 0, 0);
        $data_agora_obj->setTime(0, 0, 0);

        if($data_diagnostico_obj > $data_agora_obj) {
            http_response_code(400);
            echo json_encode(["erro" => "A data de diagnóstico não pode ser futura."]);
            exit();
        }

        if($data_diagnostico_obj < $data_nascimento) {
            http_response_code(400);
            echo json_encode(["erro" => "A data de diagnóstico não pode ser anterior à data de nascimento do paciente (" . $data_nascimento->format('d/m/Y') . ")."]);
            exit();
        }

        try{
            $enfermidadeDao = new EnfermidadeDAO($this->pdo);
            $resultado = $enfermidadeDao->cadastrarEnfermidadeNaFichaMedica($id_CID, $id_fichamedica, $data_diagnostico, $intStatus);
            
            if($resultado){
                http_response_code(200);
                echo json_encode(["ok" => "Enfermidade adicionada com sucesso à ficha do paciente"]);
                exit();
            }else{
                throw new Exception("Enfermidade não adicionada à ficha do paciente", 500);
            }
        }catch(PDOException $e){
            Util::tratarException(new Exception('Erro interno de Banco de Dados ao adicionar uma nova enfermidade na ficha do paciente', 500, $e));
        }catch(Exception $e){
            $codigo = $e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500;
            Util::tratarException(new Exception('Erro interno de Servidor ao adicionar uma nova enfermidade na ficha do paciente', $codigo, $e));
        }
        exit();
    }

    //Pega todas as enfermidades ativas ligadas a uma ficha médica pelo id_fichamedica
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
        }catch(Exception $e){
            http_response_code(500);
            echo json_encode(['erro' => 'Problema no servidor ao buscar as enfermidades ligadas ao paciente']);
        }
        exit();
    }

    public function adicionarEnfermidade(){
        header('Content-Type: application/json');

        $dados = json_decode(file_get_contents('php://input'), true);

        $eNome = trim($dados["nome"]);
        $eCid = trim($dados["cid"]);

        $regexCid = '/^[A-TV-Z][0-9]{2}(\.[0-9A-Z]{1,4})?$/';


        if(!$eCid || !isset($eCid) || !$eNome || !isset($eNome)){
            http_response_code(400);
            echo json_encode(["erro" => "Campos invalidos"]);
            exit();
        }  else if (!preg_match($regexCid, $eCid)) {
	        http_response_code(400);
	        echo json_encode(['erro' => 'O CID informado não está dentro do padrão CID-10 da OMS']);
	        exit();
        }

        try{
            $enfermidadeDao = new EnfermidadeDAO($this->pdo);
            $resultado = $enfermidadeDao->adicionarEnfermidade($eNome, $eCid);
            if($resultado){
                http_response_code(200);
                json_encode(["ok" => "Enfermidade adicionada com sucesso"]);
            }else{
                throw new Exception("Enfermidade não adicionada", 500);
            }
        }catch(PDOException $e){
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno de Banco de Dados ao adicionar uma nova enfermidade']);
        }catch(Exception $e){
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno de Servidor ao adicionar uma nova enfermidade']);
        }
        exit();
    }

    //Esse método pega todas as enfermidades da tabela de enfermidades (não confunda com as enfermidades  ativas de cada paciente)
    public function listarTodasAsEnfermidades(){
        try{
            $enfermidadeDao = new EnfermidadeDAO($this->pdo);
            $enfermidades = $enfermidadeDao->listarTodasAsEnfermidades();

            echo json_encode($enfermidades);
        }catch(Exception $e){
            http_response_code(500);
            echo json_encode(['erro' => 'Problema no servidor ao buscar enfermidades']);
        }
        exit();
    }

    //Essa método torna inativa uma enfermidade ligada a um id_enfermidade 
    //Tornar ela inativa seria semelhante a excluir, mas ao inves de deletar apenas o status fica como 0
    public function tornarEnfermidadeInativa(){
        $id_enfermidade = isset($_GET['id_enfermidade']) ? $_GET['id_enfermidade'] : null;

        if(!$id_enfermidade || !isset($id_enfermidade)){
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(["erro" => "Campos obrigatórios ausentes"]);
            exit();
        }
        try{
            $enfermidadeDao = new EnfermidadeDAO($this->pdo);
            $enfermidades = $enfermidadeDao->tornarEnfermidadeInativa($id_enfermidade);
            if($enfermidades){
                http_response_code(200);
                echo json_encode(["msg" => "Enfermidade inativada com sucesso"]);
            }else{
                throw new Exception("Erro ao inativar enfermidade", 500);
            }
        }catch(PDOException $e){
            http_response_code($e->getCode());
            echo json_encode(["msg" => "Erro de BD ao inativar enfermidade"]);
        }catch(Exception $e){
                http_response_code($e->getCode());
                echo json_encode(["msg" => $e->getMessage()]);
        }

    }
}
