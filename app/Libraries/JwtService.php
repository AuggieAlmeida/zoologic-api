<?php
namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    public static function issue(int $userId, string $email): string
    {
        $now = time();
        return JWT::encode([
            'iss' => 'zoologic-api', 'iat' => $now, 'exp' => $now + 86400,
            'sub' => $userId, 'email' => $email,
        ], self::secret(), 'HS256');
    }

    public static function decode(string $token): object
    {
        return JWT::decode($token, new Key(self::secret(), 'HS256'));
    }

    private static function secret(): string
    {
        $secret = getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? '');
        if (!$secret) throw new \RuntimeException('JWT_SECRET não configurado');
        return $secret;
    }
}
