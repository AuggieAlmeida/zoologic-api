<?php
namespace App\Routes;

// Importando os controladores
use App\Controllers\UserController;
use App\Controllers\ColaboradorController;
use App\Controllers\AnimalController;

class Router
{
    private $routes = [];

    public function add($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function get($path, $handler)
    {
        $this->add('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        $this->add('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        $this->add('DELETE', $path, $handler);
    }

    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            $result = $this->matchPath($route['path'], $path);
            if ($route['method'] === $method && $result['match']) {
                return $this->handleRequest($route['handler'], $result['params']);
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    private function matchPath($routePath, $requestPath)
    {
        $pattern = preg_replace('/\/{([^\/]+)}/', '/([^/]+)', $routePath);
        
        if (preg_match("#^$pattern$#", $requestPath, $matches)) {
            array_shift($matches);
            return ['match' => true, 'params' => $matches];
        }
        
        return ['match' => false, 'params' => []];
    }

    private function handleRequest($handler, $params = [])
    {
        if (is_array($handler)) {
            [$controller, $method] = $handler;
            $controller = new $controller();
            return $controller->$method(...$params);
        }
        return $handler();
    }

    // Método para inicializar as rotas
    public function initializeRoutes()
    {
        // Adicionando rotas para cadastro e login
        $this->post('/api/register', [UserController::class, 'register']);
        $this->post('/api/login', [UserController::class, 'login']);
        
        // Adicionando rotas para colaboradores
        $this->get('/api/colaboradores', [ColaboradorController::class, 'index']);
        $this->get('/api/colaboradores/{id}', [ColaboradorController::class, 'show']);
        $this->post('/api/colaboradores', [ColaboradorController::class, 'store']);
        $this->put('/api/colaboradores/{id}', [ColaboradorController::class, 'update']);
        $this->delete('/api/colaboradores/{id}', [ColaboradorController::class, 'destroy']);

        // Adicionando rotas para animais
        $this->get('/api/animais', [AnimalController::class, 'index']);
        $this->get('/api/animais/{id}', [AnimalController::class, 'show']);
        $this->post('/api/animais', [AnimalController::class, 'store']);
        $this->put('/api/animais/{id}', [AnimalController::class, 'update']);
        $this->delete('/api/animais/{id}', [AnimalController::class, 'destroy']);

        // Rota de health check
        $this->get('/api/health', function() {
            echo json_encode(['status' => 'OK', 'message' => 'API is running']);
        });
    }
}

// Instanciando o Router e inicializando as rotas
$router = new Router();
$router->initializeRoutes(); 