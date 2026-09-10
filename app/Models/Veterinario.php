<?php

namespace App\Models;

use App\Libraries\Database;

class Veterinario
{
    private $db;

    public function __construct(Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO veterinarios (nome, email, crmv, especialidade, telefone)
                VALUES (:nome, :email, :crmv, :especialidade, :telefone)";
        return $this->db->execute($sql, $data);
    }

    public function findAll()
    {
        return $this->db->query(
            "SELECT id, nome, email, crmv, especialidade, telefone, created_at, updated_at
             FROM veterinarios ORDER BY nome"
        );
    }

    public function findById($id)
    {
        return $this->db->queryOne(
            "SELECT id, nome, email, crmv, especialidade, telefone, created_at, updated_at
             FROM veterinarios WHERE id = :id",
            ['id' => $id]
        );
    }

    public function update($id, $data)
    {
        $data['id'] = $id;
        return $this->db->execute(
            "UPDATE veterinarios
             SET nome = :nome, email = :email, crmv = :crmv,
                 especialidade = :especialidade, telefone = :telefone
             WHERE id = :id",
            $data
        );
    }

    public function delete($id)
    {
        return $this->db->execute("DELETE FROM veterinarios WHERE id = :id", ['id' => $id]);
    }
}
