<?php

class almoxarifado
{
    private $id_almoxarifado;
    private $descricao_almoxarifado;

    public function __construct($descricao_almoxarifado)
    {
        $this->setDescricao_almoxarifado($descricao_almoxarifado);
    }

    public function getId_almoxarifado()
    {
        return $this->id_almoxarifado;
    }

    public function getDescricao_almoxarifado()
    {
        return $this->descricao_almoxarifado;
    }

    public function setId_almoxarifado(int $id_almoxarifado)
    {
        if ($id_almoxarifado < 1) {
            throw new InvalidArgumentException('O id de um almoxarifado não pode ser menor que 1.');
        }
        $this->id_almoxarifado = $id_almoxarifado;
    }

    public function setDescricao_almoxarifado(string $descricao_almoxarifado)
    {
        if (empty($descricao_almoxarifado)) {
            throw new InvalidArgumentException('A descrição de um almoxarifado não pode ser vazia.', 400);
        }
        $this->descricao_almoxarifado = $descricao_almoxarifado;
    }
}
