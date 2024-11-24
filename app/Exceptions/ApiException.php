<?php
namespace App\Exceptions;

class ApiException
{
    public static function errorHandler($severity, $message, $file, $line)
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function exceptionHandler($exception)
    {
        http_response_code(500);
        echo json_encode([
            'error' => [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]
        ]);
    }
} 