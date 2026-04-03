<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('id')->constrained('provinces');
        });

        // Migrar datos existentes: province name → province_id
        $provinces = DB::table('provinces')->pluck('id', 'name');
        DB::table('partidos')->orderBy('id')->chunk(500, function ($rows) use ($provinces) {
            foreach ($rows as $row) {
                $id = $provinces[$row->province] ?? null;
                if ($id) {
                    DB::table('partidos')->where('id', $row->id)->update(['province_id' => $id]);
                }
            }
        });

        Schema::table('partidos', function (Blueprint $table) {
            $table->dropIndex('partidos_province_index');
            $table->dropColumn('province');
            $table->index('province_id');
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->string('province')->after('id')->default('');
            $table->index('province');
        });

        DB::table('partidos')
            ->join('provinces', 'partidos.province_id', '=', 'provinces.id')
            ->update(['partidos.province' => DB::raw('provinces.name')]);

        Schema::table('partidos', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropIndex('partidos_province_id_index');
            $table->dropColumn('province_id');
        });
    }
};
