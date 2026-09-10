<?php

namespace App\Models;

use App\Libraries\Database;

class Habitat
{
    private $db;

    public function __construct(Database $db = null) { $this->db = $db ?? Database::getInstance(); }
    public function findAll() { return $this->db->query('SELECT id, nome, tipo, capacidade, localizacao, descricao FROM habitats ORDER BY nome'); }
    public function findById($id) { return $this->db->queryOne('SELECT id, nome, tipo, capacidade, localizacao, descricao FROM habitats WHERE id = :id', ['id' => $id]); }
    public function create($data) { return $this->db->execute('INSERT INTO habitats (nome, tipo, capacidade, localizacao, descricao) VALUES (:nome, :tipo, :capacidade, :localizacao, :descricao)', $data); }
    public function update($id, $data) { $data['id'] = $id; return $this->db->execute('UPDATE habitats SET nome=:nome, tipo=:tipo, capacidade=:capacidade, localizacao=:localizacao, descricao=:descricao WHERE id=:id', $data); }
    public function countAnimals($id) { $result = $this->db->queryOne('SELECT COUNT(*) FROM animais WHERE habitat_id = :id', ['id' => $id]); return (int) ($result['COUNT(*)'] ?? 0); }
    public function delete($id) { return $this->db->execute('DELETE FROM habitats WHERE id = :id', ['id' => $id]); }
}
