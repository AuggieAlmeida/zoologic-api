<?php

namespace App\Middleware;

use App\Config\Config;

class CorsMiddleware
{
    private const DEFAULT_ORIGINS = 'http://localhost:3000';

    public function handle()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // The API answers credentialed requests, so the wildcard origin is not
        // an option: the browser rejects it. Only an origin on the allowlist is
        // echoed back, and the list comes from the environment so a deployed
        // front end can be added without a code change.
        if ($origin !== '' && in_array($origin, $this->allowedOrigins(), true)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
        }

        // The response varies with the request origin even when it is refused,
        // otherwise a shared cache can serve one origin's headers to another.
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * @return string[] exact origins allowed to send credentialed requests
     */
    private function allowedOrigins()
    {
        $configured = Config::get('CORS_ALLOWED_ORIGINS', self::DEFAULT_ORIGINS);

        $origins = array_map('trim', explode(',', (string) $configured));

        return array_values(array_filter($origins, function ($origin) {
            return $origin !== '';
        }));
    }
}
