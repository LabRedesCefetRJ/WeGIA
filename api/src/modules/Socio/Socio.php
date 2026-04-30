<?php

namespace api\modules\Socio;

use api\contracts\PessoaInterface;
use api\contracts\SocioInterface;

class Socio implements SocioInterface
{
    private int $id;
    private PessoaInterface $pessoa;

    public function __construct(int $id, PessoaInterface $pessoa)
    {
        $this->id = $id;
        $this->pessoa = $pessoa;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPessoa(): PessoaInterface
    {
        return $this->pessoa;
    }
}