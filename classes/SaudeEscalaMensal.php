<?php

class SaudeEscalaMensal
{
    private ?int $idEscalaMensal = null;
    private int $ano;
    private int $mes;
    private ?string $observacao = null;

    public function __construct(int $ano, int $mes, ?string $observacao = null)
    {
        $this->setAno($ano);
        $this->setMes($mes);
        $this->setObservacao($observacao);
    }

    public function getIdEscalaMensal(): ?int
    {
        return $this->idEscalaMensal;
    }

    public function setIdEscalaMensal(?int $idEscalaMensal): self
    {
        if (!is_null($idEscalaMensal) && $idEscalaMensal < 1) {
            throw new InvalidArgumentException('O id da escala mensal deve ser maior que zero.', 400);
        }

        $this->idEscalaMensal = $idEscalaMensal;
        return $this;
    }

    public function getAno(): int
    {
        return $this->ano;
    }

    public function setAno(int $ano): self
    {
        if ($ano < 2000 || $ano > 2100) {
            throw new InvalidArgumentException('Ano da escala mensal inválido.', 400);
        }

        $this->ano = $ano;
        return $this;
    }

    public function getMes(): int
    {
        return $this->mes;
    }

    public function setMes(int $mes): self
    {
        if ($mes < 1 || $mes > 12) {
            throw new InvalidArgumentException('Mês da escala mensal inválido.', 400);
        }

        $this->mes = $mes;
        return $this;
    }

    public function getObservacao(): ?string
    {
        return $this->observacao;
    }

    public function setObservacao(?string $observacao): self
    {
        if (is_null($observacao)) {
            $this->observacao = null;
            return $this;
        }

        $observacao = trim($observacao);

        if ($observacao === '') {
            $this->observacao = null;
            return $this;
        }

        if (mb_strlen($observacao) > 500) {
            throw new InvalidArgumentException('A observação da escala mensal deve ter até 500 caracteres.', 400);
        }

        $this->observacao = $observacao;
        return $this;
    }
}
