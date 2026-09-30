<?php

namespace api\contracts\entities;

/**
 * Interface SaudeEspecialidadeInterface
 *
 * Define o contrato para uma especialidade de saúde registrada
 * na base do sistema.
 */
interface SaudeEspecialidadeInterface
{
    public function getId(): int;

    public function getDescricao(): string;
}
