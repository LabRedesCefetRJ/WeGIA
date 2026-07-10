<?php

class SocioHasTagDTO{
    public ?int $id;
    public int $socioId;
    public int $tagId;

    public function __construct(array $data)        
    {
        if(!isset($data['id_socio']) || !isset($data['id_sociotag'])){
            throw new InvalidArgumentException('Os campos id_socio e id_sociotag são obrigatórios.', 412);
        }

        $this->socioId = (int) $data['id_socio'];
        $this->tagId = (int) $data['id_sociotag'];
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
    }
}