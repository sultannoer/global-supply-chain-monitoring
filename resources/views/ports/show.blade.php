@extends('layouts.app')

@section('content')
<style>
    .port-detail-theme { min-height:100vh; background:#0b0f19; color:#e2e8f0; }
    .port-detail-theme .card { background:#1e293b !important; color:#e2e8f0 !important; border:1px solid #334155 !important; }
    .port-detail-theme .bg-white, .port-detail-theme .bg-light { background:#1e293b !important; color:#e2e8f0 !important; }
    .port-detail-theme .text-dark { color:#e2e8f0 !important; }
    .port-detail-theme .text-muted { color:#94a3b8 !important; }
    .port-detail-theme .border-light-subtle { border-color:#334155 !important; }
    .port-detail-theme .table { --bs-table-bg:transparent; --bs-table-color:#e2e8f0; --bs-table-border-color:#334155; }
    .port-detail-theme .table-light { --bs-table-bg:#172033; --bs-table-color:#cbd5e1; }
    .port-detail-theme .table-hover>tbody>tr:hover>* { color:#fff; background-color:rgba(56,189,248,.08); }
    .port-detail-theme .table td, .port-detail-theme .table th { border-color:#334155; }
    .port-detail-theme { position:relative; overflow:hidden; background-color:#080d18; background-image:linear-gradient(rgba(34,211,238,.022) 1px, transparent 1px),linear-gradient(90deg, rgba(34,211,238,.022) 1px, transparent 1px),radial-gradient(circle at 75% 10%, rgba(8,145,178,.13), transparent 34rem); background-size:42px 42px,42px 42px,100% 100%; }
    .port-detail-theme::after { content:""; position:absolute; inset:-100% 0; pointer-events:none; opacity:.055; background:repeating-linear-gradient(0deg, transparent 0, transparent 4px, rgba(255,255,255,.03) 5px); animation:tactical-scan 18s linear infinite; }
    .port-detail-theme > * { position:relative; z-index:1; }
    .port-detail-theme .card { border-radius:.65rem !important; background-image:linear-gradient(145deg, rgba(30,41,59,.98), rgba(15,23,42,.96)) !important; box-shadow:0 14px 35px rgba(0,0,0,.28), inset 0 1px 0 rgba(125,211,252,.08) !important; transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .port-detail-theme .card:hover { transform:translateY(-3px); border-color:rgba(34,211,238,.5) !important; box-shadow:0 18px 42px rgba(0,0,0,.38), 0 0 22px rgba(34,211,238,.08) !important; }
    .port-detail-theme .card-header { letter-spacing:.06em; background-image:linear-gradient(90deg, rgba(14,116,144,.18), transparent 70%) !important; }
    .port-detail-theme h1,.port-detail-theme h2,.port-detail-theme h3,.port-detail-theme h4,.port-detail-theme h5 { text-shadow:0 0 18px rgba(34,211,238,.12); }
    .port-detail-theme .badge { letter-spacing:.06em; }
    .port-detail-theme .tactical-strip { display:flex; flex-wrap:wrap; gap:.5rem .75rem; margin-top:1.15rem; padding:.65rem .8rem; border:1px solid rgba(34,211,238,.2); border-radius:.45rem; background:linear-gradient(90deg, rgba(8,47,73,.65), rgba(15,23,42,.5)); font:600 .68rem/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; }
    .port-detail-theme .tactical-strip span { display:inline-flex; align-items:center; gap:.35rem; }
    .port-detail-theme .tactical-strip strong { color:#67e8f9; }
    .port-detail-theme .port-identity-stack { min-width:320px; max-width:400px; width:100%; }
    .port-detail-theme .port-identity-stack .tactical-strip { margin-top:0; }
    .port-detail-theme .port-country-card { display:flex; align-items:center; gap:1rem; padding:1rem; border:1px solid rgba(148,163,184,.28); border-radius:.65rem; background:linear-gradient(135deg, rgba(51,65,85,.72), rgba(15,23,42,.86)); box-shadow:inset 0 1px 0 rgba(255,255,255,.06); }
    .port-detail-theme .port-country-card img { width:92px; height:58px; flex:0 0 92px; border-radius:.4rem; }
    .port-detail-theme .port-country-copy { min-width:0; }
    .port-detail-theme .port-country-copy .country-kicker { font-size:.62rem; letter-spacing:.1em; }
    .port-detail-theme .port-country-copy .country-name { font-size:1rem; letter-spacing:.04em; }
    .port-detail-theme .btn { transition:transform .18s ease, box-shadow .18s ease, filter .18s ease; }
    .port-detail-theme .btn:hover { transform:translateY(-2px); filter:brightness(1.12); box-shadow:0 0 16px rgba(34,211,238,.2); }
    .port-detail-theme .port-hub-item, .port-detail-theme .card-body .bg-light, .port-detail-theme .card-body .bg-dark { background-image:linear-gradient(135deg, rgba(30,41,59,.72), rgba(15,23,42,.9)) !important; border-color:rgba(56,189,248,.2) !important; transition:transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .port-detail-theme .port-hub-item:hover, .port-detail-theme .card-body .bg-light:hover, .port-detail-theme .card-body .bg-dark:hover { transform:translateY(-2px); border-color:rgba(34,211,238,.62) !important; box-shadow:0 0 16px rgba(34,211,238,.1); }
    @keyframes tactical-scan { from { transform:translateY(-18%); } to { transform:translateY(18%); } }
    @media (prefers-reduced-motion: reduce) { .port-detail-theme::after, .port-detail-theme .card, .port-detail-theme .btn { animation:none; transition:none; } }
    .port-detail-theme > .card, .port-detail-theme > .row { width:min(1380px, 100%); margin-left:auto; margin-right:auto; }
    .port-detail-theme > .row { --bs-gutter-x:1.75rem; --bs-gutter-y:1.75rem; margin-bottom:1.75rem !important; }
    .port-detail-theme > .card { margin-bottom:1.75rem !important; }
    .port-detail-theme .card-body { padding:1.5rem !important; }
    .port-detail-theme .card-header { padding:1rem 1.35rem !important; }
    .port-detail-theme .table-responsive { border-radius:.45rem; overflow:auto; }
    @media (max-width: 768px) { .port-detail-theme { padding-left:1rem !important; padding-right:1rem !important; } .port-detail-theme .card-body { padding:1rem !important; } }
</style>
<div class="container-fluid py-4 port-detail-theme" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    @php
        $countryName = $port->country->name ?? 'N/A';
        $currencyCode = $port->country->currency_code ?? 'USD';
        
        $dbCode = strtolower($port->country->code ?? 'id');
        $cCode = \App\Services\CountryFlagService::iso2($dbCode) ?? 'un';
        if ($dbCode === 'brn' || $dbCode === 'bn') {
            $officialRegion = "Southeast Asia";
            $officialLang = "Malay / English";
        } elseif ($dbCode === 'idn' || $dbCode === 'id') {
            $officialRegion = "Southeast Asia";
            $officialLang = "Indonesian (Bahasa)";
        } elseif ($dbCode === 'chn' || $dbCode === 'cn') {
            $officialRegion = "Eastern Asia";
            $officialLang = "Chinese (Mandarin)";
        } else {
            $officialRegion = $exchangeData['region'] ?? ($port->country->region ?? 'Global Maritime Region');
            $officialLang = $port->country->language ?? 'Local Language';
        }
        
        $liveRate = $exchangeData['rate_against_usd'] ?? $exchangeData['rate'] ?? null;
    @endphp

    <div class="mb-3">
        <a href="{{ route('ports.index') }}" class="back-dashboard">
            <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali ke Live Radar
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4 bg-dark text-white p-4" style="border-radius: 12px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <!-- BADGE RESILIENCY NETWORKS INDIKATOR -->
                @if(($exchangeData['api_status'] ?? 'OK') === 'FALLBACK_ACTIVE')
                    <span class="badge bg-warning text-dark mb-2 animate__animated animate__flash animate__infinite">
                        <i class="bi bi-wifi-off"></i> Satelit Global Offline - Menggunakan Data Cache Lokal
                    </span>
                @else
                    <span class="badge bg-success mb-2">
                        <i class="bi bi-cloud-check"></i> Semua Koneksi Satelit API Aktif (100% Real-Time)
                    </span>
                @endif
                <h1 class="h2 mb-1 fw-bold text-warning">{{ $port->name }}</h1>
                <p class="mb-0 text-white-50">
                    <i class="bi bi-geo-alt-fill text-danger"></i> 
                    {{ $port->name }}, {{ $countryName }} 
                    <span class="ms-2 text-info font-monospace">({{ $port->latitude }}, {{ $port->longitude }})</span>
                </p>
            </div>
            
            <div class="port-identity-stack d-flex flex-column gap-2">
                <div class="tactical-strip"><span><i class="bi bi-broadcast-pin text-info"></i> Port ID <strong>#{{ $port->id }}</strong></span><span><i class="bi bi-radar text-success"></i> Radar <strong>ACTIVE</strong></span><span><i class="bi bi-clock text-warning"></i> Sync <strong>{{ now()->format('H:i') }}</strong></span></div>
                <div class="port-country-card">
                <div class="shadow-sm rounded border border-secondary bg-secondary d-flex align-items-center justify-content-center overflow-hidden" style="width: 80px; height: 50px; flex-shrink: 0; position: relative;">
                    <img src="https://flagcdn.com/w160/{{ $cCode }}.png" alt="Flag of {{ $countryName }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                
                <div class="port-country-copy overflow-hidden">
                    <small class="d-block text-info text-uppercase fw-semibold country-kicker"><i class="bi bi-globe2 me-1"></i> Country Profile</small>
                    <h6 class="mb-0 text-warning fw-bold text-truncate country-name">{{ $cCode === 'bn' ? 'Brunei Darussalam' : $countryName }}</h6>
                    <small class="d-block text-white-50" style="font-size: 0.8rem;">Benua/Region: {{ $officialRegion }}</small>
                    <small class="d-block text-white-50" style="font-size: 0.75rem;">
                        <i class="bi bi-translate text-info"></i> Kliring: {{ $officialLang }}
                    </small>
                </div>
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
                            Suhu: {{ $port->temp !== null ? $port->temp . ' °C' : 'N/A' }}
                        </span>
                    </div>
                    <small class="d-block text-muted mt-3"><i class="bi bi-wind text-primary"></i> Angin: <strong>{{ $port->wind_speed !== null ? $port->wind_speed . ' km/h' : 'N/A' }}</strong></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 bg-white text-dark">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold">Status Risiko Badai</h6>
                        <h3 class="fw-bold mt-2 text-{{ ($port->storm_risk_status === 'High') ? 'danger' : (($port->storm_risk_status === 'Medium') ? 'warning' : 'success') }}">
                            {{ $port->temp !== null ? $port->storm_risk_status : 'N/A' }}
                        </h3>
                    </div>
                    <small class="text-muted d-block"><i class="bi bi-cloud-drizzle-fill text-info"></i> Curah Hujan: {{ $port->rain !== null ? $port->rain . ' mm' : 'N/A' }}</small>
                    <small class="text-muted d-block mt-1"><i class="bi bi-speedometer2 text-warning"></i> Dampak ke Risk Score: <strong class="text-{{ ($port->risk_score ?? 0) >= 75 ? 'danger' : (($port->risk_score ?? 0) >= 40 ? 'warning' : 'success') }}">{{ $port->risk_score !== null ? $port->risk_score . '/100' : 'N/A' }}</strong></small>
                    @if($stormZoneExposure)
                        <small class="text-{{ $stormZoneExposure['risk'] === 'High' ? 'danger' : 'warning' }} d-block mt-1 fw-semibold"><i class="bi bi-broadcast-pin"></i> Dampak zona: {{ strtoupper($stormZoneExposure['risk']) }} dari {{ $stormZoneExposure['source_name'] }} ({{ number_format($stormZoneExposure['distance_km'], 0) }} km)</small>
                    @endif
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
                                    <span class="{{ ($shipment->weather_delay_hours ?? 0) > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                        {{ \Carbon\Carbon::parse($shipment->adaptive_eta)->format('d M Y') }}
                                    </span>
                                    @if(($shipment->weather_delay_hours ?? 0) > 0)<small class="d-block text-danger">+{{ $shipment->weather_delay_hours }} jam · {{ $shipment->route_weather_status }}</small>@endif
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
                            <th>ETA Adaptif (Cuaca)</th>
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
                                <td><span class="{{ ($shipment->weather_delay_hours ?? 0) > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">{{ \Carbon\Carbon::parse($shipment->adaptive_eta)->format('d M Y') }}</span>@if(($shipment->weather_delay_hours ?? 0) > 0)<small class="d-block text-danger">+{{ $shipment->weather_delay_hours }} jam · {{ $shipment->route_weather_status }}</small>@endif</td>
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
                                <td><span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">Live Tracking</span></td>
                                <td class="fw-bold text-info"><i class="bi bi-compass-fill small"></i> {{ $vessel['dest_name'] }}</td>
                                <td class="fw-bold text-dark">${{ number_format($vessel['currency_value'] ?? 52000, 2) }}</td>
                                <td><span class="badge bg-warning text-dark p-2 text-uppercase" style="font-size: 11px;">{{ $vessel['status'] ?? 'DEPARTED' }}</span></td>
                            </tr>
                        @endforeach
                        @if($port->outboundShipments->isEmpty() && count($customOutboundVessels ?? []) == 0)
                            <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada armada kargo yang dijadwalkan keluar.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RESTRUKTURISASI UI/UX: RENDER REAL-TIME NEWS CARD BERBASIS GNEWS API DATA -->
    <div class="card shadow-sm border-0 mb-4 bg-white text-dark">
        <div class="card-header bg-danger bg-opacity-10 border-0 pt-3">
            <h5 class="card-title fw-bold mb-0 text-danger"><i class="bi bi-newspaper"></i> Intelijen Geopolitik & Berita Logistik Pelabuhan (GNews Live)
                @if(($newsScope ?? 'none') === 'port')<span class="badge bg-success ms-2 align-middle" style="font-size:10px;">Spesifik Port</span>@elseif(($newsScope ?? 'none') === 'country_fallback')<span class="badge bg-warning text-dark ms-2 align-middle" style="font-size:10px;">Fallback Negara</span>@endif
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @if(!empty($newsData) && count($newsData) > 0)
                    @foreach(array_slice($newsData, 0, 3) as $news)
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded border border-light-subtle h-100 d-flex flex-column justify-content-between shadow-sm">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-danger text-uppercase" style="font-size: 9px;"><i class="bi bi-broadcast"></i> Live Feed</span>
                                        <small class="text-muted font-monospace" style="font-size: 10px;">
                                            {{ isset($news['publishedAt']) ? \Carbon\Carbon::parse($news['publishedAt'])->diffForHumans() : now()->diffForHumans() }}
                                        </small>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-2 text-line-clamp" style="font-size: 13px; line-height: 1.4;">
                                        {{ $news['title'] ?? 'Logistics Update' }}
                                    </h6>
                                    <p class="text-muted small mb-3 text-justify text-line-clamp-desc" style="font-size: 11px; line-height: 1.5;">
                                        {{ $news['description'] ?? 'No extra description provided by the wire.' }}
                                    </p>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-10">
                                    <span class="text-primary fw-semibold" style="font-size: 10px;"><i class="bi bi-journal-bookmark-fill"></i> {{ $news['source']['name'] ?? 'Global Intelligence' }}</span>
                                    <a href="{{ $news['url'] ?? '#' }}" target="_blank" class="btn btn-xs btn-outline-danger py-1 px-2 fw-bold text-uppercase rounded" style="font-size: 9px; text-decoration:none;">
                                        Baca Sumber <i class="bi bi-box-arrow-up-right small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="p-3 bg-light rounded border border-light-subtle d-flex align-items-center gap-3">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                            <div>
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 13px;">{{ ($newsScope ?? 'none') === 'country_fallback' ? 'Tidak ada berita spesifik port' : 'Belum ada artikel berita yang cocok' }}</h6>
                                <small class="text-muted" style="font-size: 11px;">{{ ($newsScope ?? 'none') === 'country_fallback' ? 'Berita negara ditampilkan sebagai fallback. Sentiment tetap dianalisis dari artikel yang tersedia.' : 'GNews belum mengembalikan artikel yang cocok untuk port ini.' }}</small>
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

    </div>

    <div class="row g-4 mb-4">
        @if(false)
        <div class="col-lg-6 d-none">
            <div class="card shadow-sm border-0 h-100 bg-white text-dark">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-cash-stack text-success"></i> Market Intelligence (Ekonomi)</h5>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded mb-3 border border-light-subtle text-center">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Nilai Tukar Forex Berjalan</small>
                        <h3 class="fw-bold text-dark my-1">
                            {{ $liveRate !== null ? '1 USD = ' . number_format($liveRate, 2) . ' ' . $currencyCode : 'Kurs N/A' }}
                        </h3>
                        <small class="text-success small font-monospace"><i class="bi bi-patch-check-fill"></i> Sync: Live Data Integrated</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                            <tbody>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">PDB / GDP Negara (World Bank):</td>
                                    <td class="text-end fw-bold text-dark py-2">
                                        ${{ $port->country->gdp && $port->country->gdp > 0 ? number_format($port->country->gdp) : '1,200,000,000,000' }} USD
                                    </td>
                                </tr>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">Tingkat Inflasi Tahunan:</td>
                                    <td class="text-end fw-bold text-danger py-2">{{ $port->country->inflation_rate && $port->country->inflation_rate > 0 ? $port->country->inflation_rate : '2.1' }}%</td>
                                </tr>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">Total Populasi Penduduk:</td>
                                    <td class="text-end fw-bold text-dark py-2">{{ $port->country->population ? number_format($port->country->population) : '0' }} Jiwa</td>
                                </tr>
                                <tr class="border-bottom border-light">
                                    <td class="text-muted ps-0 py-2">Volume Ekspor Global (Real API):</td>
                                    <td class="text-end fw-bold text-success py-2">
                                        ${{ $port->country->export_volume && $port->country->export_volume > 0 ? number_format($port->country->export_volume) : '0' }} USD
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Volume Impor Global (Real API):</td>
                                    <td class="text-end fw-bold text-primary py-2">
                                        ${{ $port->country->import_volume && $port->country->import_volume > 0 ? number_format($port->country->import_volume) : '0' }} USD
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @endif
        <div class="col-lg-12">
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
    
    /* CSS Utility Helpers untuk pembatasan baris teks berita */
    .text-line-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .text-line-clamp-desc {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Render the forecast independently so a blocked map tile/Leaflet request
        // cannot prevent the weather chart from appearing.
        var weatherCanvas = document.getElementById('weatherChart');
        var weatherValues = {!! json_encode($exchangeData['weather_data'] ?? []) !!};
        if (weatherCanvas && Array.isArray(weatherValues) && weatherValues.length && typeof Chart !== 'undefined') {
            new Chart(weatherCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                    datasets: [{ label: 'Suhu Lingkungan (°C)', data: weatherValues, borderColor: '#2499e8', backgroundColor: 'rgba(36,153,232,.12)', borderWidth: 3, fill: true, tension: .3 }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

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
            if (ctxWeather && realWeatherData && typeof Chart !== 'undefined' && !Chart.getChart(ctxWeather)) {
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

    });
</script>
@endpush
@endsection
