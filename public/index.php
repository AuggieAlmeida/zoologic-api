<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Exceptions\ApiException;
use App\Middleware\CorsMiddleware;
use App\Routes\Router;

// Load the .env file when it exists. On managed platforms the configuration
// arrives as real environment variables and no file is shipped, so a strict
// load() would abort every request.
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

// CORS lives in a single place: the middleware. It answers and terminates
// preflight requests.
(new CorsMiddleware())->handle();

header('Content-Type: application/json; charset=UTF-8');

set_error_handler([ApiException::class, 'errorHandler']);
set_exception_handler([ApiException::class, 'exceptionHandler']);

$router = new Router();
require_once __DIR__ . '/../app/Routes/api.php';

$router->resolve();
