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

    /**
     * @return array<int, array{id:int, descricao:string, extensao:string|null, created_at:string|null, updated_at:string|null}>
     */
    public function listarModelosParecer(): array;

    public function salvarModeloParecer(string $descricao, UploadedFileInterface $arquivo): int;
}
