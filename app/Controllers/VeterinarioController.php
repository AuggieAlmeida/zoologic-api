<?php

namespace App\Controllers;

use App\Models\Veterinario;

class VeterinarioController extends Controller
{
    private $veterinarioModel;

    public function __construct(Veterinario $veterinarioModel = null)
    {
        $this->veterinarioModel = $veterinarioModel ?? new Veterinario();
    }

    public function index()
    {
        $this->json($this->veterinarioModel->findAll());
    }

    public function show($id)
    {
        $veterinario = $this->veterinarioModel->findById($id);
        if (!$veterinario) {
            return $this->json(['error' => 'Veterinário não encontrado'], 404);
        }
        $this->json($veterinario);
    }

    public function store()
    {
        $data = $this->getRequestData();
        foreach (['nome', 'email', 'crmv', 'especialidade'] as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return $this->json(['error' => "Campo obrigatório ausente: {$field}"], 400);
            }
        }
        $data['telefone'] = $data['telefone'] ?? null;

        if ($this->veterinarioModel->create($data)) {
            return $this->json(['message' => 'Veterinário cadastrado com sucesso'], 201);
        }
        $this->json(['error' => 'Erro ao cadastrar veterinário'], 500);
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        foreach (['nome', 'email', 'crmv', 'especialidade'] as $field) {
            if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
                return $this->json(['error' => "Campo obrigatório ausente: {$field}"], 400);
            }
        }
        $data['telefone'] = $data['telefone'] ?? null;

        if (!$this->veterinarioModel->findById($id)) {
            return $this->json(['error' => 'Veterinário não encontrado'], 404);
        }
        if ($this->veterinarioModel->update($id, $data)) {
            return $this->json(['message' => 'Veterinário atualizado com sucesso']);
        }
        $this->json(['error' => 'Erro ao atualizar veterinário'], 500);
    }

    public function destroy($id)
    {
        if (!$this->veterinarioModel->findById($id)) {
            return $this->json(['error' => 'Veterinário não encontrado'], 404);
        }
        if ($this->veterinarioModel->delete($id)) {
            return $this->json(['message' => 'Veterinário excluído com sucesso']);
        }
        $this->json(['error' => 'Erro ao excluir veterinário'], 500);
    }
}
