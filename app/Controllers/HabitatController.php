<?php

namespace App\Controllers;

use App\Models\Habitat;

class HabitatController extends Controller
{
    private $model;
    public function __construct(Habitat $model = null) { $this->model = $model ?? new Habitat(); }
    public function index() { $this->json($this->model->findAll()); }
    public function show($id) { $item = $this->model->findById($id); $item ? $this->json($item) : $this->json(['error' => 'Habitat não encontrado'], 404); }
    public function store() { $data = $this->getRequestData(); foreach (['nome','tipo','capacidade','localizacao'] as $field) if (!isset($data[$field]) || $data[$field] === '') return $this->json(['error' => "Campo obrigatório ausente: {$field}"], 400); $data['descricao'] = $data['descricao'] ?? ''; $this->model->create($data) ? $this->json(['message' => 'Habitat cadastrado com sucesso'], 201) : $this->json(['error' => 'Erro ao cadastrar habitat'], 500); }
    public function update($id) { $data = $this->getRequestData(); $data['descricao'] = $data['descricao'] ?? ''; $occupied = $this->model->countAnimals($id); if (isset($data['capacidade']) && (int) $data['capacidade'] < $occupied) return $this->json(['error' => "A capacidade não pode ser menor que os {$occupied} animais vinculados."], 409); $this->model->update($id, $data) ? $this->json(['message' => 'Habitat atualizado com sucesso']) : $this->json(['error' => 'Erro ao atualizar habitat'], 500); }
    public function destroy($id) { if ($this->model->countAnimals($id) > 0) return $this->json(['error' => 'Não é possível excluir um habitat vinculado a animais.'], 409); $this->model->delete($id) ? $this->json(['message' => 'Habitat excluído com sucesso']) : $this->json(['error' => 'Erro ao excluir habitat'], 500); }
}
