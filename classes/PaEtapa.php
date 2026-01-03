<?php

class PaEtapa
{
    private int $id;
    private string $dataInicio;
    private ?string $dataFim; 
    private string $descricao;
    private int $idProcesso;
    private int $idStatus;

    public function __construct(
        int $id = 0,
        string $dataInicio = '',
        ?string $dataFim = null,  
        string $descricao = '',
        int $idProcesso = 0,
        int $idStatus = 0
    ) {
        $this->id = $id;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->descricao = $descricao;
        $this->idProcesso = $idProcesso;
        $this->idStatus = $idStatus;
    }

    public function getDataFim(): ?string
    {
        return $this->dataFim;
    }

    public function setDataFim(?string $dataFim): void
    {
        $this->dataFim = $dataFim;
    }

   
}


