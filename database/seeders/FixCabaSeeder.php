<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reemplaza los partidos de CABA (Comunas 1-15 cargadas por ArgentinaGeoSeeder)
 * con los 48 barrios oficiales de la Ciudad Autónoma de Buenos Aires.
 * Cada barrio es a la vez partido y localidad (uso común en apps de alquiler).
 *
 * Ejecutar: php artisan db:seed --class=FixCabaSeeder
 */
class FixCabaSeeder extends Seeder
{
    /** Los 48 barrios oficiales de CABA */
    private array $barrios = [
        'Agronomía', 'Almagro', 'Balvanera', 'Barracas', 'Belgrano',
        'Boedo', 'Caballito', 'Chacarita', 'Coghlan', 'Colegiales',
        'Constitución', 'Flores', 'Floresta', 'La Boca', 'La Paternal',
        'Liniers', 'Mataderos', 'Monte Castro', 'Montserrat', 'Nueva Pompeya',
        'Núñez', 'Palermo', 'Parque Avellaneda', 'Parque Chacabuco',
        'Parque Chas', 'Parque Patricios', 'Puerto Madero', 'Recoleta',
        'Retiro', 'Saavedra', 'San Cristóbal', 'San Nicolás', 'San Telmo',
        'Versalles', 'Villa Crespo', 'Villa del Parque', 'Villa Devoto',
        'Villa General Mitre', 'Villa Lugano', 'Villa Luro', 'Villa Ortúzar',
        'Villa Pueyrredón', 'Villa Real', 'Villa Riachuelo', 'Villa Santa Rita',
        'Villa Soldati', 'Villa Urquiza', 'Villa del Parque',
    ];

    public function run(): void
    {
        $cabaId = DB::table('provinces')->where('name', 'CABA')->value('id');

        if (!$cabaId) {
            $this->command->error('No se encontró la provincia CABA en la tabla provinces.');
            return;
        }

        // 1. Eliminar localidades de los partidos actuales de CABA
        $oldPartidoIds = DB::table('partidos')
            ->where('province_id', $cabaId)
            ->pluck('id');

        DB::table('localidades')->whereIn('partido_id', $oldPartidoIds)->delete();

        // 2. Eliminar los partidos actuales (Comunas 1-15)
        DB::table('partidos')->where('province_id', $cabaId)->delete();

        $this->command->info("Eliminados {$oldPartidoIds->count()} partidos (Comunas) de CABA.");

        // 3. Insertar los 48 barrios como partidos únicos
        $barrios = array_values(array_unique($this->barrios));
        sort($barrios);

        $now          = now();
        $partidoBatch = [];
        foreach ($barrios as $barrio) {
            $partidoBatch[] = [
                'province_id' => $cabaId,
                'name'        => $barrio,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('partidos')->insert($partidoBatch);

        // 4. Recuperar los IDs recién insertados y crear la localidad con el mismo nombre
        $insertedPartidos = DB::table('partidos')
            ->where('province_id', $cabaId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $locBatch = [];
        foreach ($insertedPartidos as $p) {
            $locBatch[] = [
                'partido_id' => $p->id,
                'name'       => $p->name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('localidades')->insert($locBatch);

        $this->command->info("Insertados {$insertedPartidos->count()} barrios de CABA como partidos y localidades.");
    }
}
