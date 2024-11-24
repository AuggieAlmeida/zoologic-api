<?php
namespace App\Models;

use App\Libraries\Database;
use PDOException;

class User
{
    private $db;
    private $table = 'users';

    public function __construct(Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function create($email, $password)
    {
        try {
            // Check if email already exists
            $checkStmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE email = :email");
            $checkStmt->execute([':email' => $email]);
            
            if ($checkStmt->fetch()) {
                throw new \Exception('Email already exists');
            }

            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (email, password) VALUES (:email, :password)"
            );
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            return $stmt->execute([
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            error_log("Database error in User::create: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log("Error in User::create: " . $e->getMessage());
            return false;
        }
    }

    public function read($id)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT id, email, created_at, updated_at FROM {$this->table} WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database error in User::read: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $email, $password)
    {
        try {
            // Check if email exists for other users
            $checkStmt = $this->db->prepare(
                "SELECT id FROM {$this->table} WHERE email = :email AND id != :id"
            );
            $checkStmt->execute([':email' => $email, ':id' => $id]);
            
            if ($checkStmt->fetch()) {
                throw new \Exception('Email already exists');
            }

            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET email = :email, password = :password WHERE id = :id"
            );
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            return $stmt->execute([
                ':id' => $id,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            error_log("Database error in User::update: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log("Error in User::update: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Database error in User::delete: " . $e->getMessage());
            return false;
        }
    }

    public function validate($email, $password)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT password FROM {$this->table} WHERE email = :email"
            );
            $stmt->execute([':email' => $email]);
            $hashedPassword = $stmt->fetchColumn();
            
            if (!$hashedPassword) {
                return false;
            }
            
            return password_verify($password, $hashedPassword);
        } catch (PDOException $e) {
            error_log("Database error in User::validate: " . $e->getMessage());
            return false;
        }
    }
}