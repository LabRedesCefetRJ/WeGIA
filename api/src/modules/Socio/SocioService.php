<?php

namespace api\modules\Socio;

use api\contracts\entities\PessoaInterface;
use api\contracts\entities\SocioInterface;
use api\contracts\services\SocioServiceInterface;
use api\modules\Auth\AuthService;
use DateTime;

class SocioService implements SocioServiceInterface
{
    private SocioRepository $socioRepository;
    private EmailVerificationService $emailVerificationService;
    private AuthService $authService;

    public function __construct(SocioRepository $socioRepository, EmailVerificationService $emailVerificationService = null, AuthService $authService = null)
    {
        $this->socioRepository = $socioRepository;
        $this->emailVerificationService = $emailVerificationService;
        $this->authService = $authService;
    }

    public function criarSocio(PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade,int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface
    {
        $socio = new Socio($pessoa, $inicioContribuicao, $valorMensalidade, $idSocioStatus, $autoStatusContribuicao, $idSocioTipo);
        return $this->socioRepository->save($socio);
    }

    public function obterSocioPorId(int $id): ?SocioInterface
    {
        // Lógica para obter um sócio por ID
        // Exemplo: consultar o banco de dados e retornar o objeto Sócio correspondente ou null se não encontrado
        throw new \Exception("Método obterSocioPorId ainda não implementado", 501);
    }

    public function obterSocioPorPessoaId(int $idPessoa, PessoaInterface $pessoa): ?SocioInterface
    {
        $resultado = $this->socioRepository->findByPessoaId($idPessoa);

        if (!$resultado) {
            return null;
        }

        return new Socio(
            $pessoa,
            new DateTime($resultado['data_referencia']),
            (float)$resultado['valor_periodo'],
            (int)$resultado['id_sociostatus'],
            (bool)$resultado['auto_status_contribuicoes'],
            (int)$resultado['id_sociotipo'],
            (int)$resultado['id_socio']
        );
    }

    public function atualizarSocio(int $id, PessoaInterface $pessoa, DateTime $inicioContribuicao, float $valorMensalidade,int $idSocioStatus = 1, bool $autoStatusContribuicao = true, int $idSocioTipo = 0): SocioInterface
    {
        // Lógica para atualizar um sócio existente
        // Exemplo: validar dados, atualizar o objeto Sócio e salvar as alterações no banco de dados
        throw new \Exception("Método atualizarSocio ainda não implementado", 501);
    }

    public function deletarSocio(int $id): bool
    {
        // Lógica para deletar um sócio por ID
        // Exemplo: anonimizar o registro do banco de dados e retornar true se a operação foi bem-sucedida ou false caso contrário
        throw new \Exception("Método deletarSocio ainda não implementado", 501);
    }

    /**
     * Alter password of a socio using a verification code
     * 
     * @param int $idSocio The ID of the socio
     * @param int $idPessoa The ID of the pessoa (user) associated with the socio
     * @param string $senha The new password
     * @param string $confirmacaoSenha The password confirmation
     * @param string $code The verification code
     * @return array Result array with success status and message
     */
    public function alterPassword(int $idSocio, string $senha, string $confirmacaoSenha, string $code): array
    {
        try {
            // Validate that both email verification and auth services are available
            if ($this->emailVerificationService === null || $this->authService === null) {
                return [
                    'success' => false,
                    'message' => 'Required services not available'
                ];
            }

            // Verify password and confirmation are equals
            if ($senha !== $confirmacaoSenha) {
                return [
                    'success' => false,
                    'message' => 'Passwords do not match'
                ];
            }

            // Verify the code
            $verifyResult = $this->emailVerificationService->verifyCode($idSocio, $code);
            if (!$verifyResult['success']) {
                return $verifyResult;
            }

            //get id_pessoa associated with id_socio
            $idPessoa = $this->socioRepository->getIdPessoaByIdSocio($idSocio);
            if (!$idPessoa) {
                return [
                    'success' => false,
                    'message' => 'Pessoa not found for the given socio ID'
                ];
            }

            // Update the password
            try {
                $this->authService->assignPasswordToPerson($idPessoa, $senha);
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error updating password: ' . $e->getMessage()
                ];
            }

            // Mark the code as used
            $markResult = $this->emailVerificationService->markCodeAsUsed($idSocio, $code);
            if (!$markResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Password updated but error marking code as used: ' . $markResult['message']
                ];
            }

            return [
                'success' => true,
                'message' => 'Password altered successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error altering password: ' . $e->getMessage()
            ];
        }
    }
}
