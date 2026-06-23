<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\Modulo;

class EmpresaModulosSeeder extends Seeder
{
    public function run(): void
    {
        $todosLosModulos = Modulo::pluck('id');

        foreach (Empresa::all() as $empresa) {
            $empresa->modulos()->syncWithoutDetaching($todosLosModulos);
        }
    }
}
