<?php

namespace api\modules\Socio;

use api\modules\Pessoa\Pessoa;

class Socio
{
    private int $id;
    private Pessoa $pessoa; //pegar via contrato

    public function __construct(int $id, Pessoa $pessoa)
    {
        throw new \Exception('Not implemented.');
    }
}