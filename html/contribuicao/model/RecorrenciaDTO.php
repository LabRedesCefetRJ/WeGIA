<?php
class RecorrenciaDTO{
    //propriedades
    public int $id;
    public int $idGatewayPagamento;
    public int $idSocio;
    public string $codigo;
    public DateTime $inicio;
    public DateTime $termino;
    public $valor;
    public bool $status;

    public function __construct(string $codigo)//adicionar demais propriedades conforme necessário
    {
        $this->codigo = $codigo;
    }
}