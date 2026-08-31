<?php

namespace api\modules\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthService
{
    private UserRepository $userRepository;
    private string $secret;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->secret = JWT_SECRET;
    }

    public function register(string $login, string $senha): array
    {
        $existingUser = $this->userRepository->findByLogin($login);
        if ($existingUser) {
            throw new \Exception('Usuário já existe');
        }

        // Validate password
        $confirmacaoSenha = $senha; // In register, password must be validated by caller, but we validate here
        $this->validatePassword($senha, $confirmacaoSenha);

        return $this->userRepository->save([
            'login' => $login,
            'senha' => \LoginHelper::hashPassword($senha)
        ]);

    }

    public function login(string $login, string $senha): array
    {
        $user = $this->userRepository->findByLogin($login);
        if (!$user) {
            throw new \Exception('Credenciais inválidas');
        }

        $passwordCheck = \LoginHelper::verifyAndMigrate($senha, $user['senha'] ?? null);

        if (!$passwordCheck['valid']) {
            throw new \Exception('Credenciais inválidas');
        }

        if ($passwordCheck['updated_hash'] !== null) {
            $this->userRepository->updatePasswordHash((int) $user['id_pessoa'], $passwordCheck['updated_hash']);
        }

        return $this->generateTokens($user['id_pessoa']);
    }

    public function refreshToken(string $token): array
    {
        try {
            $decoded = $this->validateToken($token);
            $userId = $decoded->sub;

            return $this->generateTokens($userId);
        } catch (\Exception $e) {
            throw new \Exception('Token inválido ou expirado');
        }
    }

    private function generateTokens(int $userId): array
    {
        $now = time();

        // Access token - 15 minutos
        $accessPayload = [
            'iss' => 'wegia',
            'aud' => 'wegia-users',
            'iat' => $now,
            'exp' => $now + 900,
            'sub' => $userId,
            'type' => 'access'
        ];

        $accessToken = JWT::encode($accessPayload, $this->secret, 'HS256');

        // Refresh token - 7 dias
        $refreshPayload = [
            'iss' => 'wegia',
            'aud' => 'wegia-users',
            'iat' => $now,
            'exp' => $now + 604800,
            'sub' => $userId,
            'type' => 'refresh'
        ];

        $refreshToken = JWT::encode($refreshPayload, $this->secret, 'HS256');

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 900,
            'token_type' => 'Bearer'
        ];
    }

    public function validateToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (ExpiredException $e) {
            throw new \Exception('Token expirado');
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Assinatura inválida');
        } catch (\Exception $e) {
            throw new \Exception('Token inválido');
        }
    }

    public function logout(string $token): array
    {
        try {
            $decoded = $this->validateToken($token);
            
            // Token é válido, logout realizado
            return [
                'message' => 'Logout realizado com sucesso',
                'user_id' => $decoded->sub
            ];
        } catch (\Exception $e) {
            throw new \Exception('Token inválido ou expirado');
        }
    }

    public function assignPasswordToPerson(int $idPessoa, string $senha): void
    {
        if ($idPessoa <= 0) {
            throw new \Exception('ID da pessoa inválido');
        }

        // Validate password
        $confirmacaoSenha = $senha; // In this context, password must be validated by caller, but we validate here
        $this->validatePassword($senha, $confirmacaoSenha);

        $hashedPassword = \LoginHelper::hashPassword($senha);
        $this->userRepository->updatePasswordHash($idPessoa, $hashedPassword);
    }

    /**
     * Validate a new password with confirmation
     * Throws exception if validation fails
     * 
     * @param string $senha The new password
     * @param string $confirmacaoSenha The password confirmation
     * @throws \Exception If password validation fails
     */
    private function validatePassword(string $senha, string $confirmacaoSenha): void
    {
        // Validate password is not empty
        if (empty($senha)) {
            throw new \Exception('Password cannot be empty');
        }

        // Validate that passwords match
        if ($senha !== $confirmacaoSenha) {
            throw new \Exception('Passwords do not match');
        }

        // Validate password minimum length (at least 8 characters)
        if (strlen($senha) < 8) {
            throw new \Exception('Password must be at least 8 characters long');
        }

        // Validate uppercase letters
        if (!preg_match('/[A-Z]/', $senha)) {
            throw new \Exception('Password must contain at least one uppercase letter');
        }

        // Validate lowercase letters
        if (!preg_match('/[a-z]/', $senha)) {
            throw new \Exception('Password must contain at least one lowercase letter');
        }

        // Validate numbers
        if (!preg_match('/[0-9]/', $senha)) {
            throw new \Exception('Password must contain at least one number');
        }

        // Validate special characters
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $senha)) {
            throw new \Exception('Password must contain at least one special character (!@#$%^&*()_+-=[]{};\':\",./<>?/\\|`~)');
        }
    }
}
