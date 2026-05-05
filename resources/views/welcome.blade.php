<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebGIS – Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 bg-opacity-70 backdrop-blur-lg p-6 flex flex-col space-y-4">
            <h1 class="text-2xl font-bold text-cyan-400 mb-6">WEBGIS</h1>
            <nav class="flex-1 space-y-2">
                <a href="#" class="block py-2 px-3 rounded hover:bg-gray-700 transition">Beranda</a>
                <a href="#" class="block py-2 px-3 rounded hover:bg-gray-700 transition">Analisis</a>
                <a href="#" class="block py-2 px-3 rounded hover:bg-gray-700 transition">Data</a>
                <a href="#" class="block py-2 px-3 rounded hover:bg-gray-700 transition">Tentang</a>
            </nav>
            <div class="mt-auto text-sm text-gray-400">
                © 2026 WEBGIS
            </div>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 flex flex-col p-6 space-y-6 overflow-hidden">
            <!-- Analysis Card -->
            @if($hasilAnalisis)
            <section class="bg-white/10 backdrop-blur-md rounded-lg border border-cyan-500 p-6">
                <h2 class="text-xl font-semibold text-cyan-300 mb-3">STATUS: {{ $hasilAnalisis['status'] }}</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>📏 Luas Total Lahan</div><div class="text-right font-medium">{{ number_format($hasilAnalisis['luas_total'], 0, ',', '.') }} m²</div>
                    <div>✅ Luas Area Aman</div><div class="text-right font-medium">{{ number_format($hasilAnalisis['luas_aman'], 0, ',', '.') }} m²</div>
                    <div>🌊 Luas Area Terendam Banjir</div><div class="text-right font-medium">{{ number_format($hasilAnalisis['luas_banjir'], 0, ',', '.') }} m²</div>
                    <div>⚠️ Persentase Bahaya</div><div class="text-right font-medium">{{ $hasilAnalisis['persentase_banjir'] }}%</div>
                </div>
            </section>
            @endif
            <!-- Map -->
            <section class="flex-1 rounded-lg overflow-hidden shadow-lg">
                <div id="map" class="w-full h-full" wire:ignore></div>
            </section>
        </main>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const map = L.map('map').setView([-8.5833, 116.1167], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);
            const drawControl = new L.Control.Draw({
                draw: {
                    polygon: true,
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },
                edit: { featureGroup: drawnItems }
            });
            map.addControl(drawControl);

            map.on(L.Draw.Event.CREATED, function (e) {
                const layer = e.layer;
                drawnItems.addLayer(layer);
                const geomString = JSON.stringify(layer.toGeoJSON().geometry);
                @this.call('analisisLahan', geomString);
            });
        });
    </script>
</body>
</html>