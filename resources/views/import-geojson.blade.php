<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import GeoJSON - SIMBA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">📥 Import Data GeoJSON</h1>
            <p class="text-gray-600">Upload file GeoJSON untuk import data rawan banjir ke PostgreSQL</p>
        </div>

        <!-- Stats Card -->
        <div id="statsCard" class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 hidden">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">📊 Statistik Database</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-3 rounded border border-blue-200">
                    <div class="text-sm text-gray-600">Total Records</div>
                    <div class="text-2xl font-bold text-blue-600" id="totalRecords">0</div>
                </div>
                <div class="bg-white p-3 rounded border border-blue-200">
                    <div class="text-sm text-gray-600">Invalid Geom</div>
                    <div class="text-2xl font-bold text-red-600" id="invalidGeom">0</div>
                </div>
                <div class="bg-white p-3 rounded border border-blue-200">
                    <div class="text-sm text-gray-600">Area (m²)</div>
                    <div class="text-lg font-bold text-green-600" id="areaM2">0</div>
                </div>
                <div class="bg-white p-3 rounded border border-blue-200">
                    <div class="text-sm text-gray-600">Area (km²)</div>
                    <div class="text-lg font-bold text-green-600" id="areaKm2">0</div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-md p-8 mb-6">
            <form id="uploadForm" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- File Input -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 hover:bg-blue-50 transition cursor-pointer" id="dropZone">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-8-12l-3.172-3.172a2 2 0 00-2.828 0L28 12m0 0l6 6m-6-6v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input type="file" id="geojson_file" name="geojson_file" accept=".geojson,.json" class="hidden" required>
                    <p class="text-gray-600 mb-2">
                        <span class="font-semibold text-blue-600 hover:text-blue-800">Klik untuk upload</span> atau drag & drop
                    </p>
                    <p class="text-sm text-gray-500">File GeoJSON atau JSON (.geojson, .json)</p>
                    <p class="text-sm text-gray-400 mt-2" id="fileName">Tidak ada file dipilih</p>
                </div>

                <!-- Options -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" id="replace_data" name="replace_data" value="1" class="w-4 h-4 text-red-600 rounded focus:ring-red-500 border-gray-300">
                        <span class="text-sm text-gray-700">
                            <span class="font-semibold text-red-600">Hapus semua data lama</span> sebelum import (mode REPLACE)
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2 ml-7">Jika tidak dicentang, data baru akan ditambahkan/update (mode APPEND)</p>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" id="submitBtn" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <span>⏳ Import ke PostgreSQL</span>
                    </button>
                    <button type="button" id="statsBtn" class="px-6 bg-gray-200 text-gray-800 font-semibold py-3 rounded-lg hover:bg-gray-300 transition">
                        📊 Statistik
                    </button>
                </div>
            </form>
        </div>

        <!-- Progress Bar -->
        <div id="progressContainer" class="hidden">
            <div class="bg-gray-100 rounded-lg p-4 mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-700">Sedang import...</span>
                    <span class="text-sm font-semibold text-gray-700" id="progressText">0%</span>
                </div>
                <div class="w-full bg-gray-300 rounded-full h-2 overflow-hidden">
                    <div id="progressBar" class="bg-blue-600 h-full rounded-full transition-all" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Result Card -->
        <div id="resultCard" class="hidden">
            <div id="resultContent" class="bg-white rounded-lg shadow-md p-6 border-l-4">
                <h3 id="resultTitle" class="text-lg font-semibold mb-4"></h3>
                <div id="resultDetails" class="space-y-2 text-sm"></div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 mb-2">ℹ️ Informasi</h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>File GeoJSON akan diproses dan disimpan langsung ke PostgreSQL</li>
                <li>File tidak akan disimpan di folder project (temporary saja)</li>
                <li>Mode <strong>APPEND</strong>: Data baru ditambahkan/update (default)</li>
                <li>Mode <strong>REPLACE</strong>: Semua data lama dihapus terlebih dahulu</li>
                <li>Geometry yang invalid akan otomatis diperbaiki</li>
            </ul>
        </div>
    </div>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('geojson_file');
const fileNameDisplay = document.getElementById('fileName');
const uploadForm = document.getElementById('uploadForm');
const submitBtn = document.getElementById('submitBtn');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');
const resultCard = document.getElementById('resultCard');
const resultTitle = document.getElementById('resultTitle');
const resultDetails = document.getElementById('resultDetails');
const resultContent = document.getElementById('resultContent');
const statsBtn = document.getElementById('statsBtn');
const statsCard = document.getElementById('statsCard');

// Drag & drop
dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
});
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateFileName();
    }
});

fileInput.addEventListener('change', updateFileName);

function updateFileName() {
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileNameDisplay.textContent = `✓ ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
    } else {
        fileNameDisplay.textContent = 'Tidak ada file dipilih';
    }
}

// Stats
statsBtn.addEventListener('click', async () => {
    try {
        const response = await fetch('{{ route("geojson.stats") }}');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalRecords').textContent = data.stats.total_records.toLocaleString();
            document.getElementById('invalidGeom').textContent = data.stats.invalid_geometries;
            document.getElementById('areaM2').textContent = data.stats.total_area_m2.toLocaleString();
            document.getElementById('areaKm2').textContent = data.stats.total_area_km2.toLocaleString();
            statsCard.classList.remove('hidden');
        }
    } catch (error) {
        alert('Error loading stats: ' + error.message);
    }
});

// Form submit
uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!fileInput.files.length) {
        alert('Pilih file GeoJSON terlebih dahulu');
        return;
    }

    submitBtn.disabled = true;
    progressContainer.classList.remove('hidden');
    resultCard.classList.add('hidden');

    const formData = new FormData(uploadForm);
    
    try {
        const response = await fetch('{{ route("geojson.import") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();
        
        progressContainer.classList.add('hidden');
        submitBtn.disabled = false;

        // Show result
        if (data.success) {
            resultContent.classList.remove('border-red-400');
            resultContent.classList.add('border-green-400');
            resultTitle.textContent = '✅ ' + data.message;
            resultTitle.classList.remove('text-red-600');
            resultTitle.classList.add('text-green-600');

            const html = `
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div><strong>Total Features:</strong> ${data.results.total_features.toLocaleString()}</div>
                    <div><strong>Inserted:</strong> <span class="text-green-600 font-bold">${data.results.inserted}</span></div>
                    <div><strong>Updated:</strong> <span class="text-blue-600 font-bold">${data.results.updated}</span></div>
                    <div><strong>Errors:</strong> <span class="text-red-600 font-bold">${data.results.errors}</span></div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded p-3 text-green-800">
                    <strong>Total Records di Database:</strong> ${data.results.total_records_database.toLocaleString()}
                </div>
                ${data.error_details.length > 0 ? `
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded p-3">
                    <strong class="text-yellow-800 block mb-2">⚠️ Error Details:</strong>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        ${data.error_details.map(e => `<li>${e}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
            `;
            resultDetails.innerHTML = html;
        } else {
            resultContent.classList.remove('border-green-400');
            resultContent.classList.add('border-red-400');
            resultTitle.textContent = '❌ ' + data.message;
            resultTitle.classList.remove('text-green-600');
            resultTitle.classList.add('text-red-600');
            resultDetails.innerHTML = `<p class="text-red-700">${data.message}</p>`;
        }

        resultCard.classList.remove('hidden');
        uploadForm.reset();
        updateFileName();

    } catch (error) {
        progressContainer.classList.add('hidden');
        submitBtn.disabled = false;
        resultContent.classList.add('border-red-400');
        resultTitle.textContent = '❌ Error: ' + error.message;
        resultTitle.classList.add('text-red-600');
        resultDetails.innerHTML = `<p class="text-red-700">${error.message}</p>`;
        resultCard.classList.remove('hidden');
    }
});
</script>

<style>
#dropZone {
    transition: all 0.3s ease;
}
</style>
</body>
</html>
