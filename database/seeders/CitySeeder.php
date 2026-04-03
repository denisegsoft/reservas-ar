<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Buenos Aires' => [
                'La Plata', 'Mar del Plata', 'Quilmes', 'Lanús', 'Lomas de Zamora',
                'Almirante Brown', 'Merlo', 'Moreno', 'Tigre', 'Pilar', 'Luján',
                'San Isidro', 'Vicente López', 'Tres de Febrero', 'Morón',
                'San Martín', 'Hurlingham', 'Ituzaingó', 'Ezeiza', 'Cañuelas',
                'Escobar', 'Zárate', 'Campana', 'San Nicolás', 'Bahía Blanca',
                'Tandil', 'Azul', 'Olavarría', 'Necochea', 'Junín',
                'Pergamino', 'San Pedro', 'Bragado', 'Chivilcoy', 'Lobos',
                'General Rodríguez', 'Marcos Paz', 'Chascomús', 'Dolores',
                'Coronel Suárez', 'Pehuajó', 'Trenque Lauquen', 'Bolívar',
            ],
            'CABA' => [
                'Palermo', 'Belgrano', 'Recoleta', 'San Telmo', 'La Boca',
                'Flores', 'Caballito', 'Villa del Parque', 'Núñez', 'Colegiales',
                'Almagro', 'Boedo', 'Mataderos', 'Devoto', 'Villa Urquiza',
                'Saavedra', 'Balvanera', 'Monserrat', 'Puerto Madero', 'Villa Crespo',
            ],
            'Córdoba' => [
                'Córdoba', 'Villa María', 'Río Cuarto', 'San Francisco',
                'Villa Carlos Paz', 'Alta Gracia', 'Bell Ville', 'Cosquín',
                'La Falda', 'Jesús María', 'Río Tercero', 'Marcos Juárez',
                'Cruz del Eje', 'Dean Funes', 'Villa Dolores', 'Laboulaye',
                'Unquillo', 'Mendiolaza', 'La Calera', 'Arroyito',
            ],
            'Santa Fe' => [
                'Santa Fe', 'Rosario', 'Rafaela', 'Venado Tuerto', 'Reconquista',
                'Santo Tomé', 'Esperanza', 'Cañada de Gómez', 'Casilda',
                'Villa Constitución', 'San Lorenzo', 'Capitán Bermúdez',
                'Granadero Baigorria', 'Funes', 'Roldán', 'Pérez',
            ],
            'Mendoza' => [
                'Mendoza', 'San Rafael', 'Godoy Cruz', 'Maipú', 'Luján de Cuyo',
                'Las Heras', 'Guaymallén', 'Rivadavia', 'Junín', 'General Alvear',
                'Malargüe', 'La Paz', 'Santa Rosa', 'Tupungato', 'Tunuyán',
            ],
            'Tucumán' => [
                'San Miguel de Tucumán', 'Yerba Buena', 'Tafí Viejo', 'Banda del Río Salí',
                'Concepción', 'Monteros', 'Aguilares', 'Alderetes', 'Lules',
                'Famailla', 'La Cocha', 'Bella Vista',
            ],
            'Salta' => [
                'Salta', 'San Ramón de la Nueva Orán', 'Tartagal', 'Metán',
                'Rosario de la Frontera', 'Cafayate', 'Embarcación', 'Güemes',
                'General Güemes', 'Cerrillos', 'La Caldera',
            ],
            'Jujuy' => [
                'San Salvador de Jujuy', 'Palpalá', 'San Pedro de Jujuy',
                'Libertador General San Martín', 'Tilcara', 'Humahuaca',
                'Perico', 'El Carmen', 'La Quiaca',
            ],
            'Entre Ríos' => [
                'Paraná', 'Concordia', 'Gualeguaychú', 'Concepción del Uruguay',
                'Colón', 'Gualeguay', 'Victoria', 'Diamante', 'Villaguay',
                'Federación', 'La Paz', 'Chajarí',
            ],
            'Corrientes' => [
                'Corrientes', 'Goya', 'Posadas', 'Curuzú Cuatiá', 'Mercedes',
                'Esquina', 'Monte Caseros', 'Santo Tomé', 'Paso de los Libres',
                'Bella Vista', 'Saladas',
            ],
            'Misiones' => [
                'Posadas', 'Oberá', 'Eldorado', 'Puerto Iguazú', 'Apóstoles',
                'Leandro N. Alem', 'Jardín América', 'San Vicente', 'Montecarlo',
                'Aristóbulo del Valle',
            ],
            'Chaco' => [
                'Resistencia', 'Presidencia Roque Sáenz Peña', 'Barranqueras',
                'Villa Ángela', 'Fontana', 'Charata', 'Quitilipi', 'Las Breñas',
                'General José de San Martín', 'Juan José Castelli',
            ],
            'Formosa' => [
                'Formosa', 'Clorinda', 'Pirané', 'Ingeniero Juárez', 'El Colorado',
                'General Mosconi',
            ],
            'Santiago del Estero' => [
                'Santiago del Estero', 'La Banda', 'Termas de Río Hondo',
                'Añatuya', 'Frías', 'Loreto', 'Beltrán', 'Fernández',
            ],
            'Catamarca' => [
                'San Fernando del Valle de Catamarca', 'Santa María', 'Andalgalá',
                'Belén', 'Tinogasta', 'Recreo', 'Chumbicha',
            ],
            'La Rioja' => [
                'La Rioja', 'Chilecito', 'Aimogasta', 'Chamical', 'Chepes',
                'Villa Unión', 'Vinchina',
            ],
            'San Juan' => [
                'San Juan', 'Rawson', 'Rivadavia', 'Chimbas', 'Santa Lucía',
                'Pocito', 'Caucete', 'Albardón', '9 de Julio', 'Sarmiento',
            ],
            'San Luis' => [
                'San Luis', 'Villa Mercedes', 'Merlo', 'Justo Daract',
                'Quines', 'Concarán', 'Tilisarao', 'La Toma',
            ],
            'Neuquén' => [
                'Neuquén', 'Cipolletti', 'Plottier', 'Cutral Có', 'Plaza Huincul',
                'Zapala', 'San Martín de los Andes', 'Villa La Angostura',
                'Junín de los Andes', 'Centenario',
            ],
            'Río Negro' => [
                'Viedma', 'General Roca', 'Cipolletti', 'Bariloche', 'Allen',
                'Cinco Saltos', 'Catriel', 'Lago Puelo', 'El Bolsón',
                'Villa Regina', 'Roca',
            ],
            'Chubut' => [
                'Rawson', 'Trelew', 'Comodoro Rivadavia', 'Puerto Madryn',
                'Esquel', 'Gaiman', 'Dolavon', 'Rada Tilly', 'Sarmiento',
            ],
            'Santa Cruz' => [
                'Río Gallegos', 'Caleta Olivia', 'El Calafate', 'Puerto Deseado',
                'Pico Truncado', 'Las Heras', 'El Chaltén', 'Puerto San Julián',
            ],
            'Tierra del Fuego' => [
                'Ushuaia', 'Río Grande', 'Tolhuin',
            ],
            'La Pampa' => [
                'Santa Rosa', 'General Pico', 'Toay', 'Realicó', 'General Acha',
                'Eduardo Castex', 'Victorica', 'Guatraché',
            ],
        ];

        $total = 0;
        foreach ($data as $provinceName => $cities) {
            $province = Province::where('name', $provinceName)->first();
            if (!$province) continue;

            foreach ($cities as $order => $cityName) {
                City::create([
                    'province_id' => $province->id,
                    'name'        => $cityName,
                    'active'      => true,
                    'order'       => $order + 1,
                ]);
                $total++;
            }
        }

        $this->command->info("Ciudades cargadas: {$total}");
    }
}
