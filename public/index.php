<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Config;
use App\Routes\Router;
use App\Exceptions\ApiException;

// Carrega variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configurações de CORS dinâmicas
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowed_origins = [
        'http://localhost:3000',
        'http://172.19.0.1:3000',
        'http://10.0.0.2:3000',
        'http://192.168.15.128:3000'
    ];
    
    if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
}

// Tratamento de requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    exit(0);
}

// Resto do seu código
header('Content-Type: application/json; charset=UTF-8');

set_error_handler([ApiException::class, 'errorHandler']);
set_exception_handler([ApiException::class, 'exceptionHandler']);

$router = new Router();
require_once __DIR__ . '/../app/Routes/api.php';

$router->resolve(); 