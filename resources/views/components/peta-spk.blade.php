<div class="w-full h-screen flex">
    {{-- Map Container --}}
    <div class="flex-1 flex flex-col">
        {{-- Header --}}
        <div class="bg-gray-900 text-white px-6 py-4 shadow flex flex-col items-center justify-center text-center">
            <h1 class="text-2xl font-bold">Analisis Daerah Kerawanan Banjir Kota Mataram</h1>
            <p class="text-sm text-blue-100 mt-1">Gambar area lahan untuk analisis multi-parameter</p>
        </div>

        {{-- Map --}}
        <div id="map" class="flex-1" wire:ignore></div>

        {{-- Error Message --}}
        @if($errorMessage)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3">
                {{ $errorMessage }}
            </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="w-80 bg-white border-l border-gray-200 flex flex-col">
        {{-- Layer Manager --}}
        <div class="flex-1 overflow-y-auto">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center justify-center">Parameter Management</h2>

                {{-- Toggle All --}}
                <button id="toggleAllLayers"
                    class="w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm rounded mb-3 transition">
                    Tampilkan Semua Layer
                </button>

                {{-- Parameter Layers --}}
                <div class="space-y-2" id="layerList">
                    <p class="text-xs text-gray-400 ">Memuat layer...</p>
                </div>

                {{-- Color Legend --}}
                <div class="mt-6 p-3 bg-gray-50 rounded">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Legenda Score</h3>
                    <div class="space-y-1 text-sm text-gray-700">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded" style="background-color: #d9f0a3;"></div>
                            <span class="ml-2 text-gray-900">Score 1 (Rendah)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded" style="background-color: #91cf60;"></div>
                            <span class="ml-2 text-gray-900">Score 2</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded" style="background-color: #1d9641;"></div>
                            <span class="ml-2 text-gray-900">Score 3</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded" style="background-color: #de2d26;"></div>
                            <span class="ml-2 text-gray-900">Score 4</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded" style="background-color: #8b0000;"></div>
                            <span class="ml-2 text-gray-900">Score 5 (Tinggi)</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
                    <p class="text-xs text-blue-800">
                        <strong>Tips:</strong> Gunakan toolbar untuk menggambar polygon pada peta, kemudian klik tombol
                        analisis di bawah untuk melihat hasil SPK.
                    </p>
                </div>
            </div>

            {{-- Analysis Results --}}
            <div class="p-4">
                <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center justify-center">Hasil Analisis</h2>

                @if($hasilAnalisis)
                    <div class="p-3 rounded-lg mb-4"
                        style="background-color: {{ $hasilAnalisis['warna_bg'] }}; border-left: 4px solid {{ $hasilAnalisis['warna_teks'] }};">
                        <p class="font-bold text-lg" style="color: {{ $hasilAnalisis['warna_teks'] }};">
                            {{ $hasilAnalisis['status'] }}
                        </p>
                        <p class="text-sm mt-2" style="color: {{ $hasilAnalisis['warna_teks'] }};">
                            <strong>Skor Kerawanan:</strong> {{ $hasilAnalisis['skor_akhir'] }}/5
                        </p>
                    </div>

                    {{-- Detail Scoring Table --}}
                    <div class="bg-white p-3 rounded mb-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-900 mb-3 text-sm">Detail Skor Per Parameter</h3>
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-2 text-gray-700">Parameter</th>
                                    <th class="text-center py-2 px-2 text-gray-700">Skor</th>
                                    <th class="text-right py-2 px-2 text-gray-700">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hasilAnalisis['detail_skor'] as $paramName => $skor)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-2 px-2 text-gray-700">{{ $paramName }}</td>
                                        <td class="text-center py-2 px-2 font-semibold text-gray-900">{{ $skor }}</td>
                                        <td class="text-right py-2 px-2">
                                            <div class="flex items-center gap-1">
                                                <div class="w-16 bg-gray-200 rounded h-2">
                                                    <div class="bg-gradient-to-r from-green-400 to-red-500 h-2 rounded"
                                                        style="width: {{ ($skor / 5) * 100 }}%;"></div>
                                                </div>
                                                <span class="text-xs text-gray-600">{{ round(($skor / 5) * 100) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 text-center text-gray-500 text-xs">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 bg-gray-50 rounded text-sm text-gray-600 text-center">
                        Gambar polygon pada map untuk melihat hasil analisis
                    </div>
                @endif

                <button id="analyzeBtn"
                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition disabled:bg-gray-400 mt-3"
                    disabled>
                    Analisis Lahan
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-4 border-t border-gray-200 bg-gray-50 text-center text-xs text-gray-400">
            © 2026 SIMBA — Kota Mataram
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Initialize map Kota Mataram
        const map = L.map('map').setView([-8.5833, 116.1167], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            draw: {
                polygon: true,
                rectangle: true,
                polyline: false,
                circle: false,
                circlemarker: false,
                marker: false
            },
            edit: { featureGroup: drawnItems }
        });
        map.addControl(drawControl);

        const parameterLayers = {};
        const layerList = document.getElementById('layerList');
        const analyzeBtn = document.getElementById('analyzeBtn');
        const toggleAllBtn = document.getElementById('toggleAllLayers');
        let drawnPolygon = null;

        const paramConfig = {
            rainfall:  { label: 'Curah Hujan',       weight: 0.30 },
            slope:     { label: 'Kemiringan Lereng',  weight: 0.15 },
            land_use:  { label: 'Penggunaan Lahan',   weight: 0.15 },
            soil_type: { label: 'Jenis Tanah',        weight: 0.15 },
            rivers:    { label: 'Jarak Sungai',       weight: 0.15 },
            elevation: { label: 'Elevasi',            weight: 0.10 }
        };

        const colorMap = {
            rainfall:  ['#d9f0a3', '#91cf60', '#1d9641', '#de2d26', '#8b0000'],
            slope:     ['#e6f3ff', '#99d9ff', '#4da6ff', '#0066ff', '#003d99'],
            land_use:  ['#fff9e6', '#ffe6b3', '#ffcc80', '#ff9933', '#cc6600'],
            soil_type: ['#f5f5dc', '#dcd6b8', '#c4b89a', '#8b7355', '#654321'],
            rivers:    ['#e0f2f1', '#80deea', '#4dd0e1', '#26c6da', '#00838f'],
            elevation: ['#f3e5f5', '#e1bee7', '#ce93d8', '#ba68c8', '#8e24aa']
        };

        // Fetch a single layer
        async function fetchLayer(paramType) {
            const response = await fetch(`/api/parameters/${paramType}/data`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const geoJson = await response.json();
            if (geoJson.error) throw new Error(geoJson.error);
            return geoJson;
        }

        // Build and register one layer on the map
        function registerLayer(paramType, geoJson) {
            if (!geoJson.features || geoJson.features.length === 0) return;

            const fg = L.featureGroup();
            const colors = colorMap[paramType] || colorMap.rainfall;

            L.geoJSON(geoJson, {
                style: (feature) => {
                    const score = parseInt(feature.properties.score) || 1;
                    const idx   = Math.min(Math.max(score - 1, 0), 4); // clamp 0-4
                    return {
                        color:       colors[idx],
                        fillColor:   colors[idx],
                        weight:      1,
                        opacity:     0.8,
                        fillOpacity: 0.45
                    };
                },
                onEachFeature: (feature, layer) => {
                    const cfg = paramConfig[paramType];
                    layer.bindPopup(
                        `<strong>${cfg.label}</strong><br/>Score: ${feature.properties.score}/5`
                    );
                }
            }).addTo(fg);

            fg.addTo(map);

            parameterLayers[paramType] = {
                group: fg,
                visible: true,
                config: paramConfig[paramType]
            };
        }

        // Render/refresh the layer panel list
        function renderLayerList() {
            layerList.innerHTML = '';

            if (Object.keys(parameterLayers).length === 0) {
                layerList.innerHTML = '<p class="text-xs text-gray-400 ">Tidak ada layer tersedia</p>';
                return;
            }

            // Preserve order defined in paramConfig
            for (const paramType of Object.keys(paramConfig)) {
                if (!parameterLayers[paramType]) continue;
                const layer = parameterLayers[paramType];
                const div = document.createElement('div');
                div.className = 'p-2 bg-gray-50 rounded flex items-center justify-between';
                div.innerHTML = `
                    <label class="flex items-center cursor-pointer gap-2">
                        <input type="checkbox" class="layer-toggle accent-green-600 w-4 h-4" data-param="${paramType}" ${layer.visible ? 'checked' : ''}>
                        <span class="text-sm text-gray-700">
                            ${layer.config.label}
                            <span class="text-black">(${(layer.config.weight * 100).toFixed(0)}%)</span>
                        </span>
                    </label>
                `;
                layerList.appendChild(div);
            }

            document.querySelectorAll('.layer-toggle').forEach(cb => {
                cb.addEventListener('change', (e) => {
                    const pt = e.target.dataset.param;
                    if (!parameterLayers[pt]) return;
                    if (e.target.checked) {
                        map.addLayer(parameterLayers[pt].group);
                        parameterLayers[pt].visible = true;
                    } else {
                        map.removeLayer(parameterLayers[pt].group);
                        parameterLayers[pt].visible = false;
                    }
                });
            });
        }

        // Load ALL layers in PARALLEL (fixes sequential stall with large datasets)
        async function loadParameterLayers() {
            const types = Object.keys(paramConfig);

            const results = await Promise.allSettled(
                types.map(pt => fetchLayer(pt))
            );

            results.forEach((result, i) => {
                const pt = types[i];
                if (result.status === 'fulfilled') {
                    try {
                        registerLayer(pt, result.value);
                    } catch (err) {
                        console.error(`Error rendering ${pt}:`, err);
                    }
                } else {
                    console.warn(`Layer ${pt} gagal dimuat:`, result.reason.message);
                }
            });

            renderLayerList();
        }

        // Drawing events
        map.on('draw:created', function (e) {
            drawnItems.clearLayers();
            drawnItems.addLayer(e.layer);
            drawnPolygon = e.layer.toGeoJSON();
            analyzeBtn.disabled = false;
        });

        map.on('draw:edited', function (e) {
            e.layers.eachLayer(layer => {
                drawnPolygon = layer.toGeoJSON();
            });
        });

        map.on('draw:deleted', function () {
            drawnPolygon = null;
            analyzeBtn.disabled = true;
        });

        // Toggle all layers
        toggleAllBtn.addEventListener('click', () => {
            const allVisible = Object.values(parameterLayers).every(l => l.visible);
            document.querySelectorAll('.layer-toggle').forEach(cb => {
                cb.checked = !allVisible;
                cb.dispatchEvent(new Event('change'));
            });
            toggleAllBtn.textContent = allVisible ? 'Tampilkan Semua Layer' : 'Sembunyikan Semua Layer';
        });

        // Analyze button
        analyzeBtn.addEventListener('click', () => {
            if (!drawnPolygon) return;

            const selectedParams = Array.from(document.querySelectorAll('.layer-toggle:checked'))
                .map(cb => cb.dataset.param);

            if (selectedParams.length === 0) {
                alert('Pilih minimal satu parameter untuk analisis!');
                return;
            }

            @this.call('analisisLahan', JSON.stringify(drawnPolygon.geometry), selectedParams);
        });

        loadParameterLayers();
    });
</script>