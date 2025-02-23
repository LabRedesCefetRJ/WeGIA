<?php
class Enfermidade implements JsonSerializable{
    private $data;
    private $descricao;
    private $idCid;

    public function __construct($data, $descricao, $idCid)
    {
        $this->setData($data);
        $this->setDescricao($descricao);
        $this->setIdCid($idCid);
    }

    public function jsonSerialize(): array {
        return [
            'data_diagnostico' => $this->data,
            'descricao' => $this->descricao,
            'id_CID' => $this->idCid
        ];
    }

    /**
     * Get the value of data
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @return  self
     */ 
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of descricao
     */ 
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * Set the value of descricao
     *
     * @return  self
     */ 
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;

        return $this;
    }

    /**
     * Get the value of idCid
     */ 
    public function getIdCid()
    {
        return $this->idCid;
    }

    /**
     * Set the value of idCid
     *
     * @return  self
     */ 
    public function setIdCid($idCid)
    {
        $this->idCid = $idCid;

        return $this;
    }
}