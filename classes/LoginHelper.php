<?php

class LoginHelper
{
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $password, ?string $storedHash): bool
    {
        return self::verifyAndMigrate($password, $storedHash)['valid'];
    }

    public static function verifyAndMigrate(string $password, ?string $storedHash): array
    {
        if (!is_string($storedHash) || $storedHash === '') {
            return [
                'valid' => false,
                'needs_rehash' => false,
                'updated_hash' => null,
            ];
        }

        if (self::isModernHash($storedHash)) {
            $valid = password_verify($password, $storedHash);

            return [
                'valid' => $valid,
                'needs_rehash' => $valid && password_needs_rehash($storedHash, PASSWORD_DEFAULT),
                'updated_hash' => $valid && password_needs_rehash($storedHash, PASSWORD_DEFAULT)
                    ? self::hashPassword($password)
                    : null,
            ];
        }

        $legacyHash = hash('sha256', $password);
        $valid = hash_equals(strtolower($storedHash), strtolower($legacyHash));

        return [
            'valid' => $valid,
            'needs_rehash' => $valid,
            'updated_hash' => $valid ? self::hashPassword($password) : null,
        ];
    }

    private static function isModernHash(string $storedHash): bool
    {
        $info = password_get_info($storedHash);

        return !empty($info['algo']);
    }
}
