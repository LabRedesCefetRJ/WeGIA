<?php

namespace api\modules\Socio;

use api\contracts\services\EmailVerificationServiceInterface;
use DateTime;

class EmailVerificationService implements EmailVerificationServiceInterface
{
    private VerificationCodeRepository $repository;
    private string $emailFromName = 'WeGIA';
    private int $codeValidityMinutes = 15; // Default validity: 15 minutes

    public function __construct(VerificationCodeRepository $repository, int $codeValidityMinutes = 15, string $emailFromName = 'WeGIA')
    {
        $this->repository = $repository;
        $this->codeValidityMinutes = $codeValidityMinutes;
        $this->emailFromName = $emailFromName;
    }

    /**
     * Generate a random 6-digit code
     */
    private function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and send verification code via email
     */
    public function generateAndSendCode(int $idSocio, string $email): array
    {
        try {
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email'
                ];
            }

            // Invalidate previous codes
            $this->repository->invalidatePreviousCodes($idSocio);

            // Generate new code
            $code = $this->generateCode();
            
            // Set expiration date
            $expiresAt = new DateTime();
            $expiresAt->modify("+{$this->codeValidityMinutes} minutes");

            // Save code to database
            $saved = $this->repository->save($idSocio, $code, $expiresAt);

            if (!$saved) {
                return [
                    'success' => false,
                    'message' => 'Error saving verification code'
                ];
            }

            // Prepare email message
            $message = $this->prepareEmailMessage($code);

            // Send email using EmailControle
            $emailControl = $this->getEmailControl();
            $result = $emailControl->enviarEmail(
                $email,
                'Verification Code - WeGIA',
                $message,
                ''
            );

            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => 'Error sending email: ' . $result['message']
                ];
            }

            return [
                'success' => true,
                'message' => 'Code sent successfully to ' . $email,
                'validity_minutes' => $this->codeValidityMinutes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating and sending code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Prepare email message with the code
     */
    private function prepareEmailMessage(string $code): string
    {
        $codeFormatted = implode(' ', str_split($code));
        
        return "
        <p>Hello,</p>
        
        <p>Your verification code is:</p>
        
        <h2 style='text-align: center; letter-spacing: 5px; color: #2657dcff;'>
            {$codeFormatted}
        </h2>
        
        <p>This code is valid for {$this->codeValidityMinutes} minutes.</p>
        
        <p><strong>Do not share this code with anyone.</strong></p>
        
        <p>If you did not request this code, ignore this email.</p>
        
        <p>Best regards,<br>Team {$this->emailFromName}</p>
        ";
    }

    /**
     * Verify if the code matches the socio id and is valid
     * Note: This method only validates the code, it does NOT mark it as used.
     * The code will be marked as used only when it's used to change the password.
     */
    public function verifyCode(int $idSocio, string $code): array
    {
        try {
            // Validate code format
            if (!preg_match('/^\d{6}$/', $code)) {
                return [
                    'success' => false,
                    'message' => 'Code must contain 6 digits'
                ];
            }

            // Verify code validity
            $isValid = $this->repository->verifyCode($idSocio, $code);

            if (!$isValid) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired code'
                ];
            }

            return [
                'success' => true,
                'message' => 'Code is valid'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error verifying code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mark code as used (when it's used for password change)
     */
    public function markCodeAsUsed(int $idSocio, string $code): array
    {
        try {
            // Mark code as used
            $marked = $this->repository->markAsUsed($idSocio, $code);

            if (!$marked) {
                return [
                    'success' => false,
                    'message' => 'Error marking code as used'
                ];
            }

            return [
                'success' => true,
                'message' => 'Code marked as used successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error marking code as used: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get code information
     */
    public function getCodeInfo(int $idSocio): ?array
    {
        $code = $this->repository->getLatestCode($idSocio);
        
        if (!$code) {
            return null;
        }

        $expiresAt = new DateTime($code['expires_at']);
        $timeRemaining = $expiresAt->diff(new DateTime());

        return [
            'id_socio' => $code['id_socio'],
            'created_at' => $code['created_at'],
            'expires_at' => $code['expires_at'],
            'code_used' => (bool)$code['code_used'],
            'remaining_minutes' => $timeRemaining->format('%i')
        ];
    }

    /**
     * Get EmailControle instance
     * Necessary to avoid unnecessary dependency loading
     */
    private function getEmailControl()
    {
        // Load config from appropriate path depending on execution context
        $config_path = "config.php";
        if (!file_exists($config_path)) {
            $config_path = "../../web/config.php";
        }
        if (!file_exists($config_path)) {
            $config_path = "../../../web/config.php";
        }

        if (file_exists($config_path)) {
            require_once $config_path;
        }

        // Try to find and load EmailControle
        $emailControlPath = dirname(__FILE__, 5) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . 'controle' . DIRECTORY_SEPARATOR . 'EmailControle.php';

        if (file_exists($emailControlPath)) {
            require_once $emailControlPath;
            return new \EmailControle();
            }

        throw new \Exception('EmailControle not found' . (dirname(__DIR__, 3) . '/web/controle/EmailControle.php'));
    }
}
?>
