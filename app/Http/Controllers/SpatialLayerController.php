<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SpatialLayerController extends Controller
{
    /**
     * Return GeoJSON FeatureCollection for a given parameter type.
     * Used by Leaflet to render spatial layers on the map.
     */
    public function getLayerData($parameterType)
    {
        $validTypes = ['rainfall', 'slope', 'land_use', 'soil_type', 'rivers', 'elevation'];

        if (!in_array($parameterType, $validTypes)) {
            return response()->json(['error' => 'Invalid parameter type'], 422);
        }

        try {
            $rows = DB::select("
                SELECT id, parameter_type, parameter_name, score,
                       ST_AsGeoJSON(geom) as geojson
                FROM spatial_parameters
                WHERE parameter_type = ?
                  AND score >= 1 AND score <= 5
                LIMIT 10000
            ", [$parameterType]);

            $geoJsonFeatures = [];
            foreach ($rows as $row) {
                $geoJsonFeatures[] = [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $row->id,
                        'score' => (int) $row->score,
                        'parameter_name' => $row->parameter_name,
                        'parameter_type' => $row->parameter_type,
                    ],
                    'geometry' => json_decode($row->geojson),
                ];
            }

            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $geoJsonFeatures
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
