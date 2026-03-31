<?php

class SaudeEquipePlantao
{
    private ?int $idEquipePlantao = null;
    private string $nome;
    private ?string $descricao = null;
    private bool $ativo = true;

    public function __construct(string $nome, ?string $descricao = null, bool $ativo = true)
    {
        $this->setNome($nome);
        $this->setDescricao($descricao);
        $this->setAtivo($ativo);
    }

    public function getIdEquipePlantao(): ?int
    {
        return $this->idEquipePlantao;
    }

    public function setIdEquipePlantao(?int $idEquipePlantao): self
    {
        if (!is_null($idEquipePlantao) && $idEquipePlantao < 1) {
            throw new InvalidArgumentException('O id da equipe deve ser maior que zero.', 400);
        }

        $this->idEquipePlantao = $idEquipePlantao;
        return $this;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $nome = trim($nome);

        if ($nome === '' || mb_strlen($nome) > 120) {
            throw new InvalidArgumentException('O nome da equipe é obrigatório e deve ter até 120 caracteres.', 400);
        }

        $this->nome = $nome;
        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        if (is_null($descricao)) {
            $this->descricao = null;
            return $this;
        }

        $descricao = trim($descricao);

        if ($descricao === '') {
            $this->descricao = null;
            return $this;
        }

        if (mb_strlen($descricao) > 255) {
            throw new InvalidArgumentException('A descrição da equipe deve ter até 255 caracteres.', 400);
        }

        $this->descricao = $descricao;
        return $this;
    }

    public function isAtivo(): bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): self
    {
        $this->ativo = $ativo;
        return $this;
    }
}
