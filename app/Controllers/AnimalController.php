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

        $validation = $this->validateAnimalData($data);
        if ($validation) {
            return $this->json($validation['body'], $validation['status']);
        }

        if ($this->animalModel->create($data)) {
            $this->json(['message' => 'Animal cadastrado com sucesso'], 201);
        } else {
            $this->json(['error' => 'Erro ao cadastrar animal'], 500);
        }
    }

    public function update($id)
    {
        $existing = $this->animalModel->findById($id);
        if (!$existing) {
            return $this->json(['error' => 'Animal não encontrado'], 404);
        }

        $data = array_merge($existing, $this->getRequestData());
        unset($data['habitat']);

        $validation = $this->validateAnimalData($data, $id);
        if ($validation) {
            return $this->json($validation['body'], $validation['status']);
        }

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

    private function validateAnimalData($data, $animalId = null)
    {
        $requiredFields = ['nome', 'tipo', 'especie', 'setor', 'habitat_id', 'idade', 'peso',
                          'alimentacao', 'status', 'sexo'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return [
                    'status' => 400,
                    'body' => ['error' => "Campo obrigatório ausente: {$field}"]
                ];
            }
        }

        $habitatId = filter_var($data['habitat_id'], FILTER_VALIDATE_INT);
        if ($habitatId === false || $habitatId < 1) {
            return [
                'status' => 400,
                'body' => ['error' => 'habitat_id deve ser um identificador válido.']
            ];
        }

        $habitat = $this->animalModel->findHabitat($habitatId);
        if (!$habitat) {
            return [
                'status' => 400,
                'body' => ['error' => 'Habitat não encontrado.']
            ];
        }

        $occupied = $this->animalModel->countByHabitat($habitatId, $animalId);
        if ($occupied >= (int) $habitat['capacidade']) {
            return [
                'status' => 409,
                'body' => ['error' => 'A capacidade deste habitat já foi atingida.']
            ];
        }

        return null;
    }
}
