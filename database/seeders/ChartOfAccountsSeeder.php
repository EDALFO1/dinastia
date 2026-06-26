<?php

namespace Database\Seeders;

use App\Domains\Accounting\Models\ChartOfAccounts;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = Empresa::all();

        foreach ($empresas as $empresa) {
            $this->seedPucForEmpresa($empresa);
        }
    }

    private function seedPucForEmpresa(Empresa $empresa): void
    {
        $puc = [
            // ACTIVO
            [
                'codigo' => '10',
                'nombre' => 'Disponibilidades',
                'tipo_cuenta' => 'activo',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '1001',
                        'nombre' => 'Caja',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '100101',
                                'nombre' => 'Caja General',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                    [
                        'codigo' => '1005',
                        'nombre' => 'Bancos',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '100501',
                                'nombre' => 'Banco Occidente',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                            [
                                'codigo' => '100502',
                                'nombre' => 'Banco Bogotá',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                ]
            ],
            [
                'codigo' => '11',
                'nombre' => 'Inversiones',
                'tipo_cuenta' => 'activo',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],
            [
                'codigo' => '12',
                'nombre' => 'Deudores',
                'tipo_cuenta' => 'activo',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '1205',
                        'nombre' => 'Clientes',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '120501',
                                'nombre' => 'Clientes Nacionales',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                ]
            ],
            [
                'codigo' => '13',
                'nombre' => 'Inventarios',
                'tipo_cuenta' => 'activo',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '1301',
                        'nombre' => 'Mercancías No Fabricadas',
                        'nivel' => 2,
                        'permit_movimiento' => true,
                    ],
                ]
            ],
            [
                'codigo' => '15',
                'nombre' => 'Propiedad, Planta y Equipo',
                'tipo_cuenta' => 'activo',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],
            [
                'codigo' => '17',
                'nombre' => 'Activos Diferidos',
                'tipo_cuenta' => 'activo',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],

            // PASIVO
            [
                'codigo' => '20',
                'nombre' => 'Obligaciones Financieras',
                'tipo_cuenta' => 'pasivo',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],
            [
                'codigo' => '21',
                'nombre' => 'Cuentas por Pagar',
                'tipo_cuenta' => 'pasivo',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '2105',
                        'nombre' => 'Proveedores',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '210501',
                                'nombre' => 'Proveedores Nacionales',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                ]
            ],
            [
                'codigo' => '23',
                'nombre' => 'Cuentas por Pagar Socios',
                'tipo_cuenta' => 'pasivo',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],
            [
                'codigo' => '24',
                'nombre' => 'Impuestos por Pagar',
                'tipo_cuenta' => 'pasivo',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '2401',
                        'nombre' => 'IVA por Pagar',
                        'nivel' => 2,
                        'permit_movimiento' => true,
                    ],
                    [
                        'codigo' => '2408',
                        'nombre' => 'Impuesto de Renta por Pagar',
                        'nivel' => 2,
                        'permit_movimiento' => true,
                    ],
                ]
            ],
            [
                'codigo' => '26',
                'nombre' => 'Pasivos Laborales',
                'tipo_cuenta' => 'pasivo',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],

            // PATRIMONIO
            [
                'codigo' => '31',
                'nombre' => 'Capital',
                'tipo_cuenta' => 'patrimonio',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '3101',
                        'nombre' => 'Capital Pagado',
                        'nivel' => 2,
                        'permit_movimiento' => true,
                    ],
                ]
            ],
            [
                'codigo' => '32',
                'nombre' => 'Ganancias o Pérdidas del Ejercicio',
                'tipo_cuenta' => 'patrimonio',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],
            [
                'codigo' => '33',
                'nombre' => 'Ganancias o Pérdidas de Ejercicios Anteriores',
                'tipo_cuenta' => 'patrimonio',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],

            // INGRESOS
            [
                'codigo' => '41',
                'nombre' => 'Ingresos Operacionales',
                'tipo_cuenta' => 'ingresos',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '4101',
                        'nombre' => 'Venta de Productos',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '410101',
                                'nombre' => 'Ventas Nacionales',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                    [
                        'codigo' => '4105',
                        'nombre' => 'Prestación de Servicios',
                        'nivel' => 2,
                        'permit_movimiento' => true,
                    ],
                ]
            ],
            [
                'codigo' => '42',
                'nombre' => 'Ingresos No Operacionales',
                'tipo_cuenta' => 'ingresos',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],

            // GASTOS
            [
                'codigo' => '51',
                'nombre' => 'Gastos Operacionales',
                'tipo_cuenta' => 'gastos',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '5101',
                        'nombre' => 'Gastos de Personal',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '510101',
                                'nombre' => 'Salarios y Jornales',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                            [
                                'codigo' => '510105',
                                'nombre' => 'Aportes a la Seguridad Social',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                    [
                        'codigo' => '5105',
                        'nombre' => 'Gastos Generales',
                        'nivel' => 2,
                        'permit_movimiento' => false,
                        'children' => [
                            [
                                'codigo' => '510501',
                                'nombre' => 'Servicios',
                                'nivel' => 3,
                                'permit_movimiento' => true,
                            ],
                        ],
                    ],
                ]
            ],
            [
                'codigo' => '52',
                'nombre' => 'Gastos No Operacionales',
                'tipo_cuenta' => 'gastos',
                'nivel' => 1,
                'permit_movimiento' => false,
            ],

            // COSTO DE VENTA
            [
                'codigo' => '61',
                'nombre' => 'Costo de Venta',
                'tipo_cuenta' => 'costo',
                'nivel' => 1,
                'permit_movimiento' => false,
                'children' => [
                    [
                        'codigo' => '6101',
                        'nombre' => 'Compra de Mercancías',
                        'nivel' => 2,
                        'permit_movimiento' => true,
                    ],
                ]
            ],
        ];

        foreach ($puc as $cuenta) {
            $this->createAccount($empresa->id, null, $cuenta);
        }
    }

    private function createAccount(int $empresaId, ?int $parentId, array $data): void
    {
        $children = $data['children'] ?? [];
        unset($data['children']);

        $cuenta = ChartOfAccounts::create(array_merge($data, [
            'empresa_id' => $empresaId,
            'parent_id' => $parentId,
        ]));

        foreach ($children as $child) {
            $this->createAccount($empresaId, $cuenta->id, $child);
        }
    }
}
