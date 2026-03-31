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

        $payload = [
            'iss' => 'wegia',
            'aud' => 'wegia-users',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $user['id']
        ];

        $token = JWT::encode($payload, $this->secret, 'HS256');

        return [
            'token' => $token
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
}
