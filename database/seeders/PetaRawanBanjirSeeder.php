<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PetaRawanBanjirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $geojsonFile = public_path('data/PetaRawanBanjir.geojson');

        if (!file_exists($geojsonFile)) {
            $this->command->error("File GeoJSON tidak ditemukan: $geojsonFile");
            return;
        }

        $this->command->info("Membaca dan import file GeoJSON secara streaming...");
        
        // Gunakan streaming untuk file besar
        $this->importGeoJsonStreaming($geojsonFile);
    }

    private function importGeoJsonStreaming($filePath)
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->command->error("Tidak bisa membuka file: $filePath");
            return;
        }

        $buffer = '';
        $inFeature = false;
        $braceCount = 0;
        $featureCount = 0;
        $insertCount = 0;

        while (!feof($handle)) {
            $line = fgets($handle, 8192);
            if ($line === false) break;

            $buffer .= $line;

            // Hitung brace untuk mendeteksi feature yang lengkap
            $braceCount += substr_count($line, '{') - substr_count($line, '}');

            // Jika braceCount kembali ke 0, mungkin ada feature yang lengkap
            if ($braceCount === 0 && strpos($buffer, '"geometry"') !== false) {
                // Coba extract feature
                if (preg_match('/\{\s*"type"\s*:\s*"Feature".*?\}\s*$/s', $buffer, $matches)) {
                    $featureJson = $matches[0];
                    $feature = json_decode($featureJson, true);
                    
                    if ($feature && isset($feature['geometry'])) {
                        $this->insertFeature($feature);
                        $insertCount++;
                    }

                    $featureCount++;
                    $buffer = '';
                    
                    if ($featureCount % 100 === 0) {
                        $this->command->info("Imported: $insertCount features");
                    }
                }
            }
        }

        fclose($handle);
        $this->command->info("Import selesai! Total: $insertCount features");
    }

    private function insertFeature($feature)
    {
        try {
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;

            if (!$geometry) {
                return;
            }

            $geomJson = json_encode($geometry);

            DB::insert("
                INSERT INTO peta_rawan_banjir (fid, rawan_banj, panjang, geom, created_at, updated_at)
                VALUES (?, ?, ?, ST_GeomFromGeoJSON(?), NOW(), NOW())
            ", [
                $properties['fid'] ?? null,
                $properties['rawan_banj'] ?? null,
                $properties['panjang'] ?? null,
                $geomJson,
            ]);
        } catch (\Exception $e) {
            // Skip error
        }
    }
}