<?php

class DescricaoFichamedica
{
    private $texto;
    private $id_fichamedica;

    public function getDescricao()
    {
        return $this->texto;
    }

    public function getIdFichamedica()
    {
        return $this->id_fichamedica;
    }

    public function setDescricao(string $texto)
    {
        if (empty($texto)) {
            throw new InvalidArgumentException('A descrição de uma ficha médica não pode ser vazia.');
        }
        $this->texto = $texto;
    }

    public function setIdFichamedica(int $id_fichamedica)
    {
        if ($id_fichamedica < 1) {
            throw new InvalidArgumentException('O id de uma ficha médica não pode ser menor que 1.');
        }
        $this->id_fichamedica = $id_fichamedica;
    }
}
