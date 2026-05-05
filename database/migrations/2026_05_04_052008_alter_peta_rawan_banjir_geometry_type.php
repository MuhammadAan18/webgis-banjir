<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah tipe geometry dari POLYGON menjadi GEOMETRY untuk support MultiPolygon
        DB::statement('ALTER TABLE peta_rawan_banjir DROP CONSTRAINT IF EXISTS enforce_geom_type');
        DB::statement('ALTER TABLE peta_rawan_banjir ALTER COLUMN geom TYPE geometry USING geom::geometry');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE peta_rawan_banjir ALTER COLUMN geom TYPE geometry(Polygon, 4326) USING geom::geometry(Polygon, 4326)');
    }
};
