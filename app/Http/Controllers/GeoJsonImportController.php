<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeoJsonImportController extends Controller
{
    /**
     * Tampilkan form upload GeoJSON
     */
    public function showForm()
    {
        return view('import-geojson');
    }

    /**
     * Handle upload dan import GeoJSON ke PostgreSQL
     */
    public function import(Request $request)
    {
        $request->validate([
            'geojson_file' => 'required|file|mimes:geojson,json',
        ]);

        try {
            // Baca file
            $file = $request->file('geojson_file');
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format GeoJSON tidak valid: ' . json_last_error_msg()
                ], 400);
            }

            if (!isset($data['features'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format GeoJSON tidak valid (missing features)'
                ], 400);
            }

            $features = $data['features'];
            $total = count($features);

            // Optional: Replace mode
            if ($request->has('replace_data') && $request->replace_data) {
                DB::statement('DELETE FROM peta_rawan_banjir');
            }

            $inserted = 0;
            $updated = 0;
            $errors = 0;
            $errorDetails = [];

            // Import data
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

                    if ($fid) {
                        $existing = DB::table('peta_rawan_banjir')->where('fid', $fid)->exists();

                        if ($existing) {
                            DB::update("
                                UPDATE peta_rawan_banjir 
                                SET rawan_banj = ?, panjang = ?, geom = ST_MakeValid(ST_GeomFromGeoJSON(?)), updated_at = NOW()
                                WHERE fid = ?
                            ", [$rawan_banj, $panjang, $geomJson, $fid]);
                            $updated++;
                        } else {
                            DB::insert("
                                INSERT INTO peta_rawan_banjir (fid, rawan_banj, panjang, geom, created_at, updated_at)
                                VALUES (?, ?, ?, ST_MakeValid(ST_GeomFromGeoJSON(?)), NOW(), NOW())
                            ", [$fid, $rawan_banj, $panjang, $geomJson]);
                            $inserted++;
                        }
                    } else {
                        DB::insert("
                            INSERT INTO peta_rawan_banjir (rawan_banj, panjang, geom, created_at, updated_at)
                            VALUES (?, ?, ST_MakeValid(ST_GeomFromGeoJSON(?)), NOW(), NOW())
                        ", [$rawan_banj, $panjang, $geomJson]);
                        $inserted++;
                    }

                } catch (\Exception $e) {
                    $errors++;
                    if ($errors <= 5) {
                        $errorDetails[] = "Feature $index: " . $e->getMessage();
                    }
                }
            }

            // Bersihkan invalid geometries
            DB::statement("UPDATE peta_rawan_banjir SET geom = ST_MakeValid(geom) WHERE ST_IsValid(geom) = false");

            // Count total records
            $totalRecords = DB::table('peta_rawan_banjir')->count();

            return response()->json([
                'success' => true,
                'message' => 'Import GeoJSON berhasil',
                'results' => [
                    'total_features' => $total,
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'errors' => $errors,
                    'total_records_database' => $totalRecords,
                ],
                'error_details' => $errorDetails,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get statistik data di database
     */
    public function stats()
    {
        try {
            $total = DB::table('peta_rawan_banjir')->count();
            $invalid = DB::selectOne("SELECT COUNT(*) as cnt FROM peta_rawan_banjir WHERE ST_IsValid(geom) = false");
            $totalArea = DB::selectOne("
                SELECT ROUND(SUM(ST_Area(ST_Transform(geom, 32750)))::numeric, 2) as total_area_m2
                FROM peta_rawan_banjir
                WHERE ST_IsValid(geom)
            ");

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_records' => $total,
                    'invalid_geometries' => $invalid->cnt ?? 0,
                    'total_area_m2' => $totalArea->total_area_m2 ?? 0,
                    'total_area_km2' => round(($totalArea->total_area_m2 ?? 0) / 1000000, 2),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
