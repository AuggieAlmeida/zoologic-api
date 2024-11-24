<?php

namespace App\Controllers;

use App\Models\Animal;

class AnimalController extends Controller
{
    private $animalModel;

    public function __construct(Animal $animalModel = null)
    {
        $this->animalModel = $animalModel ?? new Animal();
    }

    public function index()
    {
        $animais = $this->animalModel->findAll();
        $this->json($animais);
    }

    public function show($id)
    {
        $animal = $this->animalModel->findById($id);
        if (!$animal) {
            return $this->json(['error' => 'Animal não encontrado'], 404);
        }
        $this->json($animal);
    }

    public function store()
    {
        $data = $this->getRequestData();
        
        // Validação básica
        $requiredFields = ['tipo', 'especie', 'setor', 'habitat', 'idade', 'peso', 
                          'alimentacao', 'status', 'sexo'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Campo obrigatório ausente: {$field}"], 400);
            }
        }

        if ($this->animalModel->create($data)) {
            $this->json(['message' => 'Animal cadastrado com sucesso'], 201);
        } else {
            $this->json(['error' => 'Erro ao cadastrar animal'], 500);
        }
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        
        if ($this->animalModel->update($id, $data)) {
            $this->json(['message' => 'Animal atualizado com sucesso']);
        } else {
            $this->json(['error' => 'Erro ao atualizar animal'], 500);
        }
    }

    public function destroy($id)
    {
        if ($this->animalModel->delete($id)) {
            $this->json(['message' => 'Animal excluído com sucesso']);
        } else {
            $this->json(['error' => 'Erro ao excluir animal'], 500);
        }
    }
} 