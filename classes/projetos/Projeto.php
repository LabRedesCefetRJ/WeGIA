<?php
class ProjetoClasse{
    private $id;
    private $nome;
    private $descricao;
    private $id_tipo;
    private $id_local;
    private $id_status;
    private $data_inicio;
    private $data_fim;

    public function getId(){
        return $this->id;
    }

    public function getNome(){
        return $this->nome;
    }

    public function getDescricao(){
        return $this->descricao;
    }

    public function getIdTipo(){
        return $this->id_tipo;
    }

    public function getIdLocal(){
        return $this->id_local;
    }

    public function getIdStatus(){
        return $this->id_status;
    }

    public function getDataInicio(){
        return $this->data_inicio;
    }

    public function getDataFim(){
        return $this->data_fim;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setNome($nome){
        $this->nome = $nome;
    }

    public function setDescricao($descricao){
        $this->descricao = $descricao;
    }

    public function setIdTipo($id_tipo){
        $this->id_tipo = $id_tipo;
    }

    public function setIdLocal($id_local){
        $this->id_local = $id_local;
    }

    public function setIdStatus($id_status){
        $this->id_status = $id_status;
    }

    public function setDataInicio($data_inicio){
        $this->data_inicio = $data_inicio;
    }

    public function setDataFim($data_fim){
        $this->data_fim = $data_fim;
    }
}
?>