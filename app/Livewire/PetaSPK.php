<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PetaSpk extends Component
{
    public $hasilAnalisis = null;
    public $errorMessage = null;

    public function analisisLahan($geoJsonGeometry, $selectedParams = null)
    {
        if ($selectedParams === null) {
            $selectedParams = ['rainfall', 'slope', 'land_use', 'soil_type', 'rivers', 'elevation'];
        }

        try {
            // Query menggunakan CTE agar geometri user hanya di-parsing satu kali.
            // Menggunakan tabel spatial_parameters yang terpadu (unified table).
            // Kita menggunakan AVG (rata-rata) jika lahan user memotong lebih dari satu poligon parameter.
            $query = DB::selectOne("
                WITH user_area AS (
                    SELECT ST_SetSRID(ST_GeomFromGeoJSON(?), 4326) AS geom
                )
                SELECT 
                    COALESCE((SELECT AVG(score) FROM spatial_parameters b WHERE b.parameter_type = 'rainfall'    AND ST_Intersects(user_area.geom, b.geom)), 0) as s_hujan,
                    COALESCE((SELECT AVG(score) FROM spatial_parameters b WHERE b.parameter_type = 'slope'       AND ST_Intersects(user_area.geom, b.geom)), 0) as s_lereng,
                    COALESCE((SELECT AVG(score) FROM spatial_parameters b WHERE b.parameter_type = 'land_use'    AND ST_Intersects(user_area.geom, b.geom)), 0) as s_lahan,
                    COALESCE((SELECT AVG(score) FROM spatial_parameters b WHERE b.parameter_type = 'soil_type'   AND ST_Intersects(user_area.geom, b.geom)), 0) as s_tanah,
                    COALESCE((SELECT AVG(score) FROM spatial_parameters b WHERE b.parameter_type = 'rivers'      AND ST_Intersects(user_area.geom, b.geom)), 0) as s_sungai,
                    COALESCE((SELECT AVG(score) FROM spatial_parameters b WHERE b.parameter_type = 'elevation'   AND ST_Intersects(user_area.geom, b.geom)), 0) as s_elevasi
                FROM user_area
            ", [$geoJsonGeometry]);

            $weights = [
                'rainfall' => 0.30,
                'slope' => 0.15,
                'land_use' => 0.15,
                'soil_type' => 0.15,
                'rivers' => 0.15,
                'elevation' => 0.10,
            ];

            $totalWeight = 0;
            foreach ($selectedParams as $param) {
                if (isset($weights[$param])) {
                    $totalWeight += $weights[$param];
                }
            }

            if ($totalWeight == 0) {
                throw new \Exception("Pilih minimal satu parameter.");
            }

            $w_hujan = in_array('rainfall', $selectedParams) ? ($weights['rainfall'] / $totalWeight) : 0;
            $w_lereng = in_array('slope', $selectedParams) ? ($weights['slope'] / $totalWeight) : 0;
            $w_lahan = in_array('land_use', $selectedParams) ? ($weights['land_use'] / $totalWeight) : 0;
            $w_tanah = in_array('soil_type', $selectedParams) ? ($weights['soil_type'] / $totalWeight) : 0;
            $w_sungai = in_array('rivers', $selectedParams) ? ($weights['rivers'] / $totalWeight) : 0;
            $w_elevasi = in_array('elevation', $selectedParams) ? ($weights['elevation'] / $totalWeight) : 0;

            // Kalkulasi Skor Akhir (Weighted Overlay berdasarkan Jurnal)
            $totalSkor = (
                ($query->s_hujan * $w_hujan) +
                ($query->s_lereng * $w_lereng) +
                ($query->s_lahan * $w_lahan) +
                ($query->s_tanah * $w_tanah) +
                ($query->s_sungai * $w_sungai) +
                ($query->s_elevasi * $w_elevasi)
            );

            // Klasifikasi Kerawanan Banjir (sesuai spesifikasi)
            if ($totalSkor >= 4.0) {
                $status = "SANGAT RAWAN";
                $warnaBg = "#fee2e2";
                $warnaTeks = "#7f1d1d";
            } elseif ($totalSkor >= 3.0) {
                $status = "RAWAN";
                $warnaBg = "#ffedd5";
                $warnaTeks = "#ea580c";
            } elseif ($totalSkor >= 2.0) {
                $status = "CUKUP AMAN";
                $warnaBg = "#fef3c7";
                $warnaTeks = "#d97706";
            } else {
                $status = "AMAN";
                $warnaBg = "#d1fae5";
                $warnaTeks = "#059669";
            }

            $detailSkor = [];
            if (in_array('rainfall', $selectedParams)) $detailSkor['Curah Hujan (' . round(($weights['rainfall'] / $totalWeight) * 100) . '%)'] = round($query->s_hujan, 2);
            if (in_array('slope', $selectedParams)) $detailSkor['Lereng (' . round(($weights['slope'] / $totalWeight) * 100) . '%)'] = round($query->s_lereng, 2);
            if (in_array('land_use', $selectedParams)) $detailSkor['Lahan (' . round(($weights['land_use'] / $totalWeight) * 100) . '%)'] = round($query->s_lahan, 2);
            if (in_array('soil_type', $selectedParams)) $detailSkor['Jenis Tanah (' . round(($weights['soil_type'] / $totalWeight) * 100) . '%)'] = round($query->s_tanah, 2);
            if (in_array('rivers', $selectedParams)) $detailSkor['Jarak Sungai (' . round(($weights['rivers'] / $totalWeight) * 100) . '%)'] = round($query->s_sungai, 2);
            if (in_array('elevation', $selectedParams)) $detailSkor['Elevasi (' . round(($weights['elevation'] / $totalWeight) * 100) . '%)'] = round($query->s_elevasi, 2);

            // Simpan hasil untuk dikirim ke UI (Blade)
            $this->hasilAnalisis = [
                'skor_akhir' => round($totalSkor, 2),
                'status' => $status,
                'warna_bg' => $warnaBg,
                'warna_teks' => $warnaTeks,
                'detail_skor' => $detailSkor
            ];
            $this->errorMessage = null;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error analisis: ' . $e->getMessage();
            $this->hasilAnalisis = null;
        }
    }

    public function render()
    {
        return view('components.peta-spk')
            ->layout('layouts.app', ['title' => 'SIMBA — Analisis Kerawanan Banjir']);
    }
}