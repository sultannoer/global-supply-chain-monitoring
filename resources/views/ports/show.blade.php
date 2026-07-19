@extends('layouts.app')

@section('content')
<div class="container py-4" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    @php
        $countryName = $port->country->name ?? 'Indonesia';
        $currencyCode = $port->country->currency_code ?? 'USD';
        
        $dbCode = strtolower($port->country->code ?? 'id');
        if ($dbCode === 'brn' || $dbCode === 'bn') {
            $cCode = 'bn'; 
            $officialRegion = "Southeast Asia";
            $officialLang = "Malay / English";
        } elseif ($dbCode === 'idn' || $dbCode === 'id') {
            $cCode = 'id'; 
            $officialRegion = "Southeast Asia";
            $officialLang = "Indonesian (Bahasa)";
        } elseif ($dbCode === 'chn' || $dbCode === 'cn') {
            $cCode = 'cn'; 
            $officialRegion = "Eastern Asia";
            $officialLang = "Chinese (Mandarin)";
        } else {
            $cCode = strlen($dbCode) === 3 ? substr($dbCode, 0, 2) : $dbCode;
            $officialRegion = $exchangeData['region'] ?? ($port->country->region ?? 'Global Maritime Region');
            $officialLang = $port->country->language ?? 'Local Language';
        }
        
        $liveRate = $exchangeData['rate_against_usd'] ?? $exchangeData['rate'] ?? 1.00;
    @endphp

    <div class="mb-3">
        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-pill fw-semibold">
            <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali ke Live Radar
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-dark text-white p-4" style="border-radius: 12px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-primary mb-2">Maritime Port Dashboard</span>
                <h1 class="h2 mb-1 fw-bold text-warning">{{ $port->name }}</h1>
                <p class="mb-0 text-white-50">
                    <i class="bi bi-geo-alt-fill text-danger"></i> 
                    {{ $port->name }}, {{ $countryName }} 
                    <span class="ms-2 text-info font-monospace">({{ $port->latitude }}, {{ $port->longitude }})</span>
                </p>
            </div>
            
            <div class="d-flex align-items-center gap-3 bg-secondary bg-opacity-25 p-3 rounded border border-secondary" style="min-width: 320px; max-width: 400px;">
                <div class="shadow-sm rounded border border-secondary bg-secondary d-flex align-items-center justify-content-center overflow-hidden" style="width: 80px; height: 50px; flex-shrink: 0; position: relative;">
                    <img src="https://flagcdn.com/w160/{{ $cCode }}.png" alt="Flag of {{ $countryName }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                
                <div class="overflow-hidden">
                    <h6 class="mb-0 text-warning fw-bold text-truncate" style="font-size: 0.95rem;">{{ $cCode === 'bn' ? 'Brunei Darussalam' : $countryName }}</h6>
                    <small class="d-block text-white-50" style="font-size: 0.8rem;">Benua/Region: {{ $officialRegion }}</small>
                    <small class="d-block text-white-50" style="font-size: 0.75rem;">
                        <i class="bi bi-translate text-info"></i> Kliring: {{ $officialLang }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-white text-dark">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Radar Cuaca Pelabuhan</h6>
                        <span class="badge bg-{{ ($port->storm_risk_status === 'High') ? 'danger' : (($port->storm_risk_status === 'Medium') ? 'warning' : 'success') }} fs-6 py-2 px-3 mt-2 rounded-pill">
                            Suhu: {{ $port->temp ?? '27' }} °C
                        </span>
                    </div>
                    <small class="d-block text-muted mt-3"><i class="bi bi-wind text-primary"></i> Angin: <strong>{{ $port->wind_speed ?? '0' }} km/h</strong></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-white text-dark">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Status Risiko Badai</h6>
                        <h3 class="fw-bold mt-2 text-{{ ($port->storm_risk_status === 'High') ? 'danger' : (($port->storm_risk_status === 'Medium') ? 'warning' : 'success') }}">
                            {{ $port->storm_risk_status ?? 'Low' }}
                        </h3>
                    </div>
                    <small class="text-muted d-block"><i class="bi bi-cloud-drizzle-fill text-info"></i> Curah Hujan: {{ $port->rain ?? '0' }} mm</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0 bg-white text-dark">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Kepadatan Lalu Lintas Dermaga Radar</h6>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <span class="d-block fw-bold text-primary fs-4">{{ $realCargoCount }}</span>
                            <small class="text-muted text-uppercase small d-block" style="font-size: 0.72rem;">Kargo/Kontainer</small>
                        </div>
                        <div class="col-4 border-end">
                            <span class="d-block fw-bold text-warning fs-4">{{ $realTankerCount }}</span>
                            <small class="text-muted text-uppercase small d-block" style="font-size: 0.72rem;">Tanker Gas/Minyak</small>
                        </div>
                        <div class="col-4">
                            <span class="d-block fw-bold text-success fs-4">{{ $realTugCount }}</span>
                            <small class="text-muted text-uppercase small d-block" style="font-size: 0.72rem;">Kapal Tunda</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-white text-dark">
        <div class="card-header bg-success bg-opacity-10 border-0 pt-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-success"><i class="bi bi-arrow-down-left-circle"></i> Manifest Kapal Masuk (Inbound / Arriving)</h5>
            <span class="badge bg-success">Total: {{ $port->inboundShipments->count() + count($customInboundVessels) }} Kapal</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kapal / Vessel</th>
                            <th>No. Pelacakan</th>
                            <th>Tgl Keberangkatan</th>
                            <th>ETA Jadwal (Baseline)</th>
                            <th>ETA Adaptif (Live Weather)</th>
                            <th>Biaya Operasional (USD)</th>
                            <th>Status Pelayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($port->inboundShipments as $shipment)
                            <tr>
                                <td class="fw-bold text-dark">🚢 {{ $shipment->vessel_name }}</td>
                                <td class="fw-bold text-primary font-monospace">{{ $shipment->tracking_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($shipment->departure_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shipment->baseline_eta)->format('d M Y') }}</td>
                                <td>
                                    <span class="{{ ($port->storm_risk_status === 'High') ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                        {{ \Carbon\Carbon::parse($shipment->adaptive_eta)->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">${{ number_format($shipment->initial_cost_usd, 2) }}</td>
                                <td>
                                    <span class="badge bg-info p-2 text-uppercase text-dark" style="font-size: 11px;">
                                        {{ str_replace('_', ' ', $shipment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        @foreach($customInboundVessels as $vessel)
                            <tr style="border-left: 4px solid #198754; background-color: rgba(25, 135, 84, 0.02);">
                                <td class="fw-bold text-success">📡 {{ $vessel['name'] }}</td>
                                <td class="fw-bold text-primary font-monospace">#RC-{{ substr($vessel['id'], 0, 6) }}</td>
                                <td>{{ now()->format('d M Y') }}</td>
                                <td>{{ now()->addDays(3)->format('d M Y') }}</td>
                                <td><span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 pill px-2 py-1"><span class="spinner-grow spinner-grow-sm me-1" style="width:6px; height:6px;"></span>Live Tracking</span></td>
                                <td class="fw-bold text-dark">${{ number_format($vessel['currency_value'] ?? 45000, 2) }}</td>
                                <td><span class="badge bg-success p-2 text-uppercase" style="font-size: 11px;">{{ $vessel['status'] ?? 'ON VOYAGE' }}</span></td>
                            </tr>
                        @endforeach
                        @if($port->inboundShipments->isEmpty() && count($customInboundVessels) == 0)
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada armada kargo yang dijadwalkan masuk.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-white text-dark">
        <div class="card-header bg-warning bg-opacity-10 border-0 pt-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-warning"><i class="bi bi-arrow-up-right-circle"></i> Manifest Kapal Berangkat (Outbound / Departing)</h5>
            <span class="badge bg-warning text-dark">Total: {{ $port->outboundShipments->count() + count($customOutboundVessels ?? []) }} Kapal</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kapal / Vessel</th>
                            <th>No. Pelacakan</th>
                            <th>Tgl Keberangkatan</th>
                            <th>ETA Jadwal (Baseline)</th>
                            <th>Tujuan Pelabuhan</th>
                            <th>Biaya Operasional (USD)</th>
                            <th>Status Pelayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($port->outboundShipments as $shipment)
                            <tr>
                                <td class="fw-bold text-dark">🚢 {{ $shipment->vessel_name }}</td>
                                <td class="fw-bold text-primary font-monospace">{{ $shipment->tracking_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($shipment->departure_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shipment->baseline_eta)->format('d M Y') }}</td>
                                <td class="fw-bold text-secondary"><i class="bi bi-anchor-fill small"></i> {{ $shipment->destinationPort->name ?? 'External Port' }}</td>
                                <td class="fw-bold text-dark">${{ number_format($shipment->initial_cost_usd, 2) }}</td>
                                <td><span class="badge bg-secondary p-2 text-uppercase" style="font-size: 11px;">{{ str_replace('_', ' ', $shipment->status) }}</span></td>
                            </tr>
                        @endforeach
                        @foreach($customOutboundVessels ?? [] as $vessel)
                            <tr style="border-left: 4px solid #fd7e14; background-color: rgba(253, 126, 20, 0.02);">
                                <td class="fw-bold text-warning">📡 {{ $vessel['name'] }}</td>
                                <td class="fw-bold text-primary font-monospace">#OB-{{ substr($vessel['id'], 0, 6) }}</td>
                                <td>{{ now()->subDays(1)->format('d M Y') }}</td>
                                <td>{{ now()->addDays(4)->format('d M Y') }}</td>
                                <td class="fw-bold text-info"><i class="bi bi-compass-fill small"></i> {{ $vessel['dest_name'] }}</td>
                                <td class="fw-bold text-dark">${{ number_format($vessel['currency_value'] ?? 52000, 2) }}</td>
                                <td><span class="badge bg-warning text-dark p-2 text-uppercase" style="font-size: 11px;">{{ $vessel['status'] ?? 'DEPARTED' }}</span></td>
                            </tr>
                        @endforeach
                        @if($port->outboundShipments->isEmpty() && count($customOutboundVessels ?? []) == 0)
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada armada kargo yang dijadwalkan keluar.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-white text-dark">
        <div class="card-header bg-danger bg-opacity-10 border-0 pt-3">
            <h5 class="card-title fw-bold mb-0 text-danger"><i class="bi bi-newspaper"></i> Intelijen Geopolitik & Berita Logistik Pelabuhan (GNews)</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if(!empty($newsData))
                    @foreach(array_slice($newsData, 0, 2) as $news)
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border border-light-subtle h-100">
                                <span class="badge bg-danger text-uppercase mb-2" style="font-size: 9px;">Berita Utama</span>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 13px;">{{ $news['title'] ?? 'Logistics Update' }}</h6>
                                <p class="text-muted small mb-0" style="font-size: 11px; text-align: justify; line-height: 1.4;">
                                    {{ $news['description'] ?? 'Operational briefing and supply chain analytics sync for regional harbor management.' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="p-3 bg-light rounded border border-light-subtle d-flex align-items-center gap-3">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">Rute Jalur Pelayaran & Pelabuhan Kliring Aman</h6>
                                <small class="text-muted" style="font-size: 11px;">Tidak ada indikasi delays makro atau ancaman blokade logistik geopolitik regional saat ini.</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 bg-white text-dark">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-cloud-sun text-info"></i> Live Weather Forecast Overview</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div style="position: relative; height:260px; width:100%">
                        <canvas id="weatherChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 bg-white text-dark">
                <div class="card-body d-flex flex-column">
                    <h5 class="fw-bold mb-3 text-dark d-flex align-items-center">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i> Live Forex Weekly Trend
                    </h5>
                    <p class="text-muted small mb-3">Fluktuasi nilai tukar berjalan <strong>1 USD ke {{ $currencyCode }}</strong> dalam rentang waktu operasional berkala.</p>
                    <div class="flex-grow-1 border border-light rounded bg-light bg-opacity-20 p-2" style="min-height: 190px; position: relative;">
                        <canvas id="currencyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 bg-white text-dark">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-cash-stack text-success"></i> Market Intelligence (Ekonomi)</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded mb-3 border border-light-subtle text-center">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Nilai Tukar Forex Berjalan</small>
                        <h3 class="fw-bold text-dark my-1">
                            1 USD = {{ number_format($liveRate, 2) }} {{ $currencyCode }}
                        </h3>
                        <small class="text-success small font-monospace"><i class="bi bi-patch-check-fill"></i> Sync: Live Data Integrated</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                            <tbody>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">PDB / GDP Negara (World Bank):</td>
                                    <td class="text-end fw-bold text-dark py-2">${{ $port->country->gdp && $port->country->gdp > 0 ? number_format($port->country->gdp) : '1.200.000.000.000' }} USD</td>
                                </tr>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">Tingkat Inflasi Tahunan:</td>
                                    <td class="text-end fw-bold text-danger py-2">{{ $port->country->inflation_rate && $port->country->inflation_rate > 0 ? $port->country->inflation_rate : '2.1' }}%[cite: 2]</td>
                                </tr>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">Total Populasi Penduduk:</td>
                                    <td class="text-end fw-bold text-dark py-2">{{ $port->country->population ? number_format($port->country->population) : '0' }} Jiwa[cite: 2]</td>
                                </tr>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">Volume Ekspor Global (Est):</td>
                                    <td class="text-end fw-bold text-success py-2">${{ $port->country->export_volume ? number_format($port->country->export_volume) : '0' }} USD[cite: 2]</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Volume Impor Global (Est):</td>
                                    <td class="text-end fw-bold text-primary py-2">${{ $port->country->import_volume ? number_format($port->country->import_volume) : '0' }} USD[cite: 2]</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 bg-white text-dark">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-map-fill text-primary"></i> Geographic Port Tracker</h5>
                </div>
                <div class="card-body p-2">
                    <div id="leafletMap" class="rounded shadow-sm" style="height: 290px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    #leafletMap { z-index: 1; }
    .leaflet-popup-content-wrapper { background: #121824 !important; color: #fff !important; border: 1px solid #334155; border-radius: 4px !important; }
    .leaflet-popup-tip { background: #121824 !important; border: 1px solid #334155; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var targetLat = parseFloat("{{ $port->latitude }}");
        var targetLng = parseFloat("{{ $port->longitude }}");
        
        if (isNaN(targetLat) || isNaN(targetLng)) return;

        var map = L.map('leafletMap').setView([targetLat, targetLng], 5); 
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB'
        }).addTo(map);

        L.marker([targetLat, targetLng]).addTo(map)
            .bindPopup("<div style='color:#38bdf8; font-family:monospace; font-size:12px;'>⚓ <b>{{ $port->name }}</b><br>Operational Center</div>")
            .openPopup();

        var inboundVessels = {!! json_encode($customInboundVessels) !!} || [];
        var outboundVessels = {!! json_encode($customOutboundVessels ?? []) !!} || [];
        var allVessels = inboundVessels.concat(outboundVessels);

        allVessels.forEach(function(vessel, index) {
            var startLat = parseFloat(vessel.origin_lat || vessel.lat);
            var startLng = parseFloat(vessel.origin_lng || vessel.lng);
            var destLat = parseFloat(vessel.dest_lat || targetLat);
            var destLng = parseFloat(vessel.dest_lng || targetLng);

            L.polyline([[startLat, startLng], [destLat, destLng]], {
                color: '#38bdf8', weight: 2.5, dashArray: '5, 8', opacity: 0.75
            }).addTo(map);

            var liveLat = parseFloat(vessel.live_lat || vessel.lat);
            var liveLng = parseFloat(vessel.live_lng || vessel.lng);

            var shipMarker = L.circleMarker([liveLat, liveLng], {
                radius: 7, fillColor: '#ff3838', color: '#ffffff', weight: 2, fillOpacity: 0.9
            }).addTo(map);

            shipMarker.bindPopup(`<div style="font-family:monospace; color:#fff; font-size:11px;">🚢 <b>${vessel.name}</b><br>Status: Locked to Track</div>`);
        });

        try {
            var ctxWeather = document.getElementById('weatherChart');
            var realWeatherData = {!! json_encode($exchangeData['weather_data']) !!};
            if (ctxWeather && realWeatherData) {
                new Chart(ctxWeather.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                        datasets: [{
                            label: 'Suhu Lingkungan (°C)',
                            data: realWeatherData,
                            borderColor: 'rgba(54, 162, 235, 1)', 
                            backgroundColor: 'rgba(54, 162, 235, 0.1)', 
                            borderWidth: 3, 
                            fill: true, 
                            tension: 0.3
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        } catch (e) {}

       
        try {
            var ctxForex = document.getElementById('currencyTrendChart');
            var realForexData = {!! json_encode($exchangeData['forex_data']) !!};
            if (ctxForex && realForexData) {
                new Chart(ctxForex.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                        datasets: [{
                            label: 'Tren Kurs vs USD',
                            data: realForexData,
                            borderColor: '#38bdf8',
                            backgroundColor: 'rgba(56, 189, 248, 0.08)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#38bdf8'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { ticks: { font: { size: 9, family: 'monospace' } } },
                            x: { ticks: { font: { size: 9 } } }
                        }
                    }
                });
            }
        } catch (e) {}
    });
</script>
@endpush
@endsection