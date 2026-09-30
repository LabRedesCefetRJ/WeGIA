<?php

namespace api\contracts\services;

use api\contracts\entities\SaudeEspecialidadeInterface;
use Psr\Http\Message\UploadedFileInterface;

interface SaudeServiceInterface
{
    /**
     * @return SaudeEspecialidadeInterface[]
     */
    public function listarEspecialidades(): array;

    public function salvarModeloParecer(string $descricao, UploadedFileInterface $arquivo): int;
}
