<?php

namespace App\Models;

use App\Libraries\Database;

class Animal
{
    private $db;
    
    public function __construct(Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO animais (nome, especie, setor, tipo, habitat, idade, peso, alimentacao, 
                status, sexo, observacoes, foto) 
                VALUES (:nome, :especie, :setor, :tipo, :habitat, :idade, :peso, :alimentacao, 
                :status, :sexo, :observacoes, :foto)";
                
        return $this->db->execute($sql, $data);
    }

    public function findAll()
    {
        $sql = "SELECT id_animal, nome, especie, setor, tipo, habitat, idade, peso, alimentacao, 
                status, sexo, observacoes, foto 
                FROM animais";
        return $this->db->query($sql);
    }

    public function findById($id)
    {
        $sql = "SELECT id_animal, nome, especie, setor, tipo, habitat, idade, peso, alimentacao, 
                status, sexo, observacoes, foto 
                FROM animais WHERE id_animal = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE animais 
                SET especie = :especie, setor = :setor, tipo = :tipo, habitat = :habitat,
                    idade = :idade, peso = :peso, alimentacao = :alimentacao,
                    status = :status, sexo = :sexo, observacoes = :observacoes,
                    foto = :foto 
                WHERE id_animal = :id";
                
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM animais WHERE id_animal = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
} 