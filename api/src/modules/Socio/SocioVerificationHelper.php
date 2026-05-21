<?php

namespace api\modules\Socio;

use api\contracts\services\PessoaServiceInterface;
use api\utils\Cpf;

/**
 * Helper class for common socio verification operations
 * Consolidates code used across multiple endpoints for CPF validation,
 * person/socio lookup, and verification code handling
 */
class SocioVerificationHelper
{
    private PessoaServiceInterface $pessoaService;
    private SocioService $socioService;
    private EmailVerificationService $emailVerificationService;

    public function __construct(
        PessoaServiceInterface $pessoaService,
        SocioService $socioService,
        EmailVerificationService $emailVerificationService
    ) {
        $this->pessoaService = $pessoaService;
        $this->socioService = $socioService;
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * Validates and retrieves a socio by CPF
     * 
     * @param string $cpf The CPF to validate and search
     * @return array Result containing pessoa, socio, and message
     *   [
     *     'pessoa' => PessoaInterface|null,
     *     'socio' => SocioInterface|null,
     *     'message' => string|null
     *   ]
     */
    public function findSocioByCpf(string $cpf): array
    {
        // Validate CPF
        if (!Cpf::validate($cpf)) {
            return [
                'pessoa' => null,
                'socio' => null,
                'message' => 'CPF inválido.'
            ];
        }

        $cpf = Cpf::normalize($cpf);
        $pessoa = $this->pessoaService->obterPessoaPorCpf($cpf);

        if (!$pessoa) {
            return [
                'pessoa' => null,
                'socio' => null,
                'message' => 'Pessoa não localizada.'
            ];
        }

        $socio = $this->socioService->obterSocioPorPessoaId($pessoa->getId(), $pessoa);

        if (!$socio) {
            return [
                'pessoa' => $pessoa,
                'socio' => null,
                'message' => 'Sócio não localizado.'
            ];
        }

        return [
            'pessoa' => $pessoa,
            'socio' => $socio,
            'message' => null
        ];
    }

    /**
     * Validates CPF format and existence
     * Returns a standardized error response if invalid
     * 
     * @param string $cpf The CPF to validate
     * @return array|null Error response if invalid, null if valid
     */
    public function validateCpf(string $cpf): ?array
    {
        if (empty($cpf)) {
            return [
                'success' => false,
                'message' => 'CPF é obrigatório'
            ];
        }

        if (!Cpf::validate($cpf)) {
            return [
                'success' => false,
                'message' => 'CPF inválido.'
            ];
        }

        return null;
    }

    /**
     * Sends a verification code to a socio's email
     * Automatically invalidates previous codes
     * 
     * @param int $idSocio The socio ID
     * @param string $email The email to send to
     * @return array Result array with success status and message
     */
    public function sendVerificationCode(int $idSocio, string $email): array
    {
        return $this->emailVerificationService->generateAndSendCode($idSocio, $email);
    }

    /**
     * Verifies a verification code
     * 
     * @param int $idSocio The socio ID
     * @param string $code The code to verify
     * @return array Result array with success status and message
     */
    public function verifyCode(int $idSocio, string $code): array
    {
        return $this->emailVerificationService->verifyCode($idSocio, $code);
    }

    /**
     * Gets socio data by CPF including pessoa information
     * 
     * @param string $cpf The CPF to search
     * @return array|null Structured response with socio and pessoa data, null if not found
     */
    public function getSocioDataByCpf(string $cpf): ?array
    {
        $result = $this->findSocioByCpf($cpf);

        if (!$result['socio'] || !$result['pessoa']) {
            return null;
        }

        return [
            'pessoa' => $result['pessoa'],
            'socio' => $result['socio']
        ];
    }
}
?>
