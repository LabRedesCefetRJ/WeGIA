<?php
class AgendaEquipeDivisao {
    private $id;
    private $id_equipe;
    private $nome;
    private $ativo;

    public function getId() {
        return $this->id;
    }

    public function getId_equipe() {
        return $this->id_equipe;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getAtivo() {
        return $this->ativo;
    }

    public function setId(int $id) {
        if (!$id || $id < 1)
            throw new InvalidArgumentException('O id da divisão de equipe de agenda fornecido não é válido.', 412);
        $this->id = $id;
    }

    public function setId_equipe($id_equipe) {
        $this->id_equipe = $id_equipe;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setAtivo($ativo) {
        $this->ativo = $ativo;
    }
}