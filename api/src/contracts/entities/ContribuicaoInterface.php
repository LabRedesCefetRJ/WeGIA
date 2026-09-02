<?php
namespace api\contracts\entities;

use DateTime;

interface ContribuicaoInterface
{
    //getters
    public function getId(): int;
    public function getIdSocio(): int;
    public function getIdGateway(): ?int;
    public function getValor(): float;
    public function getDataPagamento(): ?DateTime;
    public function getDataVencimento(): DateTime;
    public function getDataGeracao(): DateTime;
    public function getStatus(): string;
    
    //setters
    public function setIdSocio(int $idSocio): ContribuicaoInterface;
    public function setIdGateway(?int $idGateway): ContribuicaoInterface;
    public function setValor(float $valor): ContribuicaoInterface;
    public function setDataPagamento(?DateTime $dataPagamento): ContribuicaoInterface;
    public function setDataVencimento(DateTime $dataVencimento): ContribuicaoInterface;
    public function setDataGeracao(DateTime $dataGeracao): ContribuicaoInterface;
    public function setStatus(string $status): ContribuicaoInterface;
}
