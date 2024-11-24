<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Colaborador;
use App\Libraries\Database;

class ColaboradorTest extends TestCase
{
    private $colaborador;
    private $dbMock;

    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(Database::class);
        $this->colaborador = new Colaborador($this->dbMock);
    }

    public function testFindAllReturnsArrayOfColaboradores()
    {
        $expectedData = [
            [
                'id' => 1,
                'nome' => 'João Silva',
                'email' => 'joao@example.com',
                'funcao' => 'Veterinário',
                'salario' => 5000.00
            ]
        ];

        $this->dbMock->expects($this->once())
            ->method('query')
            ->willReturn($expectedData);

        $result = $this->colaborador->findAll();
        
        $this->assertEquals($expectedData, $result);
    }

    public function testFindByIdReturnsColaborador()
    {
        $expectedData = [
            'id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'funcao' => 'Veterinário',
            'salario' => 5000.00
        ];

        $this->dbMock->expects($this->once())
            ->method('queryOne')
            ->with(
                $this->anything(),  // Expect SQL string
                ['id' => 1]
            )
            ->willReturn($expectedData);

        $result = $this->colaborador->findById(1);
        
        $this->assertEquals($expectedData, $result);
    }

    public function testCreateColaborador()
    {
        $data = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'senha' => '123456',
            'funcao' => 'Veterinário',
            'salario' => 5000.00
        ];

        $this->dbMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->colaborador->create($data);
        
        $this->assertTrue($result);
    }
} 