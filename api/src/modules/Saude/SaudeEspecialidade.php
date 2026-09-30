<?php

namespace api\modules\Saude;

use api\contracts\entities\SaudeEspecialidadeInterface;

class SaudeEspecialidade implements SaudeEspecialidadeInterface, \JsonSerializable
{
    private int $id;
    private string $descricao;

    public function __construct(int $id, string $descricao)
    {
        $this->id = $id;
        $this->descricao = $descricao;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescricao(): string
    {
        return $this->descricao;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
        ];
    }
}
