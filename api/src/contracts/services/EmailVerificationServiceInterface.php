<?php

namespace api\contracts\services;

interface EmailVerificationServiceInterface
{
    /**
     * Generate and send verification code via email
     */
    public function generateAndSendCode(int $idSocio, string $email): array;

    /**
     * Verify if the code matches the socio id and is valid
     */
    public function verifyCode(int $idSocio, string $code): array;

    /**
     * Get code information
     */
    public function getCodeInfo(int $idSocio): ?array;
}
?>
