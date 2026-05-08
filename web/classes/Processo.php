<?php

class ProcessoDeAceitacao
{
    private int $id;
    private string $dataInicio;
    private ?string $dataFim; 
    private string $descricao;
    private int $idStatus;
    private int $idPessoa;

    public function __construct(
        int $id = 0,
        string $dataInicio = '',
        ?string $dataFim = null,
        string $descricao = '',
        int $idStatus = 0,
        int $idPessoa = 0
    ) {
        $this->id = $id;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->descricao = $descricao;
        $this->idStatus = $idStatus;
        $this->idPessoa = $idPessoa;
    }

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDataInicio(): string
    {
        return $this->dataInicio;
    }
    public function setDataInicio(string $dataInicio): void
    {
        $this->dataInicio = $dataInicio;
    }

    public function getDataFim(): ?string
    {
        return $this->dataFim;
    }
    public function setDataFim($dataFim): void 
    {
        $this->dataFim = $dataFim;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }
    public function setDescricao(string $descricao): void
    {
        $this->descricao = $descricao;
    }

    public function getIdStatus(): int
    {
        return $this->idStatus;
    }
    public function setIdStatus(int $idStatus): void
    {
        $this->idStatus = $idStatus;
    }

    public function getIdPessoa(): int
    {
        return $this->idPessoa;
    }
    public function setIdPessoa(int $idPessoa): void
    {
        $this->idPessoa = $idPessoa;
    }
}
