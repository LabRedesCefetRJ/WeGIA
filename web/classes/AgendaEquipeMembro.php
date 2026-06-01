<?php
class AgendaEquipeMembro {
    private $id;
    private $id_equipe;
    private $id_pessoa;
    private $ativo;

    public function getId()        { return $this->id; }
    public function getId_equipe() { return $this->id_equipe; }
    public function getId_pessoa() { return $this->id_pessoa; }
    public function getAtivo()     { return $this->ativo; }

    public function setId(int $id)
    {
        if (!$id || $id < 1)
            throw new InvalidArgumentException('O id do membro da equipe de agenda fornecido não é válido.', 412);
        $this->id = $id;
    }
    public function setId_equipe($id_equipe) { $this->id_equipe = $id_equipe; }
    public function setId_pessoa($id_pessoa) { $this->id_pessoa = $id_pessoa; }
    public function setAtivo($ativo)         { $this->ativo = $ativo; }
}
