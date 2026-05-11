<?php

namespace api\modules\Socio;

use api\contracts\entities\PessoaInterface;
use api\contracts\entities\SocioInterface;
use DateTime;
use JsonSerializable;

class Socio implements SocioInterface, JsonSerializable
{
    private int $id;
    private int $idSocioTipo;
    private PessoaInterface $pessoa;
    private int $idSocioStatus;
    private bool $autoStatusContribuicao;
    private float $valorMensalidade;
    private DateTime $inicioContribuicao;

    public function __construct(PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade = 10.0, int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0, ?int $id = null)
    {
        if ($id !== null)
            $this->id = $id;
        
        $this->idSocioTipo = $idSocioTipo;
        $this->pessoa = $pessoa;
        $this->idSocioStatus = $idSocioStatus;
        $this->autoStatusContribuicao = $autoStatusContribuicao;
        $this->valorMensalidade = $valorMensalidade;
        $this->inicioContribuicao = $inicioContribuicao;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIdSocioTipo(): int
    {
        return $this->idSocioTipo;
    }

    public function getStatus(): int
    {
        return $this->idSocioStatus;
    }

    public function getAutoStatusContribuicao(): bool
    {
        return $this->autoStatusContribuicao;
    }

    public function getValorMensalidade(): float
    {
        return $this->valorMensalidade;
    }

    public function getInicioContribuicao(): DateTime
    {
        return $this->inicioContribuicao;
    }

    public function getPessoa(): PessoaInterface
    {
        return $this->pessoa;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setIdSocioTipo(int $idSocioTipo): void
    {
        $this->idSocioTipo = $idSocioTipo;
    }

    public function setStatus(int $status): void
    {
        $this->idSocioStatus = $status;
    }

    public function setAutoStatusContribuicao(bool $autoStatusContribuicao): void
    {
        $this->autoStatusContribuicao = $autoStatusContribuicao;
    }

    public function setValorMensalidade(float $valorMensalidade): void
    {
        $this->valorMensalidade = $valorMensalidade;
    }

    public function setInicioContribuicao(DateTime $inicioContribuicao): void
    {
        $this->inicioContribuicao = $inicioContribuicao;
    }

    public function setPessoa(PessoaInterface $pessoa): void
    {
        $this->pessoa = $pessoa;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'idSocioTipo' => $this->idSocioTipo,
            'status' => $this->idSocioStatus,
            'autoStatusContribuicao' => $this->autoStatusContribuicao,
            'valorMensalidade' => $this->valorMensalidade,
            'inicioContribuicao' => $this->inicioContribuicao->format('Y-m-d'),
            'pessoa' => $this->pessoa
        ];
    }
}
