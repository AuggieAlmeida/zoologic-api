<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\AnimalController;
use App\Models\Animal;

class AnimalControllerTest extends TestCase
{
    private $controller;
    private $modelMock;

    protected function setUp(): void
    {
        $this->modelMock = $this->createMock(Animal::class);
        
        $this->controller = new class($this->modelMock) extends AnimalController {
            private $requestData;
            
            public function setRequestData($data) {
                $this->requestData = $data;
            }
            
            protected function getRequestData() {
                return $this->requestData;
            }
        };
    }

    public function testIndexReturnsAllAnimais()
    {
        $expectedData = [
            [
                'id_animal' => 1,
                'nome' => 'Leão',
                'especie' => 'Panthera leo'
            ]
        ];

        $this->modelMock->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedData);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(json_encode($expectedData), $output);
    }

    public function testShowReturnsAnimal()
    {
        $expectedData = [
            'id_animal' => 1,
            'nome' => 'Leão',
            'especie' => 'Panthera leo'
        ];

        $this->modelMock->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(1))
            ->willReturn($expectedData);

        ob_start();
        $this->controller->show(1);
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(json_encode($expectedData), $output);
    }

    public function testShowReturnsNotFound()
    {
        $this->modelMock->expects($this->once())
            ->method('findById')
            ->with($this->equalTo(999))
            ->willReturn(null);

        ob_start();
        $this->controller->show(999);
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Animal não encontrado']), 
            $output
        );
    }

    public function testStoreAnimalSuccess()
    {
        $requestData = [
            'nome' => 'Simba',
            'tipo' => 'Mamífero',
            'especie' => 'Panthera leo',
            'setor' => 'Felinos',
            'habitat_id' => 1,
            'idade' => 5,
            'peso' => 180.5,
            'alimentacao' => 'Carnívoro',
            'status' => 'Saudável',
            'sexo' => 'M'
        ];

        $this->controller->setRequestData($requestData);

        // Storing validates the habitat and its capacity before inserting.
        $this->modelMock->method('findHabitat')->willReturn(['id' => 1, 'capacidade' => 10]);
        $this->modelMock->method('countByHabitat')->willReturn(0);

        $this->modelMock->expects($this->once())
            ->method('create')
            ->willReturn(true);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Animal cadastrado com sucesso']),
            $output
        );
    }

    public function testStoreAnimalMissingField()
    {
        $requestData = [
            'tipo' => 'Mamífero',
            // outros campos
        ];

        $this->controller->setRequestData($requestData);

        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Campo obrigatório ausente: nome']),
            $output
        );
    }
} 