<?php

namespace api\utils;

require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidGenerator
{
    public static function generateV7(): UuidInterface
    {
        return Uuid::uuid7();
    }

    public static function generateBinary(): string
    {
        return self::generateV7()->getBytes();
    }
}
