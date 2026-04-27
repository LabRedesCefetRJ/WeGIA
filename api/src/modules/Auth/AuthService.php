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

        return $this->userRepository->save([
            'login' => $login,
            'senha' => password_hash($senha, PASSWORD_DEFAULT)
        ]);

    }

    public function login(string $login, string $senha): array
    {
        $user = $this->userRepository->findByLogin($login);
        if (!$user) {
            throw new \Exception('Credenciais inválidas');
        }

        if (!password_verify($senha, $user['senha'])) {
            throw new \Exception('Credenciais inválidas');
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
}
