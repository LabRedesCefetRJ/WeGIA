<?php
class AgendaEquipe {
    private $id;
    private $nome;
    private $id_status;
    private $descricao;
    private $id_agenda;

    public function getId() 
    { 
        return $this->id; 
    }
    public function getNome() 
    { 
        return $this->nome; 
    }
    public function getId_status() 
    { 
        return $this->id_status; 
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getId_agenda()
    {
        return $this->id_agenda;
    }

    public function setId(int $id) {
        if(!$id || $id < 1)
            throw new InvalidArgumentException('O id da equipe fornecido não é válido.', 412);
        $this->id = $id;
    }
    public function setNome($nome) 
    { 
        $this->nome = $nome; 
    }
    public function setId_status($id_status) 
    { 
        $this->id_status = $id_status; 
    }
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
    public function setId_agenda($id_agenda)
    {
        $this->id_agenda = $id_agenda;
    }
}