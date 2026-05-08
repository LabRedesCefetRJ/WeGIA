<?php
/** 
 * nome -> nome
 * data_nascimento -> nascimento 
 * data_acolhimento -> acolhimento
 * sexo -> gender
 * caracteristicas_especificas -> caracEsp
 * especie -> especie
 * foto -> imgperfil
 * cor -> cor
 * raca -> raca
 */

class PetClasse{
    private $id;
    private $nome;
    private $nascimento;
    private $acolhimento;
    private $sexo;
    private $caracEsp;
    private $especie;
    private $raca;
    private $cor;
    private $imgperfil;
    private $nomeImagem;

    public function getId(){
        return $this->id;
    }

    public function getNome(){
        return $this->nome;
    }

    public function getNascimento(){
        return $this->nascimento;
    }

    public function getAcolhimento(){
        return $this->acolhimento;
    }

    public function getSexo(){
        return $this->sexo;
    }

    public function getCaracteristicasEspecificas(){
        return $this->caracEsp;
    }

    public function getEspecie(){
        return $this->especie;
    }

    public function getRaca(){
        return $this->raca;
    }

    public function getCor(){
        return $this->cor;
    }

    public function getImgPerfil(){
        return $this->imgperfil;
    }

    public function getNomeImagem(){
        return $this->nomeImagem;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setNome($nome){
        $this->nome = $nome;
    }

    public function setNascimento($nascimento){
        $this->nascimento = $nascimento;
    }

    public function setAcolhimento($acolhimento){
        $this->acolhimento = $acolhimento;
    }

    public function setSexo($sexo){
        $this->sexo = $sexo;
    }

    public function setCaracteristicasEspecificas($caracEsp){
        $this->caracEsp = $caracEsp;
    }

    public function setEspecie($especie){
        $this->especie = $especie;
    }

    public function setRaca($raca){
        $this->raca = $raca;
    }

    public function setCor($cor){
        $this->cor = $cor;
    }

    public function setImgPerfil($imgperfil){
        $this->imgperfil = $imgperfil;
    }

    public function setNomeImagem($nomeImagem){
        $this->nomeImagem = $nomeImagem;
    }
}
?>
