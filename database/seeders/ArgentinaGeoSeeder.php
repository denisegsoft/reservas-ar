<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArgentinaGeoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departamentos ──────────────────────────────────────────────────────
        $this->command->info('Descargando departamentos...');
        $deptos = $this->fetchAllPages('departamentos', 'departamentos', [
            'campos' => 'id,nombre,provincia.nombre',
        ]);
        if (!$deptos) {
            $this->command->error('No se pudieron obtener los departamentos.');
            return;
        }

        // ── Localidades (paginado completo) ────────────────────────────────────
        $this->command->info('Descargando localidades (paginado)...');
        $locs = $this->fetchAllPages('localidades', 'localidades', [
            'campos' => 'id,nombre,departamento.id,departamento.nombre,provincia.nombre',
        ]);
        if ($locs === null) {
            $this->command->error('No se pudieron obtener las localidades.');
            return;
        }

        // ── Gobiernos locales de CABA (barrios / comunas) ──────────────────────
        $this->command->info('Descargando gobiernos locales de CABA...');
        $gobsLocales = $this->fetchAllPages('gobiernos-locales', 'gobiernos_locales', [
            'campos'   => 'id,nombre,departamento.id,departamento.nombre,provincia.nombre',
            'provincia'=> '02', // ID de CABA en georef
        ]);
        $gobsLocales = $gobsLocales ?? [];

        // ── Limpiar e insertar ─────────────────────────────────────────────────
        $this->command->info('Limpiando tablas...');
        DB::table('localidades')->delete();
        DB::table('partidos')->delete();

        // Mapa nombre georef → province_id en nuestra BD
        // Nombres alternativos para provincias con nombre distinto en georef vs BD
        $provinceIdMap = DB::table('provinces')->pluck('id', 'name')->toArray();
        $georefAliases = [
            'Tierra del Fuego, Antártida e Islas del Atlántico Sur' => 'Tierra del Fuego',
        ];

        $this->command->info('Insertando ' . count($deptos) . ' departamentos...');
        $now        = now();
        $partidoMap = []; // georef_id => partido_id en BD

        $deptoBatch = [];
        foreach ($deptos as $d) {
            $georefName = $d['provincia']['nombre'];
            $dbName     = $georefAliases[$georefName] ?? $georefName;
            $deptoBatch[] = [
                'province_id' => $provinceIdMap[$dbName] ?? null,
                'name'        => $d['nombre'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('partidos')->insert($deptoBatch);

        $insertedPartidos = DB::table('partidos')->orderBy('id')->get(['id', 'province_id', 'name']);
        foreach ($deptos as $i => $d) {
            $partidoMap[$d['id']] = $insertedPartidos[$i]->id;
        }

        // ── Insertar localidades ───────────────────────────────────────────────
        $allLocs = array_merge($locs, $gobsLocales);
        $this->command->info('Insertando ' . count($allLocs) . ' localidades...');

        $locBatch        = [];
        $skipped         = 0;
        $partidosConLocs = []; // partido_id => true

        foreach ($allLocs as $l) {
            $deptoId = $l['departamento']['id'] ?? null;
            if (!$deptoId || !isset($partidoMap[$deptoId])) {
                $skipped++;
                continue;
            }
            $partidoId = $partidoMap[$deptoId];
            $partidosConLocs[$partidoId] = true;

            $locBatch[] = [
                'partido_id' => $partidoId,
                'name'       => $l['nombre'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($locBatch) >= 500) {
                DB::table('localidades')->insert($locBatch);
                $locBatch = [];
            }
        }
        if ($locBatch) {
            DB::table('localidades')->insert($locBatch);
        }

        // ── Fallback: partidos sin localidades ─────────────────────────────────
        $sinLocs = $insertedPartidos->filter(function($p) use ($partidosConLocs) { return !isset($partidosConLocs[$p->id]); });
        if ($sinLocs->count()) {
            $this->command->warn("Partidos sin localidades: {$sinLocs->count()} — usando nombre del partido como fallback.");
            $fallback = [];
            foreach ($sinLocs as $p) {
                $fallback[] = [
                    'partido_id' => $p->id,
                    'name'       => $p->name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('localidades')->insert($fallback);
        }

        $total = count($allLocs) - $skipped;
        $this->command->info("Listo. Departamentos: " . count($deptos) . " | Localidades: {$total} | Omitidas: {$skipped}");
    }

    /**
     * Descarga todas las páginas de un endpoint georef y devuelve el array plano de resultados.
     */
    private function fetchAllPages(string $endpoint, string $key, array $params = []): ?array
    {
        $pageSize = 5000;
        $inicio   = 0;
        $results  = [];

        do {
            $query = http_build_query(array_merge($params, [
                'max'   => $pageSize,
                'inicio' => $inicio,
            ]));
            $url  = "https://apis.datos.gob.ar/georef/api/v2.0/{$endpoint}.json?{$query}";
            $data = $this->fetchJson($url);

            if (!$data || !isset($data[$key])) {
                return $inicio === 0 ? null : $results; // primera página fallida = error
            }

            $page    = $data[$key];
            $results = array_merge($results, $page);
            $total   = $data['total'] ?? count($results);
            $inicio += $pageSize;

            $this->command->line("  · {$endpoint}: {$inicio}/{$total}");
        } while ($inicio < $total);

        return $results;
    }

    private function fetchJson(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'timeout'         => 60,
                'follow_location' => true,
                'max_redirects'   => 5,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) return null;
        return json_decode($raw, true);
    }
}
