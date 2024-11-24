<?php

namespace App\Controllers;

use App\Models\Colaborador;

class ColaboradorController extends Controller
{
    private $colaboradorModel;

    public function __construct(Colaborador $colaboradorModel = null)
    {
        $this->colaboradorModel = $colaboradorModel ?? new Colaborador();
    }

    public function index()
    {
        $colaboradores = $this->colaboradorModel->findAll();
        $this->json($colaboradores);
    }

    public function show($id)
    {
        $colaborador = $this->colaboradorModel->findById($id);
        if (!$colaborador) {
            return $this->json(['error' => 'Colaborador não encontrado'], 404);
        }
        $this->json($colaborador);
    }

    public function store()
    {
        $data = $this->getRequestData();
        
        // Validação básica
        if (!isset($data['nome']) || !isset($data['email']) || !isset($data['senha']) || 
            !isset($data['funcao']) || !isset($data['salario'])) {
            return $this->json(['error' => 'Dados incompletos'], 400);
        }

        if ($this->colaboradorModel->create($data)) {
            $this->json(['message' => 'Colaborador criado com sucesso'], 201);
        } else {
            $this->json(['error' => 'Erro ao criar colaborador'], 500);
        }
    }

    public function update($id)
    {
        $data = $this->getRequestData();
        
        // Validação básica
        if (!isset($data['nome']) || !isset($data['email']) || 
            !isset($data['funcao']) || !isset($data['salario'])) {
            return $this->json(['error' => 'Dados incompletos'], 400);
        }

        if ($this->colaboradorModel->update($id, $data)) {
            $this->json(['message' => 'Colaborador atualizado com sucesso'], 200);
        } else {
            $this->json(['error' => 'Erro ao atualizar colaborador'], 500);
        }
    }

    public function destroy($id)
    {
        if ($this->colaboradorModel->delete($id)) {
            $this->json(['message' => 'Colaborador excluído com sucesso'], 200);
        } else {
            $this->json(['error' => 'Erro ao excluir colaborador'], 500);
        }
    }
}
