<div class="simba-layout">
    {{-- ========== SIDEBAR ========== --}}
    <aside class="simba-sidebar glass">
        {{-- Logo --}}
        <div class="simba-logo">
            <div class="simba-logo-icon">🌊</div>
            <div class="simba-logo-text">
                <h1>SIMBA</h1>
                <span>Sistem Informasi Pemilihan Pembuatan BTN</span>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="simba-nav">
            <div class="simba-nav-label">Menu</div>
            <a href="#" class="active">
                <span class="nav-icon">🗺️</span> Peta Analisis
            </a>
            <a href="#">
                <span class="nav-icon">📊</span> Data Wilayah
            </a>
            <a href="#">
                <span class="nav-icon">📋</span> Riwayat Analisis
            </a>
            <a href="#">
                <span class="nav-icon">⚙️</span> Pengaturan
            </a>
        </nav>

        {{-- Legend --}}
        <div class="simba-legend">
            <div class="simba-legend-title">Legenda Risiko</div>
            <div class="simba-legend-item">
                <span class="simba-legend-dot" style="background: #ef4444;"></span>
                Risiko Tinggi (> 30%)
            </div>
            <div class="simba-legend-item">
                <span class="simba-legend-dot" style="background: #f59e0b;"></span>
                Risiko Sedang (15–30%)
            </div>
            <div class="simba-legend-item">
                <span class="simba-legend-dot" style="background: #10b981;"></span>
                Risiko Rendah (< 15%)
            </div>
        </div>

        {{-- Instructions --}}
        <div class="simba-instructions glass-light">
            <div class="simba-instructions-title">Cara Penggunaan</div>
            <div class="simba-step">
                <span class="simba-step-num">1</span>
                <span>Klik ikon <strong style="color:#67e8f9;">polygon</strong> di toolbar peta</span>
            </div>
            <div class="simba-step">
                <span class="simba-step-num">2</span>
                <span>Gambar area lahan yang ingin dianalisis</span>
            </div>
            <div class="simba-step">
                <span class="simba-step-num">3</span>
                <span>Klik titik terakhir dua kali untuk menyelesaikan</span>
            </div>
            <div class="simba-step">
                <span class="simba-step-num">4</span>
                <span>Hasil analisis SPK akan muncul secara otomatis</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="simba-sidebar-footer">
            © 2026 SIMBA — Kota Mataram
        </div>
    </aside>

    {{-- ========== MAIN AREA ========== --}}
    <div class="simba-main">
        {{-- Header --}}
        <header class="simba-header glass">
            <div class="simba-header-left">
                <h2>Analisis Risiko Banjir</h2>
                <p>📍 Kota Mataram, Nusa Tenggara Barat</p>
            </div>
            <div class="simba-header-right">
                <span class="simba-badge simba-badge-live">Real-time</span>
            </div>
        </header>

        {{-- Map + Overlay --}}
        <div class="simba-map-wrapper">
            <div id="map" wire:ignore></div>

            {{-- Analysis Result Overlay --}}
            @if($hasilAnalisis)
                @php
                    $isDanger = $hasilAnalisis['persentase_banjir'] > 30;
                    $modeClass = $isDanger ? 'danger' : 'safe';
                @endphp
                <div class="simba-analysis-overlay glass">
                    {{-- Header --}}
                    <div class="simba-analysis-header">
                        <div class="simba-analysis-icon {{ $modeClass }}">
                            {{ $isDanger ? '🚨' : '✅' }}
                        </div>
                        <div>
                            <div class="simba-analysis-status {{ $modeClass }}">
                                {{ $hasilAnalisis['status'] }}
                            </div>
                            <div class="simba-analysis-subtitle">Hasil Analisis SPK Lahan</div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="simba-stats-grid">
                        <div class="simba-stat-card glass-light">
                            <div class="simba-stat-label">📏 Luas Total</div>
                            <div class="simba-stat-value">
                                {{ number_format($hasilAnalisis['luas_total'], 0, ',', '.') }}
                                <span class="simba-stat-unit">m²</span>
                            </div>
                        </div>
                        <div class="simba-stat-card glass-light">
                            <div class="simba-stat-label">✅ Area Aman</div>
                            <div class="simba-stat-value">
                                {{ number_format($hasilAnalisis['luas_aman'], 0, ',', '.') }}
                                <span class="simba-stat-unit">m²</span>
                            </div>
                        </div>
                        <div class="simba-stat-card glass-light">
                            <div class="simba-stat-label">🌊 Terendam Banjir</div>
                            <div class="simba-stat-value">
                                {{ number_format($hasilAnalisis['luas_banjir'], 0, ',', '.') }}
                                <span class="simba-stat-unit">m²</span>
                            </div>
                        </div>
                        <div class="simba-stat-card glass-light">
                            <div class="simba-stat-label">⚠️ Persentase Bahaya</div>
                            <div class="simba-stat-value {{ $modeClass }}" style="color: {{ $isDanger ? '#f87171' : '#34d399' }};">
                                {{ $hasilAnalisis['persentase_banjir'] }}%
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="simba-progress-wrapper">
                            <div class="simba-progress-header">
                                <span class="simba-progress-label">Tingkat Risiko Banjir</span>
                                <span class="simba-progress-value {{ $modeClass }}">{{ $hasilAnalisis['persentase_banjir'] }}%</span>
                            </div>
                            <div class="simba-progress-bar">
                                <div class="simba-progress-fill {{ $modeClass }}" style="width: {{ min($hasilAnalisis['persentase_banjir'], 100) }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ========== MAP SCRIPT ========== --}}
<script>
    document.addEventListener('livewire:initialized', () => {
        // Inisialisasi peta
        var map = L.map('map', {
            zoomControl: true
        }).setView([-8.5833, 116.1167], 13);

        // Dark-mode tile layer (CartoDB Dark Matter)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Drawing tools
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        var drawControl = new L.Control.Draw({
            draw: {
                polygon: {
                    shapeOptions: {
                        color: '#22d3ee',
                        weight: 2,
                        fillColor: '#06b6d4',
                        fillOpacity: 0.25
                    }
                },
                polyline: false,
                rectangle: false,
                circle: false,
                circlemarker: false,
                marker: false
            },
            edit: {
                featureGroup: drawnItems
            }
        });
        map.addControl(drawControl);

        // Event: polygon selesai digambar
        map.on(L.Draw.Event.CREATED, function (e) {
            var layer = e.layer;

            // Styling polygon yang sudah digambar
            layer.setStyle({
                color: '#22d3ee',
                weight: 2,
                fillColor: '#06b6d4',
                fillOpacity: 0.2
            });

            drawnItems.addLayer(layer);

            var dataGeoJSON = layer.toGeoJSON();
            var geomString = JSON.stringify(dataGeoJSON.geometry);

            // Kirim ke Livewire
            @this.call('analisisLahan', geomString);
        });
    });
</script>