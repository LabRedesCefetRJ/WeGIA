<?php

namespace api\modules\Contribuicao;

use api\contracts\services\ContribuicaoImportServiceInterface;
use InvalidArgumentException;

class ContribuicaoImportRegistry
{
    /** @var array<string, ContribuicaoImportServiceInterface> */
    private array $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function get(string $modelo): ContribuicaoImportServiceInterface
    {
        if (!isset($this->services[$modelo])) {
            throw new InvalidArgumentException("Modelo de importação '{$modelo}' não encontrado.", 400);
        }

        return $this->services[$modelo];
    }
}
