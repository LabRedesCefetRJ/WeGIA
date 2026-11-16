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

    public static function parseV7(string $uuid): ?UuidInterface
    {
        try {
            $parsedUuid = Uuid::fromString($uuid);
            return $parsedUuid->getVersion() === 7 ? $parsedUuid : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
