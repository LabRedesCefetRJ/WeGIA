<?php
require_once __DIR__.'/ImagemDAO.php';

class BrandDAO{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getBrand(){
        $sql = "SELECT paragrafo FROM selecao_paragrafo WHERE nome_campo = 'ContribuiçãoMSG'";
        $resultado = $this->pdo->query($sql);

        if($resultado->rowCount() < 1){
            return null;
        }

        $mensagem = $resultado->fetch(PDO::FETCH_ASSOC)['paragrafo'];

        $imagemDao = new ImagemDAO($this->pdo);
        $imagem = $imagemDao->getImagem();

        if(is_null($imagem)){
            return null;
        }

        $brand = new Brand($imagem, $mensagem);
        return $brand;
    }
}