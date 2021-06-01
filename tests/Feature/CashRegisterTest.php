<?php

namespace Tests\Feature;

use App\Models\Cash;
use App\Models\Log;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CashRegisterTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function consultarEstadoTest()
    {
        $this->seed();

        $response = $this->getJson('/api/consultarEstado');
        $response->assertStatus(200)->assertJson(["totalDinero" => 0]);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "20000",
                    "cantidad" => 5
                ],
                [
                    "valor" => "10000",
                    "cantidad" => 10
                ],
                [
                    "valor" => "500",
                    "cantidad" => 15
                ],
                [
                    "valor" => "200",
                    "cantidad" => 20
                ]
            ]
        ]);
        $response->assertStatus(200);
        $cash = Cash::find(1);
        $this->assertEquals($cash->total_money, 211500);

        $response = $this->getJson('/api/consultarEstado');
        $response->assertStatus(200)->assertJson(["totalDinero" => 211500]);
    }

    /** @test */
    public function realizarPagoTest()
    {
        $this->seed();

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "20000",
                    "cantidad" => 5
                ],
                [
                    "valor" => "10000",
                    "cantidad" => 10
                ],
                [
                    "valor" => "500",
                    "cantidad" => 15
                ],
                [
                    "valor" => "200",
                    "cantidad" => 20
                ]
            ]
        ]);
        $response->assertStatus(200);

        $cash = Cash::find(1);
        $this->assertEquals($cash->total_money, 211500);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120000",
            "dinero" => [
                [
                    "valor" => "50000",
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ]
            ]
        ]);
        $response->assertStatus(200)->assertJson([
            [
                "valor" => "20000",
                "cantidad" => 1
            ],
            [
                "valor" => "10000",
                "cantidad" => 1
            ]
        ]);

        $cash = Cash::find(1);
        $this->assertEquals($cash->total_money, 331500);
    }

    /** @test */
    public function realizarPagoErrorTest()
    {
        $this->seed();

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120050",
            "dinero" => [
                [
                    "valor" => "50000",
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ]
            ]
        ]);
        $response->assertStatus(202);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120050",
            "dinero" => [
                [
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120050",
            "dinero" => [
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                ]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120000",
            "dinero" => [
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120000",
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "",
            "dinero" => [
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                ]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
            "dinero" => [
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                ]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/realizarPago', [
            "dinero" => [
                [
                    "valor" => "2500",
                    "cantidad" => 1
                ],
            ]
        ]);
        $response->assertStatus(400);
    }

    /** @test */
    public function cargarBaseCajaErrorTest() {
        $this->seed();
        $response = $this->postJson('/api/cargarBaseCaja', [
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "cantidad" => 1
                ],
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "2500"
                ],
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "20000",
                    "cantidad" => "asd"
                ],
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "asd",
                    "cantidad" => 5
                ],
            ]
        ]);
        $response->assertStatus(400);
    }
    
    /** @test */
    public function retirarTodoTest() {
        $this->seed();
        $response = $this->getJson('/api/retirarTodo');
        $response->assertStatus(200)->assertJson(["totalRetiro" => 0]);

        $cash = Cash::find(1);
        $this->assertEquals($cash->total_money, 0);
        $this->assertEquals(count($cash->cashMovements), 1);
        $this->assertEquals(count($cash->logs), 1);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "20000",
                    "cantidad" => 5
                ],
                [
                    "valor" => "10000",
                    "cantidad" => 10
                ],
                [
                    "valor" => "500",
                    "cantidad" => 15
                ],
                [
                    "valor" => "200",
                    "cantidad" => 20
                ]
            ]
        ]);
        $response->assertStatus(200);

        $cash = Cash::find(1);
        $this->assertEquals($cash->total_money, 211500);
        $this->assertEquals(count($cash->cashMovements), 2);
        $this->assertEquals(count($cash->logs), 2);

        $response = $this->getJson('/api/retirarTodo');
        $response->assertStatus(200)->assertJson(["totalRetiro" => 211500]);

        $cash = Cash::find(1);
        $this->assertEquals($cash->total_money, 0);
        $this->assertEquals(count($cash->cashMovements), 3);
        $this->assertEquals(count($cash->logs), 3);
    }

    
    /** @test */
    public function verLogMovimientosTest() {
        $this->seed();

        $response = $this->getJson('/api/verLogMovimientos');
        $response->assertStatus(200)->assertJsonCount(0);

        $response = $this->postJson('/api/cargarBaseCaja', [
            "dinero" => [
                [
                    "valor" => "20000",
                    "cantidad" => 5
                ],
                [
                    "valor" => "10000",
                    "cantidad" => 10
                ],
                [
                    "valor" => "500",
                    "cantidad" => 15
                ],
                [
                    "valor" => "200",
                    "cantidad" => 20
                ]
            ]
        ]);
        $response->assertStatus(200);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "120000",
            "dinero" => [
                [
                    "valor" => "50000",
                    "cantidad" => 1
                ],
                [
                    "valor" => "100000",
                    "cantidad" => 1
                ]
            ]
        ]);
        $response->assertStatus(200);

        $response = $this->postJson('/api/realizarPago', [
            "totalPagar" => "10000",
            "dinero" => [
                [
                    "valor" => "50000",
                    "cantidad" => 1
                ]
            ]
        ]);
        $response->assertStatus(200);
        
        $response = $this->getJson('/api/verLogMovimientos');
        $response->assertStatus(200)->assertJsonCount(3);
    }
}
