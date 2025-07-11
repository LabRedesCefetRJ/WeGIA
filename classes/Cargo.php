<?php
//implementar JsonSerializable
class Cargo implements JsonSerializable
{
    private $id_cargo;
    private $cargo;

    public function __construct($cargo, $id = null)
    {
        $this->setCargo($cargo);
        if($id){
            $this->setId_cargo($id);
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_cargo' => $this->id_cargo,
            'cargo' => $this->cargo
        ];
    }

    public function getId_cargo()
    {
        return $this->id_cargo;
    }

    public function getCargo()
    {
        return $this->cargo;
    }

    public function setId_cargo(int $id_cargo)
    {
        if ($id_cargo < 1) {
            throw new InvalidArgumentException('O número de um id não pode ser menor que 1.');
        }
        $this->id_cargo = $id_cargo;
    }

    public function setCargo(string $cargo)
    {
        if (empty($cargo)) {
            throw new InvalidArgumentException('A descrição de um cargo não pode ser vazia.');
        }
        $this->cargo = $cargo;
    }
}
