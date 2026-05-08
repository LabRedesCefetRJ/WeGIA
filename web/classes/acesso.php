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
class Acesso
{

    private $host = DB_HOST;

    private $usuario = DB_USER;

    private $senha = DB_PASSWORD;

    private $banco = DB_NAME;

    function getHost()
    {
        return $this->host;
    }

    function getUsuario()
    {
        return $this->usuario;
    }

    function getSenha()
    {
        return $this->senha;
    }

    function getBanco()
    {
        return $this->banco;
    }

    public function conexao()
    {
        try {
            $pdo = new PDO('mysql:host=' . $this->getHost() . ';dbname=' . $this->getBanco(), $this->getUsuario(), $this->getSenha());
            
            return $pdo;
        } catch (PDOException $e) {
            
            echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
        }
    }

    public function query($sql)
    {
        $pdo = $this->conexao();
        $rs = $pdo->query($sql);
        
        if (! $rs) {
            
            echo " <b>Reveja a consulta (SQL) : $sql</b>";
        }
        
        $this->result = $rs->fetchAll();
        
        $this->linha = $rs->rowCount();
    }

    public function execute($sql)
    {
        $pdo = $this->conexao();
        
        $rs = $pdo->prepare($sql);
        
        // $this->linha = $rs->rowCount();
    }

    public function __destruct()
    {
        @mysqli_close($this->cnx);
    }
}
