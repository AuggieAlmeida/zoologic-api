<?php
namespace App\Controllers;

use App\Models\User;
use Exception;

class UserController
{
    public function register()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            if (empty($data['email']) || empty($data['password'])) {
                throw new Exception('Email and password are required');
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            if (strlen($data['password']) < 6) {
                throw new Exception('Password must be at least 6 characters long');
            }

            $user = new User();
            $result = $user->create($data['email'], $data['password']);

            if (!$result) {
                throw new Exception('Failed to create user');
            }

            http_response_code(201);
            echo json_encode(['message' => 'User successfully registered']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            error_log("Registration error: " . $e->getMessage());
        }
    }

    public function login()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            if (empty($data['email']) || empty($data['password'])) {
                throw new Exception('Email and password are required');
            }

            $user = new User();
            $isValid = $user->validate($data['email'], $data['password']);

            if (!$isValid) {
                throw new Exception('Invalid credentials');
            }

            // Here you would typically generate a JWT or session token
            echo json_encode(['message' => 'Login successful']);
            
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => $e->getMessage()]);
            error_log("Login error: " . $e->getMessage());
        }
    }

    public function getUser($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid user ID');
            }

            $user = new User();
            $data = $user->read($id);
            
            if (!$data) {
                throw new Exception('User not found');
            }

            echo json_encode($data);
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(['error' => $e->getMessage()]);
            error_log("Get user error: " . $e->getMessage());
        }
    }

    public function updateUser($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid user ID');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception('Invalid JSON data');
            }
            
            if (empty($data['email']) || empty($data['password'])) {
                throw new Exception('Email and password are required');
            }

            $user = new User();
            $result = $user->update($id, $data['email'], $data['password']);

            if (!$result) {
                throw new Exception('Failed to update user');
            }

            echo json_encode(['message' => 'User successfully updated']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            error_log("Update user error: " . $e->getMessage());
        }
    }

    public function deleteUser($id)
    {
        try {
            if (!$id || !is_numeric($id)) {
                throw new Exception('Invalid user ID');
            }

            $user = new User();
            $result = $user->delete($id);

            if (!$result) {
                throw new Exception('Failed to delete user');
            }

            echo json_encode(['message' => 'User successfully deleted']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
            error_log("Delete user error: " . $e->getMessage());
        }
    }
}