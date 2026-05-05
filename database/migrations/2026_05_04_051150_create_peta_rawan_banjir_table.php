<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('peta_rawan_banjir', function (Blueprint $table) {
            $table->id();
            $table->string('fid')->nullable();
            $table->string('rawan_banj')->nullable();
            $table->decimal('panjang', 18, 2)->nullable();
            $table->timestamps();
        });

        // Tambah geometry column menggunakan raw SQL
        DB::statement('ALTER TABLE peta_rawan_banjir ADD COLUMN geom GEOMETRY(POLYGON, 4326)');
        DB::statement('CREATE INDEX idx_peta_rawan_banjir_geom ON peta_rawan_banjir USING GIST(geom)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_peta_rawan_banjir_geom');
        Schema::dropIfExists('peta_rawan_banjir');
    }
};
