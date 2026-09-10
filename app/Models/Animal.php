<?php

namespace App\Models;

use App\Libraries\Database;

class Animal
{
    private $db;
    
    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function create($data)
    {
        $sql = "INSERT INTO animais (nome, especie, setor, tipo, habitat_id, idade, peso, alimentacao, 
                status, sexo, observacoes, foto) 
                VALUES (:nome, :especie, :setor, :tipo, :habitat_id, :idade, :peso, :alimentacao, 
                :status, :sexo, :observacoes, :foto)";
                
        return $this->db->execute($sql, $data);
    }

    public function findAll()
    {
        $sql = "SELECT a.id_animal, a.nome, a.especie, a.setor, a.tipo, a.habitat_id,
                h.nome AS habitat, a.idade, a.peso, a.alimentacao,
                a.status, a.sexo, a.observacoes, a.foto
                FROM animais a INNER JOIN habitats h ON h.id = a.habitat_id";
        return $this->db->query($sql);
    }

    public function findById($id)
    {
        $sql = "SELECT a.id_animal, a.nome, a.especie, a.setor, a.tipo, a.habitat_id,
                h.nome AS habitat, a.idade, a.peso, a.alimentacao,
                a.status, a.sexo, a.observacoes, a.foto
                FROM animais a INNER JOIN habitats h ON h.id = a.habitat_id
                WHERE a.id_animal = :id";
        return $this->db->queryOne($sql, ['id' => $id]);
    }

    public function findHabitat($id)
    {
        return $this->db->queryOne(
            'SELECT id, capacidade FROM habitats WHERE id = :id',
            ['id' => $id]
        );
    }

    public function countByHabitat($habitatId, $excludeAnimalId = null)
    {
        $sql = 'SELECT COUNT(*) FROM animais WHERE habitat_id = :habitat_id';
        $params = ['habitat_id' => $habitatId];
        if ($excludeAnimalId !== null) {
            $sql .= ' AND id_animal <> :exclude_id';
            $params['exclude_id'] = $excludeAnimalId;
        }
        $result = $this->db->queryOne($sql, $params);
        return (int) ($result['COUNT(*)'] ?? 0);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE animais 
                SET nome = :nome, especie = :especie, setor = :setor, tipo = :tipo, habitat_id = :habitat_id,
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
