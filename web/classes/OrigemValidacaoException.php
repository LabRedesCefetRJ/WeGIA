<?php

class OrigemValidacaoException extends InvalidArgumentException
{
    private string $campo;

    public function __construct(string $campo, string $mensagem)
    {
        parent::__construct($mensagem);
        $this->campo = $campo;
    }

    public function getCampo(): string
    {
        return $this->campo;
    }
}
