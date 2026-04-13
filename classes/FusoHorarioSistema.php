<?php

class FusoHorarioSistema
{
    public static function definir(?string $fusoHorario = null): string
    {
        if (!defined('APP_TIMEZONE')) {
            require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config.php';
        }

        $fusosValidos = DateTimeZone::listIdentifiers();
        $fusoHorario = is_string($fusoHorario) ? trim($fusoHorario) : '';

        if ($fusoHorario === '' || !in_array($fusoHorario, $fusosValidos, true)) {
            $fusoHorario = defined('APP_TIMEZONE') ? trim((string) APP_TIMEZONE) : '';
        }

        if ($fusoHorario === '' || !in_array($fusoHorario, $fusosValidos, true)) {
            $fusoHorario = 'America/Sao_Paulo';
        }

        date_default_timezone_set($fusoHorario);

        return $fusoHorario;
    }
}
