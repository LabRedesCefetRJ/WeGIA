<?php
$config_path = "config.php";
if(file_exists($config_path)){
    require_once($config_path);
}else{
    while(true){
        $config_path = "../" . $config_path;
        if(file_exists($config_path)) break;
    }
    require_once($config_path);
}

require_once ROOT.'/classes/SinaisVitais.php';
require_once ROOT.'/dao/SinaisVitaisDAO.php';
include_once ROOT.'/classes/Cache.php';
include_once ROOT."/dao/Conexao.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class SinaisVitaisControle 
{  
    public function verificar(){
        $id_fichamedica = trim($_REQUEST['id_fichamedica']);
        $id_funcionario = trim($_REQUEST['id_funcionario']);
        $data_afericao = trim($_REQUEST['data_afericao']);
        $saturacao = trim($_REQUEST['saturacao']);
        $pres_art = trim($_REQUEST['pres_art']);
        $freq_card = trim($_REQUEST['freq_card']);
        $freq_resp = trim($_REQUEST['freq_resp']);
        $temperatura = trim($_REQUEST['temperatura']);
        $hgt = trim($_REQUEST['hgt']);
        $observacao = trim($_REQUEST['observacao']);

        if((!isset($id_fichamedica)) || (empty($id_fichamedica))){
            $id_fichamedica = "";
        }else $id_fichamedica = intval($id_fichamedica);

        if((!isset($id_funcionario)) || (empty($id_funcionario))){
            $id_funcionario = "";
        }else $id_funcionario = intval($id_funcionario);

        if((!isset($data_afericao)) || (empty($data_afericao))){
            $data_afericao = "";
        }else{
            $timestamp = strtotime($data_afericao);
            $data_afericao = date('Y-m-d\TH:i', $timestamp);
        } 

        if((!isset($saturacao)) || (empty($saturacao))){
            $saturacao= "";
        } else{
            $saturacao = str_replace(',','.', $saturacao);
            $saturacao = floatval($saturacao);
        } 

        if((!isset($pres_art)) || (empty($pres_art))){
            $pres_art= "";
        }

        if((!isset($freq_card)) || (empty($freq_card))){
            $freq_card= "";
        }else $freq_card = intval($freq_card);

        if((!isset($freq_resp)) || (empty($freq_resp))){
            $freq_resp= "";
        }else $freq_resp = intval($freq_resp);

        if((!isset($temperatura)) || (empty($temperatura))){
            $temperatura= "";
        }else{
            $temperatura = str_replace(',','.', $temperatura);
            $temperatura = floatval($temperatura);
        } 

        if((!isset($hgt)) || (empty($hgt))){
            $hgt= "";
        }else{
            $hgt = str_replace(',','.', $hgt);
            $hgt = floatval($hgt);
        } 
        
        if(empty($observacao)) {
            $observacao = "";
        }else{
            $max_len_obs = 255;

        if(mb_strlen($observacao, 'UTF-8') > $max_len_obs) {
            $observacao = mb_substr($observacao, 0, $max_len_obs, 'UTF-8');
        }
    }
        
        $sinaisvitais = new SinaisVitais($id_fichamedica, $id_funcionario, $data_afericao, $saturacao, $pres_art, $freq_card, $freq_resp, $temperatura, $hgt, $observacao);

        $sinaisvitais->setIdFuncionario($id_funcionario);
        $sinaisvitais->setIdFichamedica($id_fichamedica);
        $sinaisvitais->setData($data_afericao);
        $sinaisvitais->setSaturacao($saturacao);
        $sinaisvitais->setPressaoArterial($pres_art);
        $sinaisvitais->setFrequenciaCardiaca($freq_card);
        $sinaisvitais->setFrequenciaRespiratoria($freq_resp);
        $sinaisvitais->setTemperatura($temperatura);
        $sinaisvitais->setHgt($hgt);
        $sinaisvitais->setObservacao($observacao);

        return $sinaisvitais;
    }

    public function incluir(){
        $id_fichamedica = trim($_REQUEST['id_fichamedica']);

        if(!$id_fichamedica || !is_numeric($id_fichamedica)){
            http_response_code(400);
            exit('Erro, o id da ficha médica não pode ser nulo ou diferente de um número');
        }

        $sinaisvitais = $this->verificar();
        $data_afericao = $sinaisvitais->getData();
        if (empty($data_afericao)) {
            http_response_code(400);
            exit('A data da aferição não pode ser vazia');
        }

        $pdo = Conexao::connect();
        $stmtPaciente = $pdo->prepare("SELECT id_pessoa FROM saude_fichamedica WHERE id_fichamedica = :idFichaMedica");
        $stmtPaciente->bindValue(':idFichaMedica', $id_fichamedica, PDO::PARAM_INT);
        $stmtPaciente->execute();
        $idPaciente = $stmtPaciente->fetchColumn();

        if (!$idPaciente) {
            http_response_code(400);
            exit('Paciente não encontrado para a ficha médica informada');
        }

        $stmtAtendido = $pdo->prepare("SELECT p.data_nascimento FROM pessoa p JOIN atendido a ON p.id_pessoa = a.pessoa_id_pessoa WHERE a.pessoa_id_pessoa = :idPessoa");
        $stmtAtendido->bindValue(':idPessoa', $idPaciente, PDO::PARAM_INT);
        $stmtAtendido->execute();
        $data_nasc_atendido = $stmtAtendido->fetchColumn() ?: '1900-01-01';

        $timezone = new DateTimeZone('America/Sao_Paulo');
        $dataAfericao = DateTime::createFromFormat('Y-m-d\TH:i', $data_afericao, $timezone);
        if (!$dataAfericao) {
            http_response_code(400);
            exit('Data de aferição inválida');
        }

        $dataNascimento = DateTime::createFromFormat('Y-m-d', $data_nasc_atendido, $timezone);
        if (!$dataNascimento) {
            $dataNascimento = new DateTime('1900-01-01', $timezone);
        }
        $dataNascimento->setTime(0, 0, 0);

        if ($dataAfericao < $dataNascimento) {
            http_response_code(400);
            exit('Data inválida: não pode ser anterior à data de nascimento.');
        }

        $dataAgora = new DateTime('now', $timezone);
        if ($dataAfericao > $dataAgora) {
            http_response_code(400);
            exit('A data da aferição não pode ser no futuro.');
        }

        $sinVitDAO = new SinaisVitaisDAO();
        
        try{
            // Caminho de Sucesso (como estava no seu original)
            $intDAO=$sinVitDAO->incluir($sinaisvitais);
            $_SESSION['msg']="Ficha médica cadastrada com sucesso!";
            $_SESSION['proxima']="Cadastrar outra ficha.";
            $_SESSION['link']="../html/saude/cadastro_ficha_medica.php";
            header("Location: ../html/saude/sinais_vitais.php?id_fichamedica=".$id_fichamedica);
            exit;

        } catch (PDOException $e) {
            error_log('Error: na tabela saude_sinais_vitais, Erro=' . $e->getMessage());
            $msg= htmlspecialchars("Não foi possível registrar o paciente <form> <input type='button' value='Voltar' onClick='history.go(-1)'> </form>"."<br>".$e->getMessage());
            echo $msg;
        }
    }
}
