<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PetaSpk extends Component
{
    public $hasilAnalisis = null;
    public $errorMessage = null;

    public function analisisLahan($geoJsonGeometry)
    {
        try {
            // Validasi input GeoJSON
            $geom = json_decode($geoJsonGeometry, true);
            if (!$geom || !isset($geom['type'])) {
                throw new \Exception('Format GeoJSON tidak valid');
            }

            // 1. Hitung Luas Total Lahan User
            $queryTotal = DB::selectOne("
                SELECT ST_Area(ST_Transform(ST_MakeValid(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)), 32750)) AS luas_total
            ", [$geoJsonGeometry]);

            if (!$queryTotal || $queryTotal->luas_total === null) {
                throw new \Exception('Gagal menghitung luas total');
            }

            $luasTotal = round($queryTotal->luas_total, 2);

            // 2. Tabrakkan lahan User dengan Peta Rawan Banjir
            // Gunakan ST_MakeValid untuk handle geometri invalid
            $queryBanjir = DB::selectOne("
                WITH user_geom AS (
                    SELECT ST_Transform(ST_MakeValid(ST_SetSRID(ST_GeomFromGeoJSON(?), 4326)), 32750) AS geom
                )
                SELECT COALESCE(SUM(ST_Area(ST_Intersection(u.geom, ST_MakeValid(b.geom)))), 0) AS luas_banjir
                FROM peta_rawan_banjir b, user_geom u
                WHERE ST_Intersects(u.geom, b.geom)
                  AND ST_IsValid(b.geom)
            ", [$geoJsonGeometry]);

            $luasBanjir = round($queryBanjir->luas_banjir, 2);

            // 3. Kalkulasi Sisa Lahan & Persentase
            $luasAman = max(0, $luasTotal - $luasBanjir);
            $persentaseBanjir = $luasTotal > 0 ? round(($luasBanjir / $luasTotal) * 100, 2) : 0;

            // 4. Algoritma Keputusan SPK (Threshold Kelayakan)
            if ($persentaseBanjir > 30) {
                $status = 'TIDAK LAYAK BANGUN (Risiko Banjir Tinggi)';
                $warnaBg = '#fecaca'; // Merah Muda
                $warnaTeks = '#991b1b'; // Merah Tua
            } else {
                $status = 'LAYAK BANGUN (Aman dari Banjir)';
                $warnaBg = '#d1fae5'; // Hijau Muda
                $warnaTeks = '#065f46'; // Hijau Tua
            }

            // Simpan hasil
            $this->hasilAnalisis = [
                'luas_total' => $luasTotal,
                'luas_aman' => $luasAman,
                'luas_banjir' => $luasBanjir,
                'persentase_banjir' => $persentaseBanjir,
                'status' => $status,
                'warna_bg' => $warnaBg,
                'warna_teks' => $warnaTeks,
            ];
            $this->errorMessage = null;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error analisis: ' . $e->getMessage();
            $this->hasilAnalisis = null;
        }
    }

    public function render()
    {
        return view('components.peta-spk');
    }
}