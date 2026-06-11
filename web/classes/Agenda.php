<?php
class Agenda {
    private $id;
    private $descricao;
    private $id_status;

    public function getId()
    {
        return $this->id;
    }
    
    public function getDescricao()
    {
        return $this->descricao;
    }

    public function getId_status()
    {
        return $this->id_status;
    }

    public function setId(int $id)
    {
        if(!$id || $id < 1)
            throw new InvalidArgumentException('O id da agenda fornecido não é válido.', 412);

        $this->id = $id;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    public function setId_status($id_status)
    {
        $this->id_status = $id_status;
    }
}

?>