<?php

class Imagem{

    //atributos

    private string $conteudo;
    private string $extensao;

    public function __construct(string $conteudo, string $extensao)
    {
        $this->setConteudo($conteudo)->setExtensao($extensao);
    }

    //lógica
    
    /**
     * Retorna a tag html img com o conteúdo da imagem preenchido
     */
    public function getHtml():string{
        return "<img id='logo_img' width='100px' src=data:image/".$this->extensao.";base64,".gzuncompress($this->conteudo).">";
    }

    //métodos acessores

    /**
     * Get the value of conteudo
     */ 
    public function getConteudo():string
    {
        return $this->conteudo;
    }

    /**
     * Set the value of conteudo
     *
     * @return  self
     */ 
    public function setConteudo(string $conteudo)
    {
        if(!$conteudo || empty($conteudo)){
            throw new InvalidArgumentException('O conteúdo de uma imagem não pode ser vazio.');
        }

        $this->conteudo = $conteudo;

        return $this;
    }

    /**
     * Get the value of exensao
     */ 
    public function getExtensao():string
    {
        return $this->extensao;
    }

    /**
     * Set the value of exensao
     *
     * @return  self
     */ 
    public function setExtensao($extensao)
    {
        //considerar fazer uma lista branca de extensões permitidas.

        if(!$extensao || empty($extensao)){
            throw new InvalidArgumentException('O conteúdo de uma extensão não pode ser vazio.');
        }

        $this->extensao = $extensao;

        return $this;
    }
}