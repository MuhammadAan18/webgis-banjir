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
        // Peta Curah Hujan
        Schema::create('peta_curah_hujan', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('skor')->nullable()->comment('Bobot/skor: 1-5');
            $table->timestamps();
        });
        DB::statement('SELECT AddGeometryColumn(\'peta_curah_hujan\', \'geom\', 4326, \'MULTIPOLYGON\', 2)');
        DB::statement('CREATE INDEX idx_peta_curah_hujan_geom ON peta_curah_hujan USING GIST(geom)');

        // Peta Kemiringan Lereng
        Schema::create('peta_kemiringan_lereng', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('skor')->nullable()->comment('Bobot/skor: 1-5');
            $table->timestamps();
        });
        DB::statement('SELECT AddGeometryColumn(\'peta_kemiringan_lereng\', \'geom\', 4326, \'MULTIPOLYGON\', 2)');
        DB::statement('CREATE INDEX idx_peta_kemiringan_lereng_geom ON peta_kemiringan_lereng USING GIST(geom)');

        // Peta Penggunaan Lahan
        Schema::create('peta_penggunaan_lahan', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('skor')->nullable()->comment('Bobot/skor: 1-5');
            $table->timestamps();
        });
        DB::statement('SELECT AddGeometryColumn(\'peta_penggunaan_lahan\', \'geom\', 4326, \'MULTIPOLYGON\', 2)');
        DB::statement('CREATE INDEX idx_peta_penggunaan_lahan_geom ON peta_penggunaan_lahan USING GIST(geom)');

        // Peta Jenis Tanah
        Schema::create('peta_jenis_tanah', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('skor')->nullable()->comment('Bobot/skor: 1-5');
            $table->timestamps();
        });
        DB::statement('SELECT AddGeometryColumn(\'peta_jenis_tanah\', \'geom\', 4326, \'MULTIPOLYGON\', 2)');
        DB::statement('CREATE INDEX idx_peta_jenis_tanah_geom ON peta_jenis_tanah USING GIST(geom)');

        // Peta Jarak Sungai
        Schema::create('peta_jarak_sungai', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('skor')->nullable()->comment('Bobot/skor: 1-5');
            $table->timestamps();
        });
        DB::statement('SELECT AddGeometryColumn(\'peta_jarak_sungai\', \'geom\', 4326, \'MULTIPOLYGON\', 2)');
        DB::statement('CREATE INDEX idx_peta_jarak_sungai_geom ON peta_jarak_sungai USING GIST(geom)');

        // Peta Elevasi
        Schema::create('peta_elevasi', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('skor')->nullable()->comment('Bobot/skor: 1-5');
            $table->timestamps();
        });
        DB::statement('SELECT AddGeometryColumn(\'peta_elevasi\', \'geom\', 4326, \'MULTIPOLYGON\', 2)');
        DB::statement('CREATE INDEX idx_peta_elevasi_geom ON peta_elevasi USING GIST(geom)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peta_elevasi');
        Schema::dropIfExists('peta_jarak_sungai');
        Schema::dropIfExists('peta_jenis_tanah');
        Schema::dropIfExists('peta_penggunaan_lahan');
        Schema::dropIfExists('peta_kemiringan_lereng');
        Schema::dropIfExists('peta_curah_hujan');
    }
};
