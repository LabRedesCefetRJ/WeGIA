<?php

namespace api\modules\Saude;

use api\contracts\services\SaudeServiceInterface;

class SaudeService implements SaudeServiceInterface
{
    private SaudeRepository $saudeRepository;

    public function __construct(SaudeRepository $saudeRepository)
    {
        $this->saudeRepository = $saudeRepository;
    }

    /**
     * @return SaudeEspecialidade[]
     */
    public function listarEspecialidades(): array
    {
        $linhas = $this->saudeRepository->listarEspecialidades();

        return array_map(
            fn (array $linha) => new SaudeEspecialidade(
                (int) ($linha['id'] ?? 0),
                (string) ($linha['descricao'] ?? '')
            ),
            $linhas
        );
    }
}