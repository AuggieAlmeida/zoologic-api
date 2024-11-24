<?php

namespace App\Models;

use App\Libraries\Database;

class Colaborador
{
    private $db;
    
    public function __construct(Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO colaboradores (nome, email, senha, funcao, salario) 
                VALUES (:nome, :email, :senha, :funcao, :salario)";
                
        $senha = password_hash($data['senha'], PASSWORD_DEFAULT);
        
        $params = [
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => $senha,
            'funcao' => $data['funcao'],
            'salario' => $data['salario']
        ];

        return $this->db->execute($sql, $params);
    }

    public function findAll()
    {
        $sql = "SELECT id, nome, email, funcao, salario, created_at, updated_at 
                FROM colaboradores";
        return $this->db->query($sql);
    }

    public function findById($id)
    {
        $sql = "SELECT id, nome, email, funcao, salario, created_at, updated_at 
                FROM colaboradores WHERE id = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE colaboradores 
                SET nome = :nome, email = :email, funcao = :funcao, salario = :salario 
                WHERE id = :id";
                
        $params = [
            'id' => $id,
            'nome' => $data['nome'],
            'email' => $data['email'],
            'funcao' => $data['funcao'],
            'salario' => $data['salario']
        ];

        return $this->db->execute($sql, $params);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM colaboradores WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
} 