@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 bg-dark text-white min-vh-100 overflow-x-hidden dashboard-shell" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    <!-- CONTAINER ELEMENT DATA JANGKAR AMAN DARI BLOKIR BROWSER -->
    <div id="logixchain-radar-data" style="display: none;" 
         data-ports='@json($enrichedPorts)' 
         data-countries='@json($enrichedCountries ?? [])'
         data-vessels='@json($enrichedVessels)' 
         data-storms='@json($enrichedStorms ?? [])'>
    </div>

    <div class="row g-0 min-vh-100 dashboard-grid">
        <!-- SIDEBAR KIRI: NAVIGASI OPERASIONAL GLOBAL -->
        <div class="col-lg-2 bg-black bg-opacity-50 border-end border-secondary border-opacity-25 d-flex flex-column justify-content-between p-3 dashboard-nav" style="min-height: 100vh;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4 px-2">
                    <i class="bi bi-shield-shaded text-primary fs-3"></i>
                    <span class="fs-4 fw-bold tracking-wider text-uppercase text-white">LOGIXCHAIN</span>
                </div>
                <div class="d-flex align-items-center gap-3 bg-secondary bg-opacity-10 p-2 rounded mb-4 border border-secondary border-opacity-10">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white font-bold shadow" style="width: 38px; height: 38px;"><i class="bi bi-person-badge-fill"></i></div>
                    <div class="overflow-hidden">
                        <h6 class="mb-0 small fw-bold text-truncate">Admin Logistics</h6>
                        <small class="text-muted text-uppercase" style="font-size: 10px;">Global Controller</small>
                    </div>
                </div>
                <ul class="nav flex-column gap-1">
                    <li class="nav-item"><a class="nav-link active rounded bg-primary text-white d-flex align-items-center gap-3 px-3 py-2.5 small fw-semibold" href="{{ url('/') }}"><i class="bi bi-grid-1x2-fill"></i> Live Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('risk-scores.index') }}"><i class="bi bi-shield-exclamation text-warning"></i> Risk Score Engine</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('country-comparison.index') }}"><i class="bi bi-bar-chart text-info"></i> Country Comparison</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('watchlists.index') }}"><i class="bi bi-star-fill text-warning"></i> Favorite Monitoring</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('news-sentiment.index') }}"><i class="bi bi-newspaper text-info"></i> News Sentiment</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('trends.index') }}"><i class="bi bi-graph-up-arrow text-success"></i> Historical Trends</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('cargo.create') }}"><i class="bi bi-box-seam"></i> Input Cargo</a></li>
                    <li class="nav-item"><a id="sidebar-active-tracking" class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="#"><i class="bi bi-cursor-fill text-success"></i> Active Tracking</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('cargo.history') }}"><i class="bi bi-clock-history text-danger"></i> Log Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ url('/api/live-metrics') }}" target="_blank"><i class="bi bi-anchor"></i> Ports Master</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="#"><i class="bi bi-gear-fill"></i> Settings</a></li>
                </ul>
            </div>
        </div>

        <!-- PANEL TENGAH: MONITOR RADAR PETA UTAMA -->
        <div class="col-lg-7 d-flex flex-column h-100 dashboard-map-column" style="min-height: 100vh;">
            <div class="bg-black bg-opacity-25 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center gap-3 flex-wrap px-4 py-3 dashboard-map-header">
                <div>
                    <h5 class="mb-0 fw-bold">Global Supply Chain Radar</h5>
                    <small class="text-white-50 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Control Tower Terminal Operations</small>
                </div>
                <div class="dashboard-search position-relative flex-grow-1 order-lg-0 order-3">
                    <label for="mapSearchInput" class="visually-hidden">Cari negara atau pelabuhan</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary border-opacity-50 text-info"><i class="bi bi-search"></i></span>
                        <input id="mapSearchInput" type="search" class="form-control bg-dark text-white border-secondary border-opacity-50 shadow-none" placeholder="Cari negara atau pelabuhan..." autocomplete="off">
                        <button id="mapSearchClear" class="btn btn-outline-secondary border-secondary border-opacity-50" type="button" aria-label="Bersihkan pencarian"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div id="mapSearchResults" class="list-group position-absolute w-100 shadow-lg d-none" role="listbox"></div>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 px-3 rounded-pill small fw-semibold">
                        <span class="spinner-grow spinner-grow-sm me-1 text-danger align-middle"></span> Live Vessel Sync Active
                    </span>
                </div>
            </div>
            <div class="flex-grow-1 position-relative radar-map-shell" style="height: calc(100vh - 72px); width: 100%;">
                <div id="fullScreenDarkMap" style="height: 100%; width: 100%; background-color: #0f1115;"></div>
            </div>
        </div>

        <!-- SIDEBAR KANAN: LIVE AUTOMATED EARLY WARNING SYSTEM -->
        <div class="col-lg-3 bg-black bg-opacity-40 border-start border-secondary border-opacity-25 d-flex flex-column p-4 dashboard-insights" style="min-height: 100vh; max-height: 100vh; overflow-y: auto;">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase small fw-bold tracking-wider text-danger mb-0"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Automated Early Warning System</h6>
                    <small class="text-muted font-monospace" style="font-size: 11px;">{{ now()->format('d M Y') }}</small>
                </div>
                
                <!-- CONTAINER LIVE RISK ALERT INTEGRATION FROM DATABASE -->
                <div id="riskAlertLiveFeedBlock" class="d-flex flex-column gap-2 mb-3">
                    @if(isset($activeAlerts) && $activeAlerts->count() > 0)
                        @foreach($activeAlerts as $alert)
                            @php
                                $badgeColor = $alert->alert_level === 'CRITICAL' ? 'danger' : 'warning';
                                $iconType = $alert->risk_type === 'ECONOMIC' ? 'bi-cash-coin' : ($alert->risk_type === 'GEOPOLITICS' ? 'bi-globe-asia-australia' : 'bi-cloud-lightning-rain-fill');
                            @endphp
                            <div class="p-2.5 rounded border border-{{ $badgeColor }} border-opacity-20 bg-{{ $badgeColor }} bg-opacity-10 animate__animated animate__headShake">
                                <div class="d-flex justify-content-between align-items-center border-bottom border-light border-opacity-10 pb-1 mb-2" style="font-size: 10px;">
                                    <span class="fw-bold text-{{ $badgeColor }} text-uppercase"><i class="bi {{ $iconType }}"></i> {{ $alert->alert_level }} {{ $alert->risk_type }}</span>
                                    <small class="text-white-50 font-monospace">{{ $alert->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-0 small text-white-50 style-text-alert" style="font-size: 11px; line-height:1.4; text-align:justify;">
                                    {{ $alert->message }}
                                </p>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-success bg-opacity-10 border border-success border-opacity-20 p-3 rounded text-center text-success small">
                            <i class="bi bi-shield-check d-block mb-1 fs-4"></i> Satelit SCRM mengonfirmasi: Jalur pelayaran global dan stabilitas ekonomi kliring 100% aman.
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                    <h6 class="text-uppercase small fw-bold tracking-wider text-info mb-0"><i class="bi bi-rss-fill text-info"></i> Sensor Koordinat Pelayaran</h6>
                </div>
                
                <!-- CONTAINER UTAMA FEED TRANSPONDER KKOORDINAT -->
                <div id="radarNotificationFeed" class="d-flex flex-column gap-3"></div>

                <!-- DAFTAR KAPAL YANG SUDAH SAMPAI -->
                <div id="vesselArrivalContainerZone" class="mt-3">
                    @foreach($enrichedVessels as $v)
                        @if(($v['step'] ?? 0) >= 1500)
                        <div class="bg-success bg-opacity-10 border-start border-3 border-success p-3 rounded shadow-sm mb-2 animate__animated animate__fadeIn">
                            <div class="d-flex justify-content-between align-items-start mb-1"><span class="fw-bold text-success text-uppercase" style="font-size: 11px;">⚓ Arrival Report</span><small class="text-muted" style="font-size: 10px;">Selesai</small></div>
                            <p class="mb-0 small text-white-50">Kapal <strong>{{ $v['name'] }}</strong> telah bersandar dengan aman di <strong>{{ $v['dest_name'] }}</strong>.</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <div class="col-12 mt-auto border-top border-secondary border-opacity-25 pt-4">
                <div class="d-flex align-items-center justify-content-between mb-3"><h6 class="text-uppercase small fw-bold tracking-wider text-success mb-0"><i class="bi bi-graph-up-arrow"></i> Live Forex Tracker</h6></div>
                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary border-opacity-25" style="height: 200px; position: relative;"><canvas id="currencyTrendChart"></canvas></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" crossorigin="anonymous">
<style>
    .hover-light:hover { background-color: rgba(255, 255, 255, 0.05); color: #ffffff !important; }
    .leaflet-popup-content-wrapper { background: #121824 !important; color: #ffffff !important; border: 1px solid #334155; border-radius: 6px !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important; }
    .leaflet-popup-content { margin: 0 !important; width: min(340px, calc(100vw - 48px)) !important; }
    .leaflet-popup-tip { background: #121824 !important; border: 1px solid #334155; }
    .leaflet-marker-icon { transition: opacity 0.25s ease, filter 0.25s ease; }
    .leaflet-marker-icon:hover { opacity: 0.35 !important; filter: grayscale(80%); }
    .radar-map-shell { min-height: 480px; }
    .dashboard-search { max-width: 420px; z-index: 1100; }
    #mapSearchResults { top: calc(100% + 0.35rem); max-height: 260px; overflow-y: auto; border: 1px solid rgba(148, 163, 184, 0.35); border-radius: 0.45rem; }
    #mapSearchResults .list-group-item { background: #111827; border-color: rgba(148, 163, 184, 0.18); color: #e2e8f0; cursor: pointer; }
    #mapSearchResults .list-group-item:hover,
    #mapSearchResults .list-group-item:focus { background: #1e293b; color: #fff; }
    .port-count-label { background: transparent; border: 0; box-shadow: none; color: #fff; font-weight: 700; font-size: 10px; text-shadow: 0 1px 3px #020617; }

    @media (max-width: 991.98px) {
        .dashboard-grid { min-height: 0 !important; }
        .dashboard-map-column { order: 1; min-height: 0 !important; }
        .dashboard-nav { order: 2; min-height: auto !important; }
        .dashboard-insights { order: 3; min-height: auto !important; max-height: none !important; }
        .radar-map-shell { height: min(68dvh, 640px) !important; min-height: 440px; }
        .dashboard-nav .nav { flex-direction: row !important; flex-wrap: wrap; }
        .dashboard-nav .nav-item { flex: 1 1 150px; }
        .dashboard-nav > div:last-child { display: none; }
        .dashboard-search { order: 3; max-width: none; width: 100%; }
    }

    @media (max-width: 575.98px) {
        .dashboard-map-column > .bg-black { padding: 0.85rem 1rem !important; }
        .dashboard-map-column h5 { font-size: 1rem; }
        .dashboard-map-column .badge { font-size: 9px; padding: 0.4rem 0.55rem !important; }
        .dashboard-map-header { align-items: flex-start !important; }
        .radar-map-shell { height: 62dvh !important; min-height: 390px; }
        .dashboard-insights { padding: 1rem !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>
<script>
    window.addEventListener("load", function () {
        if (typeof L === 'undefined') return;
        var map, lastVesselMarker = null, shipLat = -2.34, shipLng = 108.56, currencyChart = null;
        
        var dataContainer = document.getElementById('logixchain-radar-data');
        var portDataList = JSON.parse(dataContainer.getAttribute('data-ports') || '[]');
        var countryDataList = JSON.parse(dataContainer.getAttribute('data-countries') || '[]');
        var vesselDataList = JSON.parse(dataContainer.getAttribute('data-vessels') || '[]');
        var stormDataList = JSON.parse(dataContainer.getAttribute('data-storms') || '[]');

        var liveVesselStatusMemory = {};

        // 🔒 FIX PETA BERULANG: Batasi layar peta agar tidak menggambar duplikat dunia ke samping
        var maxWorldBounds = L.latLngBounds(L.latLng(-85, -180), L.latLng(85, 180));

        map = L.map('fullScreenDarkMap', { 
            zoomControl: false,
            minZoom: 2,
            maxBounds: maxWorldBounds,
            maxBoundsViscosity: 1.0 
        }).setView([12.0, 105.0], 4);

        L.control.zoom({ position: 'bottomleft' }).addTo(map);

        // noWrap diaktifkan untuk mengunci peta agar tidak berulang tanpa batas ke samping kanan-kiri
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { 
            attribution: '&copy; CartoDB',
            noWrap: true,
            bounds: maxWorldBounds
        }).addTo(map);

        // Garis batas negara: satu dataset GeoJSON dunia, tanpa pewarnaan area
        // dan tanpa request REST Countries per negara.
        fetch('https://raw.githubusercontent.com/datasets/geo-countries/master/data/countries.geojson')
            .then(function (response) { if (!response.ok) throw new Error('boundary dataset unavailable'); return response.json(); })
            .then(function (geojson) {
                L.geoJSON(geojson, {
                    interactive: false,
                    style: { color: '#64748b', weight: 0.8, opacity: 0.7, fill: false }
                }).addTo(map);
            })
            .catch(function () { /* peta dan marker tetap berjalan jika dataset batas gagal dimuat */ });

        map.createPane('portPane');
        map.getPane('portPane').style.zIndex = '650';
        map.getPane('portPane').style.pointerEvents = 'auto';

        var portLayer = L.layerGroup().addTo(map);
        var portRenderFrame = null;
        var countryMarkersByCode = {};

        function scheduleMapResize() {
            window.requestAnimationFrame(function () { map.invalidateSize({ pan: false }); });
        }

        if (window.ResizeObserver) {
            new ResizeObserver(scheduleMapResize).observe(document.getElementById('fullScreenDarkMap'));
        }
        window.addEventListener('resize', scheduleMapResize);
        window.addEventListener('orientationchange', function () { setTimeout(scheduleMapResize, 180); });

        function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
            var R = 6371; 
            var dLat = (lat2 - lat1) * Math.PI / 180, dLon = (lon2 - lon1) * Math.PI / 180;
            var a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))); 
        }

        function renderRadarNotificationFeed() {
            var feedContainer = document.getElementById('radarNotificationFeed');
            if (!feedContainer) return;

            var activeVesselsCount = Object.keys(liveVesselStatusMemory).length;
            if (activeVesselsCount === 0) {
                feedContainer.innerHTML = `
                    <div class="bg-dark bg-opacity-40 border border-secondary border-opacity-15 p-3 rounded text-center text-muted small">
                        <i class="bi bi-radar d-block mb-1 fs-4"></i> Menyinkronkan pemindai transponder...
                    </div>`;
                return;
            }

            var htmlContent = '';
            for (var id in liveVesselStatusMemory) {
                var v = liveVesselStatusMemory[id];
                var stormClass = v.isThreatened 
                    ? "bg-danger bg-opacity-10 border-start border-3 border-danger mb-2 p-2.5 rounded animate__animated animate__pulse animate__infinite"
                    : "bg-dark bg-opacity-50 border-start border-3 border-info mb-2 p-2.5 rounded";
                var stormTitleColor = v.isThreatened ? "text-danger" : "text-info";
                var stormIcon = v.isThreatened ? "⚠️" : "🌤️";

                var matchedPort = portDataList.find(p => p.name.trim().toLowerCase() === v.portName.trim().toLowerCase());
                var weatherDetailsHtml = `Sensor regional terputus...`;
                
                if (matchedPort) {
                    var wRiskColor = matchedPort.wind > 18 ? '#ef4444' : '#4ade80';
                    var wRiskText = matchedPort.wind > 18 ? 'EXTREME WIND' : 'SAFE RECORD';
                    weatherDetailsHtml = `Dest: <strong>Port of ${matchedPort.name}</strong><br>
                                          Suhu: <span class="text-warning fw-bold">${matchedPort.temp}°C</span> | Angin: ${matchedPort.wind} km/h<br>
                                          Status: <span style="color: ${wRiskColor}; font-weight: bold; font-size:10px;">[${wRiskText}]</span>`;
                }

                htmlContent += `
                    <div class="p-2.5 rounded border border-secondary border-opacity-25 bg-black bg-opacity-30">
                        <div class="fw-bold tracking-wider text-white border-bottom border-secondary border-opacity-10 pb-1 mb-2 text-truncate" style="font-size: 11px;">
                            <i class="bi bi-compass text-primary me-1"></i> ${v.vesselName.toUpperCase()}
                        </div>
                        <div class="${stormClass}" style="font-size: 11px;">
                            <span class="fw-bold ${stormTitleColor}">${stormIcon} METEO ALERT</span><br>
                            ${v.isThreatened 
                                ? `Kritis! Terjebak di <strong>${v.stormDetails.name}</strong> (${Math.round(v.stormDetails.dist)} KM).` 
                                : `Jalur aman. Jarak terdekat ke badai: ${Math.round(v.stormDetails.dist)} KM.`
                            }
                        </div>
                        <div class="bg-dark bg-opacity-50 border-start border-3 border-warning p-2.5 rounded" style="font-size: 11px;">
                            <span class="fw-bold text-warning"><i class="bi bi-cloud-sun-fill"></i> PORT CLIMATE</span><br>
                            ${weatherDetailsHtml}
                        </div>
                    </div>
                `;
            }
            feedContainer.innerHTML = htmlContent;
        }

        function initCurrencyChart(labelCurrency, currentRate) {
            var ctx = document.getElementById('currencyTrendChart').getContext('2d');
            if (currencyChart) currencyChart.destroy();
            
            var baseRate = parseFloat(currentRate);
            labelCurrency = labelCurrency.toUpperCase();

            if (!Number.isFinite(baseRate)) {
                return;
            }

            currencyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['LIVE NOW'],
                    datasets: [{
                        label: 'Kurs USD / ' + labelCurrency, 
                        data: [baseRate],
                        borderColor: '#4ade80', 
                        backgroundColor: 'rgba(74, 222, 128, 0.1)', 
                        borderWidth: 2, 
                        pointBackgroundColor: '#4ade80',
                        pointBorderColor: '#ffffff',
                        pointRadius: 4,
                        fill: true, 
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { 
                            display: true, 
                            labels: { color: '#94a3b8', font: { size: 10, family: 'monospace' } } 
                        }
                    },
                    scales: { 
                        x: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: 'rgba(255,255,255,0.03)' } }, 
                        y: { 
                            ticks: { color: '#64748b', font: { size: 9 } }, 
                            grid: { color: 'rgba(255,255,255,0.03)' },
                            suggestedMin: baseRate * 0.99,
                            suggestedMax: baseRate * 1.01
                        } 
                    }
                }
            });
        }

        if (typeof Chart !== 'undefined') initCurrencyChart('USD', 1);

        function buildPortPopup(port) {
            return `
                <div style="width: 340px; font-family: 'Courier New', monospace; font-size: 12px; color: #e2e8f0; line-height: 1.4;">
                    <div style="padding: 8px 10px; background: #1e293b; border-bottom: 2px solid #0284c7; font-weight: bold; color: #38bdf8;">⚓ PORT LOGISTICS: ${port.name.toUpperCase()}</div>
                    <div style="padding: 4px 10px; font-size: 11px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-bottom: 1px solid #334155; text-align: center; font-weight: bold;">📡 RADAR STATUS: OPERATIONAL ACTIVE</div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">📍 LAT/LNG HUB: ${parseFloat(port.lat).toFixed(4)}, ${parseFloat(port.lng).toFixed(4)}<br>Region Hub: 🌍 <strong>${port.country.toUpperCase()}</strong></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #1c1917;"><span style="color:#38bdf8; font-weight:bold;">🌤️ CLIMATE MATRIX:</span> Temp <span style="color:#4ade80;">${port.temp ?? 'N/A'}°C</span> | Wind: ${port.wind ?? 'N/A'} km/h</div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">💰 MONETARY LINK: Valuta ${port.currency} | <span style="color:#4ade80; font-weight:bold;">1 USD = ${port.rate ?? 'N/A'} ${port.currency}</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #0f172a;">📊 WORLD BANK MACRO: GDP ${port.gdp} | Inflation: <span style="color:#ef4444;">${port.inflation}</span></div>
                    <div style="padding: 8px; background: #0f172a; text-align: center;"><a href="/ports/${port.id}" style="display: block; width: 100%; padding: 6px 0; background: #0284c7; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 4px; text-align: center;">Buka Analitik Pelabuhan Complete →</a></div>
                </div>`;
        }

        function renderVisiblePorts() {
            portLayer.clearLayers();
            var visibleBounds = map.getBounds().pad(0.15);

            // At world view, group nearby ports so users can still see port
            // coverage without creating thousands of individual markers.
            if (map.getZoom() < 5) {
                var bucketSize = map.getZoom() <= 2 ? 24 : (map.getZoom() === 3 ? 16 : 10);
                var buckets = {};

                portDataList.forEach(function (port) {
                    var lat = parseFloat(port.lat), lng = parseFloat(port.lng);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng) || !visibleBounds.contains([lat, lng])) return;

                    var key = Math.floor((lat + 90) / bucketSize) + ':' + Math.floor((lng + 180) / bucketSize);
                    if (!buckets[key]) buckets[key] = { lat: 0, lng: 0, count: 0, ports: [] };
                    buckets[key].lat += lat;
                    buckets[key].lng += lng;
                    buckets[key].count += 1;
                    if (buckets[key].ports.length < 4) buckets[key].ports.push(port.name);
                });

                Object.keys(buckets).forEach(function (key) {
                    var group = buckets[key];
                    var center = [group.lat / group.count, group.lng / group.count];
                    var radius = Math.min(20, 7 + Math.log(group.count + 1) * 3);
                    var marker = L.circleMarker(center, {
                        radius: radius,
                        fillColor: '#0ea5e9',
                        color: '#bae6fd',
                        weight: 2,
                        fillOpacity: 0.78,
                        pane: 'portPane'
                    }).addTo(portLayer);

                    marker.bindTooltip(String(group.count), { permanent: true, direction: 'center', className: 'port-count-label' });
                    marker.bindPopup(
                        '<div class="p-2 text-center"><strong class="text-info">⚓ ' + group.count + ' Port</strong><br><small>' + group.ports.map(escapeHtml).join('<br>') + (group.count > group.ports.length ? '<br>… dan lainnya' : '') + '</small><br><span class="text-white-50 small">Klik marker untuk memperbesar peta.</span></div>'
                    );
                    marker.on('click', function () { map.flyTo(center, 6, { duration: 0.7 }); });
                });
                return;
            }

            portDataList.forEach(function (port) {
                var lat = parseFloat(port.lat), lng = parseFloat(port.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng) || !visibleBounds.contains([lat, lng])) return;

                var portMarker = L.circleMarker([lat, lng], {
                    radius: 6, fillColor: '#38bdf8', color: '#ffffff', weight: 1.5,
                    fillOpacity: 0.9, pane: 'portPane'
                }).addTo(portLayer);
                portMarker.bindPopup(buildPortPopup(port), { maxWidth: 340 })
                    .on('click', function () {
                        initCurrencyChart(port.currency, port.rate);
                        if (port._liveLoaded || port._liveLoading) return;
                        port._liveLoading = true;
                        portMarker.setPopupContent('<div class="p-3 text-center text-info">Memuat data live pelabuhan...</div>');
                        fetch('/api/live/markers/ports/' + encodeURIComponent(port.id))
                            .then(function(response) { if (!response.ok) throw new Error('port live request failed'); return response.json(); })
                            .then(function(payload) {
                                var live = payload.data || {};
                                port.temp = live.temp ?? 'N/A';
                                port.wind = live.wind ?? 'N/A';
                                port.rain = live.rain ?? 'N/A';
                                port.rate = live.rate ?? 'N/A';
                                port.gdp = Number.isFinite(Number(live.gdp)) ? '$' + (Number(live.gdp) / 1e9).toFixed(1) + 'B' : 'N/A';
                                port.inflation = Number.isFinite(Number(live.inflation)) ? Number(live.inflation).toFixed(2) + '%' : 'N/A';
                                port._liveLoaded = true;
                                portMarker.setPopupContent(buildPortPopup(port));
                                initCurrencyChart(port.currency, port.rate);
                            })
                            .catch(function() { portMarker.setPopupContent('<div class="p-3 text-center text-warning">Data live tidak tersedia. Coba klik marker lagi.</div>'); })
                            .finally(function() { port._liveLoading = false; });
                    });
            });
        }

        function requestVisiblePortRender() {
            if (portRenderFrame) window.cancelAnimationFrame(portRenderFrame);
            portRenderFrame = window.requestAnimationFrame(renderVisiblePorts);
        }

        map.on('zoomend moveend', requestVisiblePortRender);
        requestVisiblePortRender();

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, function (character) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[character];
            });
        }

        function focusSearchResult(type, id) {
            if (type === 'country') {
                var country = countryDataList.find(function (item) { return item.id === id; });
                if (!country) return;
                map.flyTo([parseFloat(country.lat), parseFloat(country.lng)], Math.max(5, map.getZoom()), { duration: 0.7 });
                setTimeout(function () {
                    var marker = countryMarkersByCode[country.id];
                    if (marker) marker.openPopup();
                }, 750);
                return;
            }

            var port = portDataList.find(function (item) { return String(item.id) === String(id); });
            if (!port) return;
            map.flyTo([parseFloat(port.lat), parseFloat(port.lng)], Math.max(7, map.getZoom()), { duration: 0.7 });
            setTimeout(function () {
                L.popup({ maxWidth: 340 }).setLatLng([parseFloat(port.lat), parseFloat(port.lng)]).setContent(buildPortPopup(port)).openOn(map);
            }, 750);
        }

        (function initMapSearch() {
            var input = document.getElementById('mapSearchInput');
            var clearButton = document.getElementById('mapSearchClear');
            var results = document.getElementById('mapSearchResults');
            if (!input || !results) return;

            function hideResults() {
                results.innerHTML = '';
                results.classList.add('d-none');
            }

            function renderResults() {
                var term = input.value.trim().toLowerCase();
                if (term.length < 2) return hideResults();

                var countries = countryDataList.filter(function (country) {
                    return [country.name, country.code, country.region].some(function (value) {
                        return String(value ?? '').toLowerCase().includes(term);
                    });
                }).slice(0, 5);

                var ports = portDataList.filter(function (port) {
                    return [port.name, port.country, port.currency].some(function (value) {
                        return String(value ?? '').toLowerCase().includes(term);
                    });
                }).slice(0, 8);

                if (countries.length === 0 && ports.length === 0) {
                    results.innerHTML = '<div class="list-group-item small text-white-50">Tidak ada negara atau pelabuhan yang cocok.</div>';
                    results.classList.remove('d-none');
                    return;
                }

                var html = countries.map(function (country) {
                    return '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-map-search-type="country" data-map-search-id="' + escapeHtml(country.id) + '"><span><i class="bi bi-globe2 text-warning me-2"></i>' + escapeHtml(country.name) + '</span><small class="text-white-50">Negara</small></button>';
                }).join('');
                html += ports.map(function (port) {
                    return '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-map-search-type="port" data-map-search-id="' + escapeHtml(port.id) + '"><span class="text-truncate"><i class="bi bi-anchor text-info me-2"></i>' + escapeHtml(port.name) + '</span><small class="text-white-50 ms-2 text-nowrap">' + escapeHtml(port.country) + '</small></button>';
                }).join('');

                results.innerHTML = html;
                results.classList.remove('d-none');
            }

            input.addEventListener('input', renderResults);
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    input.value = '';
                    hideResults();
                    input.blur();
                }
            });
            clearButton.addEventListener('click', function () {
                input.value = '';
                hideResults();
                input.focus();
            });
            results.addEventListener('click', function (event) {
                var item = event.target.closest('[data-map-search-type]');
                if (!item) return;
                focusSearchResult(item.dataset.mapSearchType, item.dataset.mapSearchId);
                input.value = '';
                hideResults();
            });
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.dashboard-search')) hideResults();
            });
        })();

        // 🌍 RENDER PENANDA NEGARA (Pin Makro Emas Berlogo Bumi - Berbeda Kontras Dari Port Bulat)
        countryDataList.forEach(function(country) {
            var countryLat = parseFloat(country.lat);
            var countryLng = parseFloat(country.lng);

            var customCountryIcon = L.divIcon({
                html: `
                    <div style="position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <div class="animate__animated animate__ping animate__infinite animate__slower" style="position: absolute; width: 34px; height: 34px; background: rgba(251, 191, 36, 0.2); border-radius: 50%;"></div>
                        <div style="position: relative; width: 30px; height: 30px; background: #78350f; border: 2px solid #fbbf24; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.4);">
                            <i class="bi bi-globe-asia-australia text-warning" style="transform: rotate(45deg); font-size: 14px;"></i>
                        </div>
                    </div>`,
                iconSize: [40, 40],
                iconAnchor: [20, 30],
                className: 'bg-transparent text-center'
            });

            var countryMarker = L.marker([countryLat, countryLng], { icon: customCountryIcon }).addTo(map);
            countryMarkersByCode[country.id] = countryMarker;

            function buildCountryPopup(country) {
            var cCodeFixed = country.flag_code || null;

            // 🚀 TOMBOL BARU FIX: Menyematkan tautan analitik dinamis makro otonom negara di bagian paling bawah kontainer popup
            return `
                <div style="width: 340px; font-family: 'Courier New', monospace; font-size: 12px; color: #e2e8f0; line-height: 1.4;">
                    <div style="padding: 8px 10px; background: #78350f; border-bottom: 2px solid #fbbf24; font-weight: bold; color: #fbbf24; display: flex; justify-content: space-between; align-items: center;">
                        <span>🌍 SOVEREIGN STATE: ${country.name.toUpperCase()}</span>
                        ${cCodeFixed ? `<img src="https://flagcdn.com/w40/${cCodeFixed}.png" style="height: 14px; border-radius: 2px; object-fit: cover;" alt="Flag of ${country.name}">` : ''}
                    </div>
                    <div style="padding: 4px 10px; font-size: 11px; background: rgba(251, 191, 36, 0.1); color: #fbbf24; border-bottom: 1px solid #334155; text-align: center; font-weight: bold;">📊 INTELLIGENCE STATS: WORLD BANK SYNCED</div>
                    
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">
                        🌐 GEOPOLITICAL REGION: <strong>${country.region.toUpperCase()}</strong><br>
                        🗣️ DOMESTIC LANGUAGE  : <span style="color:#38bdf8;">${country.language}</span>
                    </div>
                    
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #1c1917;">
                        <span style="color:#fbbf24; font-weight:bold;">💰 CURRENCY & EXCHANGE RATE:</span><br>
                        Mata Uang Terikat: <strong>${country.currency}</strong><br>
                        Kurs Finansial   : <span style="color:#4ade80; font-weight:bold;">1 USD = ${country.rate} ${country.currency}</span>
                    </div>
                    
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #102a2f;">
                        <span style="color:#38bdf8; font-weight:bold;">COUNTRY WEATHER:</span><br>
                        Suhu: <span style="color:#4ade80;">${country.weather?.temp ?? 'N/A'}°C</span> | Angin: ${country.weather?.wind_speed ?? 'N/A'} km/h | Hujan: ${country.weather?.rain ?? 'N/A'} mm
                    </div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #0f172a;">
                        <span style="color:#94a3b8; font-weight:bold;">📊 MACRO DATA INDICATORS:</span><br>
                        • Volume PDB / GDP : <span style="color:#fff;">${country.gdp}</span><br>
                        • Tingkat Inflasi  : <span style="color:#ef4444; font-weight:bold;">${country.inflation}</span><br>
                        • Total Populasi   : <span style="color:#fff;">${country.population} Jiwa</span>
                    </div>

                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #111827; font-size: 11px;">
                        <span style="color:#4ade80; font-weight:bold;">🚢 GLOBAL TRADE BALANCE:</span><br>
                        • Total Volume Ekspor : <span style="color:#4ade80;">${country.export}</span><br>
                        • Total Volume Impor  : <span style="color:#38bdf8;">${country.import}</span>
                    </div>

                    <div style="padding: 8px; background: #0f172a; text-align: center;">
                        <a href="/countries/${country.code}" style="display: block; width: 100%; padding: 7px 0; background: #78350f; color: #fbbf24; border: 1px solid #fbbf24; text-decoration: none; font-weight: bold; border-radius: 4px; text-align: center; font-size: 11px;">
                            Buka Detail Makro Sovereign Negara →
                        </a>
                    </div>
                </div>`; }
            
            countryMarker.bindPopup(buildCountryPopup(country), { maxWidth: 360 }).on('popupopen', function() {
                if (country._liveLoaded || country._liveLoading) return;
                country._liveLoading = true;
                countryMarker.setPopupContent('<div class="p-3 text-center text-info">Memuat data live negara...</div>');
                fetch('/api/live/markers/countries/' + encodeURIComponent(country.id))
                    .then(function(response) { if (!response.ok) throw new Error('country live request failed'); return response.json(); })
                    .then(function(payload) {
                        var live = payload.data || {};
                        country.gdp = Number.isFinite(Number(live.gdp)) ? '$' + (Number(live.gdp) / 1e9).toFixed(1) + 'B' : 'N/A';
                        country.inflation = Number.isFinite(Number(live.inflation)) ? Number(live.inflation).toFixed(2) + '%' : 'N/A';
                        country.population = Number.isFinite(Number(live.population)) ? Number(live.population).toLocaleString() : 'N/A';
                        country.export = Number.isFinite(Number(live.export)) ? '$' + (Number(live.export) / 1e9).toFixed(1) + 'B' : 'N/A';
                        country.import = Number.isFinite(Number(live.import)) ? '$' + (Number(live.import) / 1e9).toFixed(1) + 'B' : 'N/A';
                        country.rate = live.rate ?? 'N/A';
                        country.weather = live.weather || null;
                        country._liveLoaded = true;
                        countryMarker.setPopupContent(buildCountryPopup(country));
                        initCurrencyChart(country.currency, country.rate);
                    })
                    .catch(function() { countryMarker.setPopupContent('<div class="p-3 text-center text-warning">Data live tidak tersedia. Coba klik marker lagi.</div>'); })
                    .finally(function() { country._liveLoading = false; });
            });
        });

        // 🚢 KONFIGURASI TRANSPONDER DAN SIMULASI PERGERAKAN KAPAL OPERASIONAL
        var shipIcon = L.divIcon({
            html: `
                <div style="position: relative; width: 70px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <div class="animate__animated animate__pulse animate__infinite animate__slower" style="position: absolute; width: 65px; height: 22px; background: rgba(0, 123, 255, 0.25); filter: blur(3px); border-radius: 40% 10px 10px 40%; z-index: 1;"></div>
                    <div style="position: relative; width: 60px; height: 16px; background: #1c2230; border: 1.5px solid #007bff; border-radius: 50% 4px 4px 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.5); z-index: 2; display: flex; align-items: center; justify-content: space-around; padding-left: 12px; padding-right: 4px;">
                        <div style="width: 8px; height: 8px; background: #dc3545; border-radius: 1px;"></div><div style="width: 8px; height: 8px; background: #0d6efd; border-radius: 1px;"></div><div style="width: 8px; height: 8px; background: #ffc107; border-radius: 1px;"></div><div style="width: 8px; height: 8px; background: #198754; border-radius: 1px;"></div><div style="width: 5px; height: 10px; background: #ffffff; border-radius: 1px; border: 1px solid #333;"></div>
                    </div>
                </div>`,
            iconSize: [70, 30], iconAnchor: [35, 15], className: 'bg-transparent text-center'
        });

        if (vesselDataList.length > 0) {
            vesselDataList.forEach(function(vessel) {
                function generateDynamicVesselPopup(cLat, cLng, currentStatus, isThreatened, currentLossText, currentAlertText) {
                    var statusColor = isThreatened ? '#ef4444' : '#4ade80', statusBg = isThreatened ? 'rgba(239, 68, 68, 0.15)' : 'rgba(74, 222, 128, 0.1)';
                    var baseValue = Number(vessel.currency_value), calculatedValue = isThreatened ? baseValue * 0.85 : baseValue;
                    
                    return `
                        <div style="width: 340px; font-family: 'Courier New', monospace; font-size: 12px; color: #e2e8f0; line-height: 1.4;">
                            <div style="padding: 8px 10px; background: #1e293b; border-bottom: 2px solid ${isThreatened ? '#ef4444' : '#334155'}; font-weight: bold; color: #38bdf8;">🚢 CARGO VESSEL: ${vessel.name}</div>
                            <div style="padding: 4px 10px; font-size: 11px; background: ${statusBg}; color: ${statusColor}; border-bottom: 1px solid #334155; text-align: center; font-weight: bold;">📡 RADAR STATUS: ${currentStatus}</div>
                            <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">📍 LOCATION: <span class="live-lat-${vessel.id}">${cLat.toFixed(4)}</span>, <span class="live-lng-${vessel.id}">${cLng.toFixed(4)}</span><br>Route: ${vessel.origin_name} ➡️ ${vessel.dest_name}</div>
                            <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #1c1917;"><span style="color:#ef4444; font-weight:bold;">🌤️ METEOROLOGY:</span> Temp ${isThreatened ? '24' : vessel.temp}°C | Wind ${isThreatened ? '29 m/s' : vessel.wind + ' m/s'}<br><span style="color:#ef4444;">${currentAlertText}</span></div>
                            <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">💰 CARGO VALUE: $${baseValue.toLocaleString()} USD | Adjust: $${Math.round(calculatedValue).toLocaleString()}<br>Risk: <span style="color:${isThreatened ? '#ef4444' : '#f87171'}; font-weight:bold;">${currentLossText}</span></div>
                            <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #0f172a;">📊 DEST ECON: GDP ${vessel.dest_gdp} | Inflation: ${vessel.dest_inflation}</div>
                            <div style="padding: 8px 10px; background: rgba(220, 53, 69, 0.05);"><button onclick="dismissVessel('${vessel.id}')" class="btn btn-sm btn-danger w-100 fw-bold text-uppercase" style="font-size:10px; border:0; padding:6px 0;"><i class="bi bi-trash3-fill me-1"></i> Dismiss From Radar</button></div>
                        </div>`;
                }

                var portAsalObj = portDataList.find(p => p.name.trim().toLowerCase() === vessel.origin_name.trim().toLowerCase());
                var portTujuanObj = portDataList.find(p => p.name.trim().toLowerCase() === vessel.dest_name.trim().toLowerCase());

                var origLat = portAsalObj ? parseFloat(portAsalObj.lat) : parseFloat(vessel.origin_lat || vessel.lat);
                var origLng = portAsalObj ? parseFloat(portAsalObj.lng) : parseFloat(vessel.origin_lng || vessel.lng);
                var targetLat = portTujuanObj ? parseFloat(portTujuanObj.lat) : parseFloat(vessel.dest_lat);
                var targetLng = portTujuanObj ? parseFloat(portTujuanObj.lng) : parseFloat(vessel.dest_lng);
                
                var step = parseInt(vessel.step ?? 0), totalSteps = 1500; 

                var visualRouteLine = L.polyline([[origLat, origLng], [targetLat, targetLng]], { color: '#38bdf8', weight: 2.5, opacity: 0.85, dashArray: '6, 8', lineJoin: 'round' }).addTo(map);

                var ratio = step / totalSteps;
                if (ratio > 1) ratio = 1;
                
                var currentLat = origLat + (targetLat - origLat) * ratio;
                var currentLng = origLng + (targetLng - origLng) * ratio;

                var marker = L.marker([currentLat, currentLng], { icon: shipIcon }).addTo(map);

                if (step < totalSteps) {
                    var checkThreat = false, nearStormName = '', mindist = 99999;
                    stormDataList.forEach(st => {
                        var d = calculateHaversineDistance(currentLat, currentLng, parseFloat(st.lat), parseFloat(st.lng));
                        if(d <= st.radius_km) { checkThreat = true; mindist = d; nearStormName = st.name; }
                        else if(d < mindist) { mindist = d; nearStormName = st.name; }
                    });
                    
                    liveVesselStatusMemory[vessel.id] = {
                        vesselName: vessel.name,
                        portName: vessel.dest_name,
                        isThreatened: checkThreat,
                        stormDetails: { name: nearStormName, dist: mindist }
                    };
                }

                if (step >= totalSteps) {
                    marker.bindPopup(`<div style="width: 280px; font-family: monospace; color: #4ade80; font-size: 11px; padding: 10px; background: #121824;">⚓ <strong>ARRIVAL REPORT</strong><br>Vessel: ${vessel.name}<br>Status: ✅ SUCCESSFULLY BERTHED<br>Terminal: ${vessel.dest_name}</div>`);
                } else {
                    marker.bindPopup(generateDynamicVesselPopup(currentLat, currentLng, 'ON VOYAGE', false, vessel.currency_loss, vessel.storm_alert)).on('click', function() {
                        initCurrencyChart(vessel.currency_code, vessel.exchange_rate);
                    });
                }

                if (step < totalSteps) {
                    var shipMovementEngine = setInterval(function() {
                        step += 2; 
                        var ratio = step / totalSteps;
                        if (ratio > 1) ratio = 1;

                        var pStart = map.latLngToLayerPoint(new L.LatLng(origLat, origLng));
                        var pEnd = map.latLngToLayerPoint(new L.LatLng(targetLat, targetLng));
                        
                        var pCurrentX = pStart.x + (pEnd.x - pStart.x) * ratio;
                        var pCurrentY = pStart.y + (pEnd.y - pStart.y) * ratio;
                        
                        var finalLatLng = map.layerPointToLatLng(L.point(pCurrentX, pCurrentY));
                        currentLat = finalLatLng.lat;
                        currentLng = finalLatLng.lng;
                        
                        marker.setLatLng([currentLat, currentLng]);

                        if (step >= totalSteps) {
                            clearInterval(shipMovementEngine);
                            delete liveVesselStatusMemory[vessel.id]; 
                            renderRadarNotificationFeed();

                            marker.setLatLng([targetLat, targetLng]);
                            marker.setPopupContent(`<div style="width: 280px; font-family: monospace; color: #4ade80; font-size: 11px; padding: 10px; background: #121824;">⚓ <strong>ARRIVAL REPORT</strong><br>Vessel: ${vessel.name}<br>Status: ✅ SUCCESSFULLY BERTHED<br>Terminal: ${vessel.dest_name}</div>`);
                            
                            var feed = document.getElementById('vesselArrivalContainerZone');
                            if (feed) {
                                var newAlert = document.createElement('div');
                                newAlert.className = 'bg-success bg-opacity-10 border-start border-3 border-success p-3 rounded shadow-sm mb-2';
                                newAlert.innerHTML = `
                                    <div class="d-flex justify-content-between align-items-start mb-1"><span class="fw-bold text-success text-uppercase" style="font-size: 11px;">⚓ Arrival Report</span><small class="text-muted" style="font-size: 10px;">Just Now</small></div>
                                    <p class="mb-0 small text-white-50">Kapal <strong>${vessel.name}</strong> telah bersandar dengan aman di <strong>${vessel.dest_name}</strong>.</p>
                                `;
                                feed.prepend(newAlert);
                            }
                            
                            fetch(`/cargo/vessel/${vessel.id}/update-coordinates`, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                                body: JSON.stringify({ live_lat: targetLat, live_lng: targetLng, step: totalSteps })
                            }).catch(err => console.error(err));
                            
                            return;
                        }

                        var statusString = 'ON VOYAGE', stormString = '🌤️ Safe Maritime Condition', lossString = vessel.currency_loss, insideDangerZone = false;
                        var closestStormName = '', closestStormDist = 99999;

                        for (var i = 0; i < stormDataList.length; i++) {
                            var storm = stormDataList[i];
                            var distanceToThisStorm = calculateHaversineDistance(currentLat, currentLng, parseFloat(storm.lat), parseFloat(storm.lng));
                            
                            if (distanceToThisStorm < closestStormDist) {
                                closestStormDist = distanceToThisStorm;
                                closestStormName = storm.name;
                            }

                            if (distanceToThisStorm <= storm.radius_km) {
                                statusString = '🚨 WARNING: CRITICAL STORM IMPACT';
                                stormString = `⚠️ ALERT: Terjebak di ${storm.name}`;
                                lossString = '⚠️ LOSS METRIC: Devisa Penalty Applied (-15%)';
                                insideDangerZone = true;
                            }
                        }

                        if (insideDangerZone) {
                            var pulseDiv = marker._icon.querySelector('.animate__pulse');
                            if (pulseDiv) { pulseDiv.style.background = 'rgba(239, 68, 68, 0.7)'; pulseDiv.style.filter = 'blur(2px)'; }
                        } else {
                            var pulseDiv = marker._icon.querySelector('.animate__pulse');
                            if (pulseDiv) pulseDiv.style.background = 'rgba(0, 123, 255, 0.25)';
                        }

                        liveVesselStatusMemory[vessel.id] = {
                            vesselName: vessel.name,
                            portName: vessel.dest_name,
                            isThreatened: insideDangerZone,
                            stormDetails: { name: closestStormName, dist: closestStormDist }
                        };

                        renderRadarNotificationFeed();

                        marker.setPopupContent(generateDynamicVesselPopup(currentLat, currentLng, statusString, insideDangerZone, lossString, stormString));
                        var latEl = document.querySelector(`.live-lat-${vessel.id}`), lngEl = document.querySelector(`.live-lng-${vessel.id}`);
                        if (latEl && lngEl) { latEl.innerText = currentLat.toFixed(4); lngEl.innerText = currentLng.toFixed(4); }
                        
                        fetch(`/cargo/vessel/${vessel.id}/update-coordinates`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ live_lat: currentLat, live_lng: currentLng, step: step })
                        }).catch(err => console.error(err));

                        shipLat = currentLat; shipLng = currentLng;
                    }, 5000);
                }
                lastVesselMarker = marker;
            });

            renderRadarNotificationFeed();

            map.flyTo([shipLat, shipLng], 4);
            setTimeout(function() { if (lastVesselMarker) lastVesselMarker.openPopup(); }, 1000);
        } else {
            // FALLBACK JIKA ANTRIAN KANBAN RADAR ARMADA SEDANG KOSONG
            var fallbackMarker = L.marker([shipLat, shipLng], { icon: shipIcon }).addTo(map);
            var defaultPopupTemplate = `
                <div style="width: 340px; font-family: 'Courier New', monospace; font-size: 12px; color: #e2e8f0; line-height: 1.4;">
                    <div style="padding: 8px 10px; background: #1e293b; border-bottom: 2px solid #334155; font-weight: bold; color: #38bdf8;">🚢 CARGO VESSEL: OCEANIC-EXPLORER</div>
                    <div style="padding: 4px 10px; font-size: 11px; background: #0f172a; color: #4ade80; border-bottom: 1px solid #334155;">📦 Resi: #VNG-2026-0982 | Status: <span style="font-weight: bold;">ON VOYAGE</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">📍 LOCATION: -2.34, 108.56 (Laut Jawa)<br>Destination: 🇳🇱 Rotterdam, Netherlands</div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #1c1917;">🌤️ WEATHER: 29°C | Rain: 12 mm | Wind: 22 m/s (HIGH)<br><span style="color: #f87171; font-weight: bold;">⚠️ Alert: High Storm Risk in this Coordinate</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155;">💰 FINANCES: Cost $50,000 | Impact: <span style="color: #f87171;">Loss (-1.2%)</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #0f172a;">📊 DEST ECON: GDP $1.1T | Inflation: 2.8%</div>
                </div>`;
            fallbackMarker.bindPopup(defaultPopupTemplate).openPopup();
            
            document.getElementById('radarNotificationFeed').innerHTML = `
                <div class="p-2.5 rounded border border-secondary border-opacity-25 bg-black bg-opacity-30">
                    <div class="fw-bold tracking-wider text-white border-bottom border-secondary border-opacity-10 pb-1 mb-2" style="font-size: 11px;">
                        <i class="bi bi-compass text-primary me-1"></i> SIMULASI: OCEANIC-EXPLORER
                    </div>
                    <div class="bg-dark bg-opacity-50 border-start border-3 border-info mb-2 p-2.5 rounded" style="font-size: 11px;">
                        <span class="fw-bold text-info">🌤️ METEO STATUS</span><br>
                        Jalur aman. Jarak terdekat ke Badai Selat Malaka: 1,140 KM.
                    </div>
                    <div class="bg-dark bg-opacity-50 border-start border-3 border-warning p-2.5 rounded" style="font-size: 11px;">
                        <span class="fw-bold text-warning"><i class="bi bi-cloud-sun-fill"></i> PORT CLIMATE</span><br>
                        Dest: <strong>Port of Rotterdam (NLD)</strong><br>
                        Suhu: <span class="text-warning fw-bold">17°C</span> | Angin: 14 km/h<br>
                        Status: <span class="text-success fw-bold">[NORMAL OPERATION]</span>
                    </div>
                </div>`;

            lastVesselMarker = fallbackMarker;
            L.polyline([[-6.1014, 106.8831], [-2.34, 108.56], [5.50, 95.89], [5.90, 80.20], [11.85, 51.25], [12.78, 43.26], [27.80, 33.90], [32.50, 32.40], [36.50, 12.00], [35.95, -5.50], [48.00, -5.00], [50.50, -0.50], [51.9488, 4.1430]], { color: '#28a745', weight: 2.5, opacity: 0.85, dashArray: '6, 8', lineJoin: 'round' }).addTo(map);
        }

        var trackingBtn = document.getElementById('sidebar-active-tracking');
        if (trackingBtn) {
            trackingBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if(map) { map.flyTo([shipLat, shipLng], 7); setTimeout(function() { if (lastVesselMarker) lastVesselMarker.openPopup(); }, 1200); }
            });
        }

        window.dismissVessel = function(vesselId) {
            if (confirm("Apakah Anda yakin ingin menghapus kapal ini dari radar operasional?")) {
                fetch(`/cargo/vessel/${vesselId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' } })
                .then(res => res.json()).then(data => { if (data.status === 'success') window.location.reload(); })
                .catch(err => console.error(err));
            }
        };
    });
</script>
@endpush
