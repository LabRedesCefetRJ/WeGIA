<?php

namespace api\modules\Socio;

use PDO;
use DateTime;

class VerificationCodeRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Save a verification code
     */
    public function save(int $idSocio, string $code, DateTime $expiresAt): bool
    {
        $query = "INSERT INTO socio_verification_code (id_socio, code, created_at, expires_at, verified) 
                  VALUES (:id_socio, :code, NOW(), :expires_at, 0)";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_socio' => $idSocio,
            ':code' => $code,
            ':expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get the latest valid verification code for a socio
     */
    public function getLatestCode(int $idSocio): ?array
    {
        $query = "SELECT * FROM socio_verification_code 
                  WHERE id_socio = :id_socio 
                  AND verified = 0
                  AND expires_at > NOW()
                  ORDER BY created_at DESC
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_socio' => $idSocio]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Verify if the code matches the socio id
     */
    public function verifyCode(int $idSocio, string $code): bool
    {
        $codeRecord = $this->getLatestCode($idSocio);
        
        if (!$codeRecord) {
            return false;
        }

        // Verify if code matches
        if ($codeRecord['code'] !== $code) {
            return false;
        }

        // Verify if not expired
        $expiresAt = new DateTime($codeRecord['expires_at']);
        if (new DateTime() > $expiresAt) {
            return false;
        }

        return true;
    }

    /**
     * Mark code as verified
     */
    public function markAsVerified(int $idSocio, string $code): bool
    {
        $query = "UPDATE socio_verification_code 
                  SET verified = 1, verified_at = NOW()
                  WHERE id_socio = :id_socio AND code = :code AND verified = 0";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id_socio' => $idSocio,
            ':code' => $code
        ]);
    }

    /**
     * Invalidate all previous codes for a socio
     */
    public function invalidatePreviousCodes(int $idSocio): bool
    {
        $query = "UPDATE socio_verification_code 
                  SET expires_at = NOW()
                  WHERE id_socio = :id_socio AND verified = 0 AND expires_at > NOW()";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id_socio' => $idSocio]);
    }

    /**
     * Get verified code for a socio
     */
    public function getVerifiedCode(int $idSocio): ?array
    {
        $query = "SELECT * FROM socio_verification_code 
                  WHERE id_socio = :id_socio 
                  AND verified = 1
                  ORDER BY verified_at DESC
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_socio' => $idSocio]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get socio email using id_socio through join
     */
    public function getSocioEmail(int $idSocio): ?string
    {
        $query = "SELECT p.email FROM socio_verification_code svc
                  JOIN socio s ON svc.id_socio = s.id_socio
                  JOIN pessoa p ON s.id_pessoa = p.id_pessoa
                  WHERE svc.id_socio = :id_socio
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_socio' => $idSocio]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['email'] ?? null;
    }
}
?>
