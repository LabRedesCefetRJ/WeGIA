<?php

namespace api\contracts\services;

use api\contracts\entities\SaudeEspecialidadeInterface;

interface SaudeServiceInterface
{
    /**
     * @return SaudeEspecialidadeInterface[]
     */
    public function listarEspecialidades(): array;
}
