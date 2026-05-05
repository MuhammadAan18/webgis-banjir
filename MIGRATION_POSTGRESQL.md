# Dokumentasi Migrasi WebGIS Banjir ke PostgreSQL

## Status: ✅ SELESAI

Aplikasi sudah berhasil dimigrasikan dari SQLite ke PostgreSQL dengan PostGIS untuk spatial analysis.

---

## 📊 Data yang Diimport

- **Total Features**: 101,941 poligon rawan banjir
- **Geometry Type**: MultiPolygon + Polygon
- **SRID (Coordinate System)**: EPSG:4326 (WGS84)
- **Database**: `webgis`
- **Table**: `peta_rawan_banjir`

---

## 🔧 Konfigurasi Database

### File .env

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=webgis
DB_USERNAME=postgres
DB_PASSWORD=aan180405
```

### Extensions yang Diaktifkan

- ✅ PostGIS (untuk spatial queries)
- ✅ ST_Area, ST_Intersects, ST_Geometry functions

---

## 📁 File yang Dimodifikasi/Dibuat

### Migrations

1. `2026_05_04_051150_create_peta_rawan_banjir_table.php`
    - Membuat tabel dengan geometry column
    - Membuat spatial index

2. `2026_05_04_052008_alter_peta_rawan_banjir_geometry_type.php`
    - Mengubah tipe geometry dari POLYGON → GEOMETRY
    - Untuk support MultiPolygon

### Seeders

- `PetaRawanBanjirSeeder.php` - Seeder untuk import data GeoJSON

### Scripts

- `import_geojson.php` - Script standalone untuk import data dari GeoJSON file

### Routes

- `routes/web.php` - Updated routes untuk status check

---

## 🚀 Menjalankan Aplikasi

### 1. Start Laravel Server

```bash
php artisan serve
```

Aplikasi akan berjalan di: `http://127.0.0.1:8000`

### 2. Cek Status Database

```bash
curl http://127.0.0.1:8000/status
```

Response:

```json
{
    "status": "Koneksi Berhasil",
    "database": "PostgreSQL + PostGIS",
    "total_data_poligon": 101941,
    "message": "Siap untuk analisis. Total data poligon rawan banjir: 101941"
}
```

### 3. Akses pgAdmin 4

- **URL**: http://localhost/phpmyadmin (atau pgAdmin 4 jika terinstall terpisah)
- **Server**: localhost
- **Database**: webgis
- **Username**: postgres
- **Password**: aan180405

---

## 📍 Fitur Spatial Queries yang Tersedia

Aplikasi sudah siap menggunakan:

- `ST_Area()` - Menghitung luas area
- `ST_Intersects()` - Mengecek perpotongan geometry
- `ST_Intersection()` - Menghitung area irisan
- `ST_Transform()` - Transform coordinate system
- `ST_GeomFromGeoJSON()` - Parse GeoJSON

---

## ✨ Kapasitas Data

- **File GeoJSON**: 442.7 MB
- **Total Records**: 101,941
- **Memory Usage**: ~512 MB untuk import
- **Import Time**: ~2-3 menit

---

## 🔄 Cara Re-import Data (Jika Diperlukan)

```bash
# Drop table
php artisan tinker
> DB::statement('DROP TABLE IF EXISTS peta_rawan_banjir CASCADE')
> exit

# Re-migrate
php artisan migrate

# Import data
php -d memory_limit=-1 import_geojson.php
```

---

## 📋 Troubleshooting

### Error: Geometry type mismatch

**Penyebab**: Column geometry berjenis POLYGON tapi data berisi MultiPolygon
**Solusi**: Sudah dilakukan - geometry column diubah menjadi GEOMETRY (generic)

### Error: Connection refused

**Penyebab**: PostgreSQL service tidak running
**Solusi**:

```powershell
Get-Service -Name postgresql* | Start-Service
```

### Error: Memory exhausted

**Penyebab**: File GeoJSON terlalu besar
**Solusi**: Gunakan script `import_geojson.php` dengan unlimited memory

```bash
php -d memory_limit=-1 import_geojson.php
```

---

## 📞 Kontak & Support

Jika ada pertanyaan atau error, silakan check:

1. Status PostgreSQL service
2. Kredensial database di `.env`
3. PostGIS extension sudah aktif: `DB::statement('SELECT PostGIS_Full_Version()')`

---

**Last Updated**: 2026-05-04
**Status**: Production Ready ✅
