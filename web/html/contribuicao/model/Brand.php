<?php
require_once __DIR__.'/Imagem.php';

class Brand{

    //atributos

    private Imagem $imagem;
    private $mensagem;

    public function __construct(Imagem $imagem, string $mensagem)
    {
        $this->setImagem($imagem)->setMensagem($mensagem);
    }

    //métodos acessores

    /**
     * Get the value of imagem
     */ 
    public function getImagem():Imagem
    {
        return $this->imagem;
    }

    /**
     * Set the value of imagem
     *
     * @return  self
     */ 
    public function setImagem(Imagem $imagem)
    {
        $this->imagem = $imagem;

        return $this;
    }

    /**
     * Get the value of mensagem
     */ 
    public function getMensagem():string
    {
        return $this->mensagem;
    }

    /**
     * Set the value of mensagem
     *
     * @return  self
     */ 
    public function setMensagem(string $mensagem)
    {
        if(!$mensagem || empty($mensagem)){
            throw new InvalidArgumentException('O conteúdo de uma mensagem não pode ser vazio.');
        }

        $this->mensagem = $mensagem;

        return $this;
    }
}