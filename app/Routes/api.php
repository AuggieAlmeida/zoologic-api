<?php

use App\Controllers\ColaboradorController;
use App\Controllers\AnimalController;
use App\Controllers\UserController;
use App\Controllers\VeterinarioController;
use App\Controllers\HabitatController;

$router->get('/api/habitats', [HabitatController::class, 'index']);
$router->get('/api/habitats/{id}', [HabitatController::class, 'show']);
$router->post('/api/habitats', [HabitatController::class, 'store']);
$router->put('/api/habitats/{id}', [HabitatController::class, 'update']);
$router->delete('/api/habitats/{id}', [HabitatController::class, 'destroy']);

// Rotas para Colaboradores
$router->get('/api/colaboradores', [ColaboradorController::class, 'index']);
$router->get('/api/colaboradores/{id}', [ColaboradorController::class, 'show']);
$router->post('/api/colaboradores', [ColaboradorController::class, 'store']);
$router->put('/api/colaboradores/{id}', [ColaboradorController::class, 'update']);
$router->put('/api/colaboradores/{id}/delegar', [ColaboradorController::class, 'delegar']);
$router->delete('/api/colaboradores/{id}', [ColaboradorController::class, 'destroy']);

// Rotas para Animais
$router->get('/api/animais', [AnimalController::class, 'index']);
$router->get('/api/animais/{id}', [AnimalController::class, 'show']);
$router->post('/api/animais', [AnimalController::class, 'store']);
$router->put('/api/animais/{id}', [AnimalController::class, 'update']);
$router->delete('/api/animais/{id}', [AnimalController::class, 'destroy']);

// Rotas para Veterinários
$router->get('/api/veterinarios', [VeterinarioController::class, 'index']);
$router->get('/api/veterinarios/{id}', [VeterinarioController::class, 'show']);
$router->post('/api/veterinarios', [VeterinarioController::class, 'store']);
$router->put('/api/veterinarios/{id}', [VeterinarioController::class, 'update']);
$router->delete('/api/veterinarios/{id}', [VeterinarioController::class, 'destroy']);

// Rotas para Usuários
$router->post('/api/register', [UserController::class, 'register']);
$router->post('/api/login', [UserController::class, 'login']);
$router->get('/api/users/{id}', [UserController::class, 'getUser']);
$router->put('/api/users/{id}', [UserController::class, 'updateUser']);
$router->delete('/api/users/{id}', [UserController::class, 'deleteUser']);

// Rota de health check existente
$router->get('/api/health', function() {
    echo json_encode(['status' => 'OK', 'message' => 'API is running']);
}); 
