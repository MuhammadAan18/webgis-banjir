<?php

use App\Models\PetaRawanBanjir;
use App\Http\Controllers\GeoJsonImportController;
use Illuminate\Support\Facades\Route;
use App\Livewire\PetaSpk;

Route::get('/', PetaSpk::class);

Route::get('/status', function () {
    // Menghitung total poligon yang ada di dalam database
    $jumlahPoligon = PetaRawanBanjir::count();

    return [
        'status' => 'Koneksi Berhasil',
        'database' => 'PostgreSQL + PostGIS',
        'total_data_poligon' => $jumlahPoligon,
        'message' => "Siap untuk analisis. Total data poligon rawan banjir: " . $jumlahPoligon
    ];
});

// GeoJSON Import Routes
Route::get('/import', [GeoJsonImportController::class, 'showForm'])->name('geojson.form');
Route::post('/import', [GeoJsonImportController::class, 'import'])->name('geojson.import');
Route::get('/api/stats', [GeoJsonImportController::class, 'stats'])->name('geojson.stats');