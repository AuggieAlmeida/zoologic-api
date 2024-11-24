<?php
namespace App\Controllers;

abstract class Controller
{
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }

    protected function getRequestData()
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
} 