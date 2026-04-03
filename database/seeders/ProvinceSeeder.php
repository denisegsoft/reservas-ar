<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'Buenos Aires',
            'CABA',
            'Catamarca',
            'Chaco',
            'Chubut',
            'Córdoba',
            'Corrientes',
            'Entre Ríos',
            'Formosa',
            'Jujuy',
            'La Pampa',
            'La Rioja',
            'Mendoza',
            'Misiones',
            'Neuquén',
            'Río Negro',
            'Salta',
            'San Juan',
            'San Luis',
            'Santa Cruz',
            'Santa Fe',
            'Santiago del Estero',
            'Tierra del Fuego',
            'Tucumán',
        ];

        foreach ($provinces as $index => $name) {
            Province::create([
                'name'   => $name,
                'slug'   => Str::slug($name),
                'active' => true,
                'order'  => $index + 1,
            ]);
        }

        $this->command->info('Provincias cargadas: ' . count($provinces));
    }
}
