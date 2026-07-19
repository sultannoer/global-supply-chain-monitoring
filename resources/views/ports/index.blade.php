@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 bg-dark text-white min-vh-100 overflow-x-hidden" style="font-family: 'Segoe UI', Roboto, sans-serif;">
   
    <div id="logixchain-radar-data" style="display: none;" 
         data-ports='@json($enrichedPorts)' 
         data-vessels='@json($enrichedVessels)' 
         data-storms='@json($enrichedStorms ?? [])'>
    </div>

    <div class="row g-0 min-vh-100">
        <div class="col-lg-2 bg-black bg-opacity-50 border-end border-secondary border-opacity-25 d-flex flex-column justify-content-between p-3" style="min-height: 100vh;">
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
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('cargo.create') }}"><i class="bi bi-box-seam"></i> Input Cargo</a></li>
                    <li class="nav-item"><a id="sidebar-active-tracking" class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="#"><i class="bi bi-cursor-fill text-success"></i> Active Tracking</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('cargo.history') }}"><i class="bi bi-clock-history text-danger"></i> Log Riwayat</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ url('/api/live-metrics') }}" target="_blank"><i class="bi bi-anchor"></i> Ports Master</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="#"><i class="bi bi-gear-fill"></i> Settings</a></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7 d-flex flex-column h-100" style="min-height: 100vh;">
            <div class="bg-black bg-opacity-25 border-bottom border-secondary border-opacity-25 d-flex justify-content-between align-items-center px-4 py-3">
                <div>
                    <h5 class="mb-0 fw-bold">Global Supply Chain Radar</h5>
                    <small class="text-white-50 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Control Tower Terminal Operations</small>
                </div>
                <div class="d-flex gap-3 align-items-center"><span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-2 px-3 rounded-pill small fw-semibold"><span class="spinner-grow spinner-grow-sm me-1 text-danger align-middle"></span> Live Vessel Sync Active</span></div>
            </div>
            <div class="flex-grow-1 position-relative" style="height: calc(100vh - 72px); width: 100%;"><div id="fullScreenDarkMap" style="height: 100%; width: 100%; background-color: #0f1115;"></div></div>
        </div>

        <div class="col-lg-3 bg-black bg-opacity-40 border-start border-secondary border-opacity-25 d-flex flex-column p-4" style="min-height: 100vh; max-height: 100vh; overflow-y: auto;">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-uppercase small fw-bold tracking-wider text-danger mb-0"><i class="bi bi-rss-fill text-danger"></i> Live Alert Feed</h6>
                    <small class="text-muted" style="font-size: 11px;">19 Jul 2026</small>
                </div>

                <div id="radarNotificationFeed" class="d-flex flex-column gap-3"></div>

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
<link class="a" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" crossorigin="anonymous">
<style>
    .hover-light:hover { background-color: rgba(255, 255, 255, 0.05); color: #ffffff !important; }
    .leaflet-popup-content-wrapper { background: #121824 !important; color: #ffffff !important; border: 1px solid #334155; border-radius: 6px !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important; }
    .leaflet-popup-content { margin: 0 !important; width: 340px !important; }
    .leaflet-popup-tip { background: #121824 !important; border: 1px solid #334155; }
    .leaflet-marker-icon { transition: opacity 0.25s ease, filter 0.25s ease; }
    .leaflet-marker-icon:hover { opacity: 0.35 !important; filter: grayscale(80%); }
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
        var vesselDataList = JSON.parse(dataContainer.getAttribute('data-vessels') || '[]');
        var stormDataList = JSON.parse(dataContainer.getAttribute('data-storms') || '[]');

        var liveVesselStatusMemory = {};

        map = L.map('fullScreenDarkMap', { zoomControl: false }).setView([12.0, 105.0], 4);
        L.control.zoom({ position: 'bottomleft' }).addTo(map);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; CartoDB' }).addTo(map);

        map.createPane('portPane');
        map.getPane('portPane').style.zIndex = '650';
        map.getPane('portPane').style.pointerEvents = 'none'; 

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
                        <i class="bi bi-radar d-block mb-1 fs-4"></i> Menyinkronkan pemindai transponder armada...
                    </div>`;
                return;
            }

            var htmlContent = '';
            for (var id in liveVesselStatusMemory) {
                var v = liveVesselStatusMemory[id];
                var stormClass = v.isThreatened 
                    ? "bg-danger bg-opacity-10 border-start border-3 border-danger mb-2 p-2.5 rounded animate__animated animate__flash"
                    : "bg-dark bg-opacity-50 border-start border-3 border-info mb-2 p-2.5 rounded";
                var stormTitleColor = v.isThreatened ? "text-danger" : "text-info";
                var stormIcon = v.isThreatened ? "⚠️" : "🌤️";

                var matchedPort = portDataList.find(p => p.name.trim().toLowerCase() === v.portName.trim().toLowerCase());
                var weatherDetailsHtml = `Menghubungkan sensor terminal ${v.portName}...`;
                
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
            
            var baseRate = parseFloat(currentRate) || 1.0;
            labelCurrency = labelCurrency.toUpperCase();

            var textSeed = 0;
            for (var i = 0; i < labelCurrency.length; i++) {
                textSeed += labelCurrency.charCodeAt(i) * (i + 1);
            }


            var wave1 = 0.990 + (Math.sin(textSeed + 1.1) * 0.012);
            var wave2 = 0.992 + (Math.cos(textSeed + 2.4) * 0.010);
            var wave3 = 1.002 + (Math.sin(textSeed + 3.7) * 0.015);
            var wave4 = 1.000; 
            currencyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['08:00', '12:00', '16:00', '20:00'],
                    datasets: [{
                        label: 'Kurs USD / ' + labelCurrency, 
                        data: [baseRate * wave1, baseRate * wave2, baseRate * wave3, baseRate * wave4],
                        borderColor: '#38bdf8', 
                        backgroundColor: 'rgba(56, 189, 248, 0.08)', 
                        borderWidth: 2, 
                        pointBackgroundColor: '#38bdf8', 
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
                        y: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: 'rgba(255,255,255,0.03)' } } 
                    }
                }
            });
        }
        if (typeof Chart !== 'undefined') initCurrencyChart('EUR', 0.92);

        portDataList.forEach(function(port) {
            var portMarker = L.circleMarker([parseFloat(port.lat), parseFloat(port.lng)], { radius: 6, fillColor: '#38bdf8', color: '#ffffff', weight: 1.5, fillOpacity: 0.9, pane: 'portPane' }).addTo(map);
            var portPopupContent = `
                <div style="width: 330px; font-family: 'Courier New', monospace; font-size: 11px; color: #e2e8f0; line-height: 1.4; overflow: hidden;">
                    <div style="padding: 8px 10px; background: #1e293b; border-bottom: 2px solid #0284c7; font-weight: bold; color: #38bdf8; display: flex; justify-content: space-between; align-items: center;"><span>⚓ PORT: ${port.name.toUpperCase()}</span><span style="font-size: 9px; background: #0369a1; padding: 2px 5px; border-radius: 3px; color: #fff;">ACTIVE</span></div>
                    <div style="padding: 6px 10px; border-bottom: 1px solid #1e293b; font-size: 10px; color: #94a3b8;">📍 Lat/Lng: ${parseFloat(port.lat).toFixed(4)}, ${parseFloat(port.lng).toFixed(4)}<br>Region: 🌍 ${port.country.toUpperCase()}</div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #1e293b; background: #1c1917;">🌤️ LIVE WEATHER: <span style="color:#4ade80;">${port.temp}°C</span> | Wind: ${port.wind} km/h | Risk: <span style="color:${port.wind > 18 ? '#f87171' : '#4ade80'}">${port.wind > 18 ? '⚠️ Medium' : 'Low'}</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #1e293b;">💰 FOREX IMPACT: Mata Uang ${port.currency} | <span style="color:#38bdf8; font-weight:bold;">1 USD = ${port.rate} ${port.currency}</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #1e293b;">📊 MACRO ECON: GDP ${port.gdp} | Inflation: <span style="color:#f87171;">${port.inflation}</span></div>
                    <div style="padding: 8px 10px; border-bottom: 1px solid #334155; background: #1e1b4b; font-size: 10px;">🚨 STATUS MAP: <span style="font-style: italic; color: #cbd5e1;">"${port.news}"</span></div>
                    <div style="padding: 8px; background: #0f172a; text-align: center;"><a href="/ports/${port.id}" style="display: block; width: 100%; padding: 6px 0; background: #0284c7; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 4px; text-align: center;">Buka Analitik Pelabuhan Complete →</a></div>
                </div>`;

            portMarker.bindPopup(portPopupContent).on('click', function() { initCurrencyChart(port.currency, port.rate); });
        });

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
                    /
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
            var fallbackMarker = L.marker([shipLat, shipLng], { icon: shipIcon }).addTo(map);
            var defaultPopupTemplate = `
                <div style="width: 340px; font-family: 'Courier New', monospace; font-size: 12px; color: #e2e8f0; line-height: 1.4;">
                    <div style="padding: 8px 10px; background: #1e293b; border-bottom: 2px solid #334155; font-weight: bold; color: #38bdf8;">🚢 CARGO VESSEL: OCEANIC-EXPLORER</div>
                    <div style="padding: 4px 10px; font-size: 11px; background: #0f172a; color: #94a3b8; border-bottom: 1px solid #334155;">📦 Resi: #VNG-2026-0982 | Status: <span style="color: #4ade80; font-weight: bold;">ON VOYAGE</span></div>
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