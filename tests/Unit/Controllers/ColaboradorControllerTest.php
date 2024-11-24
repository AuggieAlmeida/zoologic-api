<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use App\Controllers\ColaboradorController;
use App\Models\Colaborador;

class ColaboradorControllerTest extends TestCase
{
    private $controller;
    private $modelMock;

    protected function setUp(): void
    {
        $this->modelMock = $this->createMock(Colaborador::class);
        $this->controller = new ColaboradorController($this->modelMock);
    }

    public function testIndexReturnsAllColaboradores()
    {
        $expectedData = [
            [
                'id' => 1,
                'nome' => 'João Silva',
                'email' => 'joao@example.com'
            ]
        ];

        $this->modelMock->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedData);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertEquals(json_encode($expectedData), $output);
    }

    public function testShowReturnsColaborador()
    {
        $expectedData = [
            'id' => 1,
            'nome' => 'João Silva',
            'email' => 'joao@example.com'
        ];

        $this->modelMock->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($expectedData);

        ob_start();
        $this->controller->show(1);
        $output = ob_get_clean();

        $this->assertEquals(json_encode($expectedData), $output);
    }
} 