<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'quinta',       'name' => 'Quinta'],
            ['slug' => 'salon',        'name' => 'Salón de Eventos'],
            ['slug' => 'casa',         'name' => 'Casa'],
            ['slug' => 'departamento', 'name' => 'Departamento'],
            ['slug' => 'terreno',      'name' => 'Terreno'],
            ['slug' => 'cancha',       'name' => 'Cancha Deportiva'],
            ['slug' => 'coworking',    'name' => 'Espacio Coworking'],
            ['slug' => 'campo',        'name' => 'Campo / Estancia'],
            ['slug' => 'otro',         'name' => 'Otro'],
        ];

        foreach ($types as $index => $type) {
            PropertyType::create(array_merge($type, ['order' => $index + 1]));
        }

        $this->command->info('Tipos de propiedad cargados: ' . count($types));
    }
}
