<?php

final class OrigemNavegacao
{
    public static function normalizar($origem): string
    {
        return in_array($origem, ['cotacao', 'entrada', 'lista_origem'], true)
            ? $origem : 'lista_origem';
    }

    public static function destino($origem): string
    {
        return match (self::normalizar($origem)) {
            'cotacao' => WWW . 'controle/control.php?nomeClasse=CotacaoControle&metodo=cadastrar',
            'entrada' => WWW . 'html/matPat/cadastro_entrada.php',
            default => WWW . 'html/matPat/listar_origem.php'
        };
    }

    public static function cadastro($origem): string
    {
        return WWW . 'html/matPat/cadastro_doador.php?' . http_build_query([
            'origem' => self::normalizar($origem)
        ]);
    }
}
