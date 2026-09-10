<?php
namespace App\Middleware;

use App\Libraries\JwtService;

class AuthMiddleware
{
    public static function requireAuth(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            self::reject();
        }
        try {
            $claims = JwtService::decode($matches[1]);
            $_REQUEST['auth'] = $claims;
        } catch (\Throwable $e) {
            self::reject();
        }
    }

    private static function reject(): never
    {
        http_response_code(401);
        echo json_encode(['error' => 'Autenticação necessária']);
        exit;
    }
}
