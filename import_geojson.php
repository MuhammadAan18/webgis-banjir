<?php
/**
 * Script untuk import GeoJSON ke PostgreSQL
 * Jalankan: php -d memory_limit=-1 import_geojson.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$geojsonFile = __DIR__ . '/public/data/PetaRawanBanjir.geojson';

if (!file_exists($geojsonFile)) {
	echo "Error: File tidak ditemukan: $geojsonFile\n";
	exit(1);
}

echo "Membaca GeoJSON file...\n";
$fileSize = filesize($geojsonFile);
echo "Ukuran file: " . round($fileSize / 1024 / 1024, 2) . " MB\n";

// Baca seluruh file (dengan unlimited memory)
$json = file_get_contents($geojsonFile);
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
	echo "Error parsing JSON: " . json_last_error_msg() . "\n";
	exit(1);
}

if (!isset($data['features'])) {
	echo "Error: Format GeoJSON tidak valid\n";
	exit(1);
}

$features = $data['features'];
$total = count($features);
echo "Total features: $total\n";

$inserted = 0;
$errors = 0;

foreach ($features as $index => $feature) {
	try {
		$properties = $feature['properties'] ?? [];
		$geometry = $feature['geometry'] ?? null;

		if (!$geometry) {
			continue;
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

		$inserted++;

		if (($index + 1) % 1000 === 0) {
			echo "Progress: " . ($index + 1) . " / $total\n";
		}
	} catch (\Exception $e) {
		$errors++;
		if ($index < 5) {  // Show first 5 errors
			echo "Error at index $index: " . $e->getMessage() . "\n";
		}
	}
}

echo "\n=== IMPORT SELESAI ===\n";
echo "Total features: $total\n";
echo "Berhasil diinsert: $inserted\n";
echo "Errors: $errors\n";
