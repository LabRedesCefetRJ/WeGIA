<?php

namespace api\modules\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthService
{
    private string $secret;

    public function __construct()
    {
        $this->secret = JWT_SECRET;
    }

    public function login(string $login, string $senha): array
    {
        // MOCK (trocar por banco depois)
        $usuarioFake = [
            'id' => 1,
            'login' => 'admin',
            'senha' => password_hash('123456', PASSWORD_DEFAULT)
        ];

        if ($login !== $usuarioFake['login'] || !password_verify($senha, $usuarioFake['senha'])) {
            throw new \Exception('Credenciais inválidas');
        }

        $payload = [
            'iss' => 'wegia',
            'aud' => 'wegia-users',
            'iat' => time(),
            'exp' => time() + 3600,
            'sub' => $usuarioFake['id']
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
