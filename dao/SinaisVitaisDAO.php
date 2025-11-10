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
require_once ROOT."/dao/Conexao.php";
require_once ROOT."/classes/SinaisVitais.php";
require_once ROOT."/Functions/funcoes.php";

class SinaisVitaisDAO
{
    public function incluir($sinaisvitais)
    {               
        $id_fichamedica = $sinaisvitais->getIdFichamedica();
        $id_funcionario = $sinaisvitais->getIdFuncionario();
        $data = $sinaisvitais->getData();
        $saturacao = $sinaisvitais->getSaturacao();
        $pres_art = $sinaisvitais->getPressaoArterial();
        $freq_card = $sinaisvitais->getFrequenciaCardiaca();
        $freq_resp = $sinaisvitais->getFrequenciaRespiratoria();
        $temperatura = $sinaisvitais->getTemperatura();
        $hgt = $sinaisvitais->getHgt();
        $observacao = $sinaisvitais->getObservacao();
        if ($saturacao === '') {
            $saturacao = null;
        }
        if ($pres_art === '') {
            $pres_art = null;
        }
        if ($freq_card === '') {
            $freq_card = null;
        }
        if ($freq_resp === '') {
            $freq_resp = null;
        }
        if ($temperatura === '') {
            $temperatura = null;
        }
        if ($hgt === '') {
            $hgt = null;
        }
        if ($observacao === '') {
            $observacao = null;
        }
        
        $sql = "INSERT INTO saude_sinais_vitais (id_fichamedica, id_funcionario, data, saturacao, pressao_arterial, frequencia_cardiaca, frequencia_respiratoria, temperatura, hgt, observacao) VALUES (:id_fichamedica, :id_funcionario, :data, :saturacao, :pressao_arterial, :frequencia_cardiaca, :frequencia_respiratoria, :temperatura, :hgt, :observacao)";
        $pdo = Conexao::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_fichamedica',$id_fichamedica);
        $stmt->bindParam(':id_funcionario',$id_funcionario);
        $stmt->bindParam(':data',$data);
        $stmt->bindParam(':saturacao',$saturacao);
        $stmt->bindParam(':pressao_arterial',$pres_art);
        $stmt->bindParam(':frequencia_cardiaca',$freq_card);
        $stmt->bindParam(':frequencia_respiratoria',$freq_resp);
        $stmt->bindParam(':temperatura',$temperatura);
        $stmt->bindParam(':hgt',$hgt);
        $stmt->bindParam(':observacao', $observacao);
        $stmt->execute();
        
    }
    
}
error_log('Error: na tabela saude_sinais_vitais = ' . $sql . ' | Erro=' . $e->getMessage());
