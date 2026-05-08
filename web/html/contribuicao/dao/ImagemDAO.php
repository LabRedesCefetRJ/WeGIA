<?php
require_once dirname(__DIR__).'/model/Imagem.php';

class ImagemDAO{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getImagem(){
        $sql = "SELECT imagem.imagem, imagem.tipo FROM imagem, campo_imagem, tabela_imagem_campo WHERE campo_imagem.id_campo=tabela_imagem_campo.id_campo AND imagem.id_imagem=tabela_imagem_campo.id_imagem AND campo_imagem.nome_campo='Logo'";

        $resultado = $this->pdo->query($sql);

        if($resultado->rowCount() < 1){
            return null;
        }

        $imagemData = $resultado->fetch(PDO::FETCH_ASSOC);

        $imagem = new Imagem($imagemData['imagem'], $imagemData['tipo']);
        return $imagem;
    }
}