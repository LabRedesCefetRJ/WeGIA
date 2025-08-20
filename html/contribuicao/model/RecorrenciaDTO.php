<?php
class RecorrenciaDTO{
    //propriedades
    public int $id;
    public string $codigo;
    public DateTime $inicio;
    public DateTime $termino;
    public float $valor;
    public bool $stauts;

    public function __construct(string $codigo)//adicionar demais propriedades conforme necessário
    {
        $this->codigo = $codigo;
    }
}