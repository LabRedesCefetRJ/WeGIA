<?php
class Situacao
{
   private $id_situacao;
   private $situacoes;
   
    public function __construct($situacoes)
    {

        $this->situacoes=$situacoes;

    }

   public function getId_situacao()
    {
        return $this->id_situacao;
    }

    public function getSituacoes()
    {
        return $this->situacoes;
    }

    public function setId_situacao($id_situacao)
    {
        $this->id_situacao = $id_situacao;
    }

    public function setSituacoes($situacoes)
    {
        $this->situacoes = $situacoes;
    }
}