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
include_once ROOT."/dao/EnderecoDAO.php";
include_once ROOT."/classes/Endereco.php";
include_once ROOT."/dao/Conexao.php";

class EnderecoControle
{
	public function incluirEndereco() 
	{
		extract($_REQUEST);
		if((!isset($numero_residencia)) || empty(($numero_residencia))){
            $numero_residencia = "Sem número";
        }
        $endereco = new Endereco();
        $endereco->setNome($nome);
        $endereco->setNumeroEndereco($numero_residencia);
        $endereco->setLogradouro($rua);
        $endereco->setBairro($bairro);
        $endereco->setCidade($cidade);
        $endereco->setEstado($uf);
        $endereco->setCep($cep);
        $endereco->setComplemento($complemento);
        $endereco->setIbge($ibge);
        $enderecoDAO=new EnderecoDAO();
        try {
            $enderecoDAO->incluirEndereco($endereco);
            header("Location: ../html/personalizacao.php");
        } catch (PDOException $e) {
            echo $e->getMessage();
        }  
	}

	public function listarInstituicao()
	{
		$enderecoDAO = new EnderecoDAO();
        $endereco = $enderecoDAO->listarInstituicao();
        $_SESSION['endereco']=$endereco;
	}

	public function alterarEndereco()
    {
        extract($_REQUEST);
        if((!isset($numero_residencia)) || empty(($numero_residencia))){
            $numero_residencia = "Sem número";
        }
        $endereco = new Endereco;
        $endereco->setNome($nome);
        $endereco->setNumeroEndereco($numero_residencia);
        $endereco->setLogradouro($rua);
        $endereco->setBairro($bairro);
        $endereco->setCidade($cidade);
        $endereco->setEstado($uf);
        $endereco->setCep($cep);
        $endereco->setComplemento($complemento);
        $endereco->setIbge($ibge);
        $enderecoDAO=new EnderecoDAO();
        try {
            $enderecoDAO->alterarEndereco($endereco);
            header("Location: ../html/personalizacao.php");
        } catch (PDOException $e) {
            echo $e->getMessage();
        } 
    }
}