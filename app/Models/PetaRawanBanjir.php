<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetaRawanBanjir extends Model
{
    // 1. Beritahu Laravel nama tabel spesifik Anda (karena bukan bahasa Inggris plural)
    protected $table = 'peta_rawan_banjir';

    // 2. Matikan timestamps karena hasil export QGIS tidak memiliki kolom created_at & updated_at
    public $timestamps = false;

    // 3. Izinkan mass assignment (opsional, untuk kemudahan nanti)
    protected $guarded = [];
}
