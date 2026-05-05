<?php
/**
 * Script untuk import GeoJSON ke PostgreSQL
 * Fitur:
 * - Handle geometry invalid dengan ST_MakeValid
 * - Support MultiPolygon dan Polygon
 * - Skip data yang sudah ada (based on fid)
 * - Error handling yang lebih baik
 * 
 * Jalankan: php -d memory_limit=-1 import_geojson_new.php path/to/file.geojson [--replace]
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Parse arguments
$geojsonFile = $argv[1] ?? null;
$replace = isset($argv[2]) && $argv[2] === '--replace';

if (!$geojsonFile) {
	echo "❌ Error: File GeoJSON tidak diberikan\n";
	echo "Usage: php import_geojson_new.php <file.geojson> [--replace]\n";
	echo "  --replace: Hapus semua data lama sebelum import (optional)\n";
	exit(1);
}

if (!file_exists($geojsonFile)) {
	echo "❌ Error: File tidak ditemukan: $geojsonFile\n";
	exit(1);
}

echo "📂 File: $geojsonFile\n";
$fileSize = filesize($geojsonFile);
echo "📊 Ukuran: " . round($fileSize / 1024 / 1024, 2) . " MB\n";

// Baca file
echo "\n⏳ Membaca GeoJSON file...\n";
$json = file_get_contents($geojsonFile);
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
	echo "❌ Error parsing JSON: " . json_last_error_msg() . "\n";
	exit(1);
}

if (!isset($data['features'])) {
	echo "❌ Error: Format GeoJSON tidak valid (missing 'features')\n";
	exit(1);
}

$features = $data['features'];
$total = count($features);
echo "✅ Total features: $total\n";

// Optional: Replace mode
if ($replace) {
	echo "\n⚠️  Mode REPLACE: Menghapus semua data lama...\n";
	DB::statement('DELETE FROM peta_rawan_banjir');
	echo "✅ Semua data lama dihapus\n";
}

$inserted = 0;
$updated = 0;
$errors = 0;
$errorDetails = [];

echo "\n⏳ Importing data...\n";

foreach ($features as $index => $feature) {
	try {
		$properties = $feature['properties'] ?? [];
		$geometry = $feature['geometry'] ?? null;

		if (!$geometry) {
			continue;
		}

		$fid = $properties['fid'] ?? null;
		$rawan_banj = $properties['rawan_banj'] ?? null;
		$panjang = $properties['panjang'] ?? null;
		$geomJson = json_encode($geometry);

		// Validasi geometry
		$geomCheck = DB::selectOne("SELECT ST_GeomFromGeoJSON(?) as geom", [$geomJson]);

		if ($fid) {
			// Check if exists
			$existing = DB::table('peta_rawan_banjir')->where('fid', $fid)->first();

			if ($existing) {
				// Update
				DB::update("
                    UPDATE peta_rawan_banjir 
                    SET rawan_banj = ?, panjang = ?, geom = ST_MakeValid(ST_GeomFromGeoJSON(?)), updated_at = NOW()
                    WHERE fid = ?
                ", [$rawan_banj, $panjang, $geomJson, $fid]);
				$updated++;
			} else {
				// Insert
				DB::insert("
                    INSERT INTO peta_rawan_banjir (fid, rawan_banj, panjang, geom, created_at, updated_at)
                    VALUES (?, ?, ?, ST_MakeValid(ST_GeomFromGeoJSON(?)), NOW(), NOW())
                ", [$fid, $rawan_banj, $panjang, $geomJson]);
				$inserted++;
			}
		} else {
			// Insert tanpa fid
			DB::insert("
                INSERT INTO peta_rawan_banjir (rawan_banj, panjang, geom, created_at, updated_at)
                VALUES (?, ?, ST_MakeValid(ST_GeomFromGeoJSON(?)), NOW(), NOW())
            ", [$rawan_banj, $panjang, $geomJson]);
			$inserted++;
		}

		if (($index + 1) % 10000 === 0) {
			echo "  ✓ Progress: " . ($index + 1) . " / $total (Inserted: $inserted, Updated: $updated, Errors: $errors)\n";
		}

	} catch (\Exception $e) {
		$errors++;
		if ($errors <= 5) {
			$errorDetails[] = "  Index $index: " . $e->getMessage();
		}
	}
}

echo "\n📊 ===== HASIL IMPORT =====\n";
echo "✅ Total features: $total\n";
echo "✅ Berhasil diinsert: $inserted\n";
echo "✅ Berhasil diupdate: $updated\n";
echo "❌ Errors: $errors\n";

if ($errorDetails) {
	echo "\n⚠️  Error Details (first 5):\n";
	foreach ($errorDetails as $detail) {
		echo $detail . "\n";
	}
}

// Final check
$count = DB::table('peta_rawan_banjir')->count();
echo "\n📈 Total records di database sekarang: $count\n";

// Check invalid geometries
$invalid = DB::selectOne("SELECT COUNT(*) as cnt FROM peta_rawan_banjir WHERE ST_IsValid(geom) = false");
echo "🔍 Invalid geometries: " . $invalid->cnt . "\n";

if ($invalid->cnt > 0) {
	echo "⏳ Membenahi invalid geometries...\n";
	DB::statement("UPDATE peta_rawan_banjir SET geom = ST_MakeValid(geom) WHERE ST_IsValid(geom) = false");
	echo "✅ Geometries sudah diperbaiki\n";
}

echo "\n✨ Import selesai!\n";
