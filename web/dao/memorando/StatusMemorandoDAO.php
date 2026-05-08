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

class StatusMemorandoDAO{

    //Atributos privados
    private $pdo;
    public function __construct()
    {
        $this->pdo = Conexao::connect();
    }

    /**
     * Método responsável por realizar a pesquisa no banco de dados e retorna o resultado da query que busca um status_memorando que possua um id equivalente ao passado como parâmetro.
     */
     public function getPorId(int $id){
        $sql = 'SELECT * FROM status_memorando WHERE id_status_memorando=:idStatus';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idStatus', $id);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado;
     }
}