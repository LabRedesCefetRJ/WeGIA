<?php

namespace api\modules\Auth;

use PDO;

class JwtBlacklistRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function isBlacklisted(string $token): bool
    {
        $query = 'SELECT 1 FROM jwt_blacklist WHERE token_hash = :token_hash LIMIT 1';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['token_hash' => $this->hashToken($token)]);

        return $stmt->fetchColumn() !== false;
    }

    public function blacklist(string $token, object $decodedToken): void
    {
        $query = 'INSERT IGNORE INTO jwt_blacklist
            (token_hash, user_id, token_type, issued_at, expires_at)
            VALUES (:token_hash, :user_id, :token_type, FROM_UNIXTIME(:issued_at), FROM_UNIXTIME(:expires_at))';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'token_hash' => $this->hashToken($token),
            'user_id' => (int) $decodedToken->sub,
            'token_type' => $decodedToken->type,
            'issued_at' => (int) $decodedToken->iat,
            'expires_at' => (int) $decodedToken->exp,
        ]);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }
}