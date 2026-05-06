<?php

use App\Models\PetaRawanBanjir;
use App\Http\Controllers\SpatialLayerController;
use Illuminate\Support\Facades\Route;
use App\Livewire\PetaSpk;

Route::get('/', PetaSpk::class);

Route::get('/status', function () {
    $jumlahPoligon = PetaRawanBanjir::count();
    return [
        'status' => 'Koneksi Berhasil',
        'database' => 'PostgreSQL + PostGIS',
        'total_data_poligon' => $jumlahPoligon,
        'message' => "Siap untuk analisis. Total data poligon rawan banjir: " . $jumlahPoligon
    ];
});

// API endpoint for layer data (read-only)
Route::get('/api/parameters/{parameterType}/data', [SpatialLayerController::class, 'getLayerData'])->name('parameters.data');