<?php

namespace api\modules\Pessoa;

use Normalizer;

final class PessoaNameNormalizer
{
    public function normalize(string $name): string
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name));

        if ($name === '') {
            return '';
        }

        $normalized = Normalizer::normalize($name, Normalizer::FORM_D);
        if ($normalized !== false) {
            $name = (string) preg_replace('/\p{Mn}+/u', '', $normalized);
        }

        return mb_strtolower($name, 'UTF-8');
    }
}
