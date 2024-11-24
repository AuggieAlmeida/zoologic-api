<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Animal;
use App\Libraries\Database;

class AnimalTest extends TestCase
{
    private $animal;
    private $dbMock;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(Database::class);
        $this->animal = new Animal($this->dbMock);
    }

    public function testFindAllReturnsArrayOfAnimals()
    {
        $expectedData = [
            [
                'id_animal' => 1,
                'nome' => 'Simba',
                'especie' => 'Panthera leo',
                'tipo' => 'Mamífero'
            ]
        ];

        $this->dbMock->expects($this->once())
            ->method('query')
            ->willReturn($expectedData);

        $result = $this->animal->findAll();
        $this->assertEquals($expectedData, $result);
    }

    public function testFindByIdReturnsAnimal()
    {
        $expectedData = [
            'id_animal' => 1,
            'nome' => 'Simba',
            'especie' => 'Panthera leo',
            'tipo' => 'Mamífero'
        ];

        $this->dbMock->expects($this->once())
            ->method('queryOne')
            ->with(
                $this->stringContains('SELECT'),
                ['id' => 1]
            )
            ->willReturn($expectedData);

        $result = $this->animal->findById(1);
        $this->assertEquals($expectedData, $result);
    }

    public function testCreateAnimal()
    {
        $data = [
            'nome' => 'Simba',
            'especie' => 'Panthera leo',
            'setor' => 'Felinos',
            'tipo' => 'Mamífero',
            'habitat' => 'Savana',
            'idade' => 5,
            'peso' => 180.5,
            'alimentacao' => 'Carnívoro',
            'status' => 'Saudável',
            'sexo' => 'M',
            'observacoes' => 'Nenhuma',
            'foto' => 'simba.jpg'
        ];

        $this->dbMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->animal->create($data);
        $this->assertTrue($result);
    }

    public function testUpdateAnimal()
    {
        $id = 1;
        $data = [
            'especie' => 'Panthera leo',
            'setor' => 'Felinos',
            'tipo' => 'Mamífero',
            'habitat' => 'Savana',
            'idade' => 6,
            'peso' => 190.5,
            'alimentacao' => 'Carnívoro',
            'status' => 'Saudável',
            'sexo' => 'M',
            'observacoes' => 'Atualizado',
            'foto' => 'simba_new.jpg'
        ];

        $this->dbMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->animal->update($id, $data);
        $this->assertTrue($result);
    }

    public function testDeleteAnimal()
    {
        $id = 1;

        $this->dbMock->expects($this->once())
            ->method('execute')
            ->with(
                $this->stringContains('DELETE'),
                ['id' => $id]
            )
            ->willReturn(true);

        $result = $this->animal->delete($id);
        $this->assertTrue($result);
    }
} 