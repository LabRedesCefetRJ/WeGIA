<?php


class SaudePet{
    private $nome;
    private $texto;
    private $castrado;
    private $vacinado;
    private $vermifugado;
    private $dataVacinado;
    private $dataVermifugado;

    public function getNome(){
        return $this->nome;
    }

    public function getTexto(){
        return $this->texto;
    }

    public function getCastrado(){
        return $this->castrado;
    }

    public function getVacinado(){
        return $this->vacinado;
    }

    public function getVermifugado(){
        return $this->vermifugado;
    }

    public function getDataVacinado(){
        return $this->dataVacinado;
    }

    public function getDataVermifugado(){
        return $this->dataVermifugado;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function setTexto($texto)
    {
        $this->texto = $texto;
    }

    public function setCastrado($castrado)
    {
        $this->castrado = $castrado;
    }

    public function setVacinado($vacinado)
    {
        $this->vacinado = $vacinado;
    }

    public function setVermifugado($vermifugado)
    {
        $this->vermifugado = $vermifugado;
    }

    public function setDataVacinado($dataVacinado)
    {
        $this->dataVacinado = $dataVacinado;
    }

    public function setDataVermifugado($dataVermifugado)
    {
        $this->dataVermifugado = $dataVermifugado;
    }
}
?>
