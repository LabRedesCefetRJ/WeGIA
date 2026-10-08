<?php

class PaymentServiceException extends Exception
{
    private string $mensagemCliente;

    public function __construct(
        string $mensagemCliente,
        string $mensagemInterna = '',
        int $code = 500,
        ?Throwable $previous = null
    ) {
        $mensagemInterna = $mensagemInterna !== '' ? $mensagemInterna : $mensagemCliente;

        parent::__construct($mensagemInterna, $code, $previous);

        $this->mensagemCliente = $mensagemCliente !== ''
            ? $mensagemCliente
            : 'Não foi possível concluir a operação no momento. Tente novamente mais tarde.';
    }

    public function getMensagemCliente(): string
    {
        return $this->mensagemCliente;
    }
}
