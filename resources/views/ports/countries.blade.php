@extends('layouts.app')

@section('content')
<style>
    .country-detail-theme { min-height:100vh; background:#0b0f19; color:#e2e8f0; }
    .country-detail-theme .card { background:#1e293b !important; color:#e2e8f0 !important; border-color:#334155 !important; }
    .country-detail-theme .bg-black { background:#0f172a !important; }
    .country-detail-theme .text-muted { color:#94a3b8 !important; }
    .country-detail-theme .text-dark { color:#e2e8f0 !important; }
    .country-detail-theme { position:relative; overflow:hidden; background-color:#080d18; background-image:linear-gradient(rgba(34,211,238,.022) 1px, transparent 1px),linear-gradient(90deg, rgba(34,211,238,.022) 1px, transparent 1px),radial-gradient(circle at 75% 10%, rgba(8,145,178,.13), transparent 34rem); background-size:42px 42px,42px 42px,100% 100%; }
    .country-detail-theme::after { content:""; position:absolute; inset:-100% 0; pointer-events:none; opacity:.055; background:repeating-linear-gradient(0deg, transparent 0, transparent 4px, rgba(255,255,255,.03) 5px); animation:tactical-scan-country 18s linear infinite; }
    .country-detail-theme > * { position:relative; z-index:1; }
    .country-detail-theme .card { border-radius:.65rem !important; background-image:linear-gradient(145deg, rgba(30,41,59,.98), rgba(15,23,42,.96)) !important; box-shadow:0 14px 35px rgba(0,0,0,.28), inset 0 1px 0 rgba(125,211,252,.08) !important; transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .country-detail-theme .card:hover { transform:translateY(-3px); border-color:rgba(34,211,238,.5) !important; box-shadow:0 18px 42px rgba(0,0,0,.38), 0 0 22px rgba(34,211,238,.08) !important; }
    .country-detail-theme .card-header { background-image:linear-gradient(90deg, rgba(14,116,144,.18), transparent 70%) !important; }
    .country-detail-theme h1,.country-detail-theme h2,.country-detail-theme h3,.country-detail-theme h4,.country-detail-theme h5 { text-shadow:0 0 18px rgba(34,211,238,.12); }
    .country-detail-theme .badge { letter-spacing:.06em; }
    .country-detail-theme .tactical-strip { display:flex; flex-wrap:wrap; gap:.5rem .75rem; margin-top:1.15rem; padding:.65rem .8rem; border:1px solid rgba(34,211,238,.2); border-radius:.45rem; background:linear-gradient(90deg, rgba(8,47,73,.65), rgba(15,23,42,.5)); font:600 .68rem/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; }
    .country-detail-theme .tactical-strip span { display:inline-flex; align-items:center; gap:.35rem; }
    .country-detail-theme .tactical-strip strong { color:#67e8f9; }
    .country-detail-theme > .card > .tactical-strip { margin-left:1.5rem; margin-right:1.5rem; width:calc(100% - 3rem); }
    .country-detail-theme .country-identity-stack { min-width:320px; max-width:430px; width:100%; }
    .country-detail-theme .country-identity-stack .tactical-strip { margin:0; }
    .country-detail-theme .country-profile-card { display:flex; align-items:center; gap:1rem; padding:1rem; border:1px solid rgba(148,163,184,.28); border-radius:.65rem; background:linear-gradient(135deg, rgba(51,65,85,.72), rgba(15,23,42,.86)); box-shadow:inset 0 1px 0 rgba(255,255,255,.06); }
    .country-detail-theme .country-flag { width:104px; height:66px; flex:0 0 104px; }
    .country-detail-theme .country-profile-copy { min-width:0; flex:1; text-align:left; }
    .country-detail-theme .country-profile-kicker { font-size:.62rem; letter-spacing:.1em; }
    .country-detail-theme .country-profile-name { font-size:1rem; letter-spacing:.04em; margin-top:.18rem; }
    .country-detail-theme .country-profile-name small { color:#94a3b8; font-size:.7rem; }
    .country-detail-theme .country-profile-region { font-size:.76rem; margin-top:.25rem; }
    @media (max-width: 768px) { .country-detail-theme .country-profile-card { justify-content:flex-start; } }
    .country-detail-theme .btn { transition:transform .18s ease, box-shadow .18s ease, filter .18s ease; }
    .country-detail-theme .btn:hover { transform:translateY(-2px); filter:brightness(1.12); box-shadow:0 0 16px rgba(34,211,238,.2); }
    .country-detail-theme .port-hub-item, .country-detail-theme .card-body .bg-dark, .country-detail-theme .card-body .bg-black { background-image:linear-gradient(135deg, rgba(30,41,59,.72), rgba(15,23,42,.9)) !important; border-color:rgba(56,189,248,.2) !important; transition:transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .country-detail-theme .port-hub-item:hover, .country-detail-theme .card-body .bg-dark:hover, .country-detail-theme .card-body .bg-black:hover { transform:translateY(-2px); border-color:rgba(34,211,238,.62) !important; box-shadow:0 0 16px rgba(34,211,238,.1); }
    @keyframes tactical-scan-country { from { transform:translateY(-18%); } to { transform:translateY(18%); } }
    @media (prefers-reduced-motion: reduce) { .country-detail-theme::after, .country-detail-theme .card, .country-detail-theme .btn { animation:none; transition:none; } }
    .country-detail-theme > .card, .country-detail-theme > .row { width:min(1380px, 100%); margin-left:auto; margin-right:auto; }
    .country-detail-theme > .card { margin-bottom:1.75rem !important; }
    .country-detail-theme .card-body { padding:1.5rem !important; }
    .country-detail-theme .card-header { padding:1rem 1.35rem !important; }
    .country-detail-theme > .card + .card { margin-top:1.75rem; }
    @media (max-width: 768px) { .country-detail-theme { padding-left:1rem !important; padding-right:1rem !important; } .country-detail-theme .card-body { padding:1rem !important; } }
    .port-hub-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.55rem; max-height:360px; overflow-y:auto; padding:.15rem; }
    .port-hub-item { min-width:0; background:rgba(15,23,42,.72); border:1px solid rgba(148,163,184,.2); border-radius:.45rem; padding:.7rem .8rem; }
    .port-hub-item strong { display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:.9rem; }
    .port-hub-item small { display:block; color:rgba(255,255,255,.52); font-family:monospace; font-size:.68rem; margin-top:.25rem; }
    .port-hub-item:hover { border-color:rgba(56,189,248,.65); background:rgba(14,116,144,.18); }
    .country-flag { width: 108px; height: 68px; object-fit: cover; border-radius: .45rem; border: 1px solid rgba(255,255,255,.25); box-shadow: 0 5px 14px rgba(0,0,0,.25); }
    @media (max-width: 991px) { .port-hub-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575px) { .port-hub-grid { grid-template-columns:1fr; max-height:420px; } }
</style>
<div class="container-fluid py-5 text-white country-detail-theme" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm text-white border-secondary border-opacity-50"><i class="bi bi-arrow-left"></i> Radar Monitor</a>
        <span class="text-muted">/</span>
        <span class="text-warning fw-semibold">Sovereign Analytics</span>
    </div>

    <div class="card bg-dark bg-opacity-70 border-secondary border-opacity-25 shadow-lg rounded-3 overflow-hidden">
        <!-- HEADER PROFILE -->
        <div class="p-4 bg-gradient d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25" style="background: linear-gradient(135deg, #1e293b, #0f172a); border-left: 4px solid #f59e0b;">
            <div>
                <h3 class="mb-1 fw-bold text-uppercase tracking-wide" style="color: #f59e0b;">
                    🌍 {{ $country->name }}
                </h3>
                <small class="text-white-50 font-monospace">DATA TERSINKRON DARI LAYANAN API TERPUSAT</small>
            </div>
            <div class="country-identity-stack d-flex flex-column gap-2">
                <div class="tactical-strip"><span><i class="bi bi-globe2 text-info"></i> Country ID <strong>{{ strtoupper($country->code) }}</strong></span><span><i class="bi bi-radar text-success"></i> Radar <strong>ACTIVE</strong></span><span><i class="bi bi-clock text-warning"></i> Sync <strong>{{ now()->format('H:i') }}</strong></span></div>
                <div class="country-profile-card">
                    @if(!empty($apiData['flag_code']))
                        <img class="country-flag" src="https://flagcdn.com/w160/{{ strtolower($apiData['flag_code']) }}.png" alt="Bendera {{ $country->name }}">
                    @endif
                    <div class="country-profile-copy">
                        <small class="d-block text-info text-uppercase fw-semibold country-profile-kicker"><i class="bi bi-globe2 me-1"></i> Sovereign State</small>
                        <span class="d-block font-monospace text-warning fw-bold country-profile-name">{{ strtoupper($country->name) }} <small>({{ strtoupper($country->code) }})</small></span>
                        <span class="d-block text-white-50 country-profile-region">Wilayah: {{ $apiData['region'] ?: 'N/A' }}</span>
                        <form method="POST" action="{{ route('watchlists.toggle', $country->code) }}" class="mt-2">@csrf<button class="btn btn-sm {{ $isWatched ? 'btn-warning text-dark' : 'btn-outline-warning' }} rounded-pill px-3"><i class="bi {{ $isWatched ? 'bi-star-fill' : 'bi-star' }} me-1"></i>{{ $isWatched ? 'Dipantau' : 'Tambah Favorit' }}</button></form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4">
                
                <!-- 1. REST COUNTRIES FEEDS -->
                <div class="col-md-6 border-end border-secondary border-opacity-25">
                    <div class="p-4 rounded bg-black bg-opacity-30 h-100 border border-secondary border-opacity-10">
                        <h5 class="text-info fw-bold mb-3 border-bottom border-info border-opacity-25 pb-2"><i class="bi bi-globe-asia-australia"></i> REST Countries Info</h5>
                        <p class="mb-2 text-white-50">Geopolitical Region : <span class="text-white fw-bold ms-1">{{ $apiData['region'] }}</span></p>
                        <p class="mb-2 text-white-50">Official Languages  : <span class="text-white fw-bold ms-1">{{ $apiData['language'] }}</span></p>
                        <p class="mb-2 text-white-50">National Currency   : <span class="text-white fw-bold ms-1">{{ $apiData['currency'] ?? 'N/A' }}</span></p>
                    </div>
                </div>

                <!-- 2. LIVE EXCHANGE RATE METRICS -->
                <div class="col-md-6">
                    <div class="p-4 rounded bg-black bg-opacity-30 h-100 border border-secondary border-opacity-10 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="text-success fw-bold mb-3 border-bottom border-success border-opacity-25 pb-2"><i class="bi bi-cash-coin"></i> Real-Time Forex Tracker</h5>
                            <p class="mb-2 text-white-50 fw-semibold small text-uppercase tracking-wider">Kurs Mata Uang Saat Ini (Terhadap USD):</p>
                            <span class="fs-2 fw-bold text-success font-monospace">
                                {{ $apiData['rate_to_usd'] !== null ? '1 USD = ' . number_format($apiData['rate_to_usd'], 2) . ' ' . $apiData['currency'] : 'N/A' }}
                            </span>
                        </div>
                        <small class="text-white-50 font-monospace mt-3" style="font-size: 11px;">*Diperbarui secara otomatis via open.er-api.com</small>
                    </div>
                </div>

                @php
                    $countryWeather = $apiData['weather'] ?? null;
                    $stormStatus = $countryWeather['storm_risk_status'] ?? 'N/A';
                    $stormTone = $stormStatus === 'High' ? 'danger' : ($stormStatus === 'Medium' ? 'warning' : ($stormStatus === 'Low' ? 'success' : 'secondary'));
                @endphp
                <div class="col-12">
                    <div class="p-4 rounded bg-black bg-opacity-30 border border-{{ $stormTone }} border-opacity-25" style="background-image:linear-gradient(120deg, rgba(14,116,144,.14), transparent 55%) !important;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border-bottom border-{{ $stormTone }} border-opacity-25 pb-2">
                            <h5 class="text-{{ $stormTone }} fw-bold mb-0"><i class="bi bi-cloud-lightning-rain-fill me-1"></i> Live Weather & Storm Impact</h5>
                            <span class="badge text-bg-{{ $stormTone }} rounded-pill px-3">{{ strtoupper($stormStatus) }} WEATHER</span>
                        </div>
                        @if($countryWeather)
                            <div class="row g-3 text-center">
                                <div class="col-6 col-lg-3"><div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15"><small class="text-white-50 d-block text-uppercase font-monospace">Suhu</small><strong class="fs-5 text-info">{{ number_format($countryWeather['temp'], 1) }} °C</strong></div></div>
                                <div class="col-6 col-lg-3"><div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15"><small class="text-white-50 d-block text-uppercase font-monospace">Angin</small><strong class="fs-5 text-warning">{{ number_format($countryWeather['wind_speed'], 1) }} km/h</strong></div></div>
                                <div class="col-6 col-lg-3"><div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15"><small class="text-white-50 d-block text-uppercase font-monospace">Hujan</small><strong class="fs-5 text-info">{{ number_format($countryWeather['rain'], 1) }} mm</strong></div></div>
                                <div class="col-6 col-lg-3"><div class="p-3 bg-{{ $stormTone }} bg-opacity-10 rounded border border-{{ $stormTone }} border-opacity-25"><small class="text-white-50 d-block text-uppercase font-monospace">Komponen Cuaca Risiko</small><strong class="fs-5 text-{{ $stormTone }}">{{ $apiData['weather_risk_score'] !== null ? number_format($apiData['weather_risk_score'], 1) . '/100' : 'N/A' }}</strong></div></div>
                            </div>
                            @if($apiData['storm_zone'])
                                <div class="mt-3 px-3 py-2 rounded border border-{{ $apiData['storm_zone']['risk'] === 'High' ? 'danger' : 'warning' }} border-opacity-35 bg-{{ $apiData['storm_zone']['risk'] === 'High' ? 'danger' : 'warning' }} bg-opacity-10 small">
                                    <i class="bi bi-broadcast-pin me-1"></i><strong>Dampak Zona Badai {{ strtoupper($apiData['storm_zone']['risk']) }}:</strong> koordinat negara berada {{ number_format($apiData['storm_zone']['distance_km'], 0) }} km dari zona {{ $apiData['storm_zone']['source_name'] }}. Cuaca lokal tetap data asli; dampak zona ini ikut menaikkan komponen cuaca Risk Score.
                                </div>
                            @endif
                            <p class="small text-white-50 mb-0 mt-3"><i class="bi bi-info-circle text-info me-1"></i>Status ini berasal dari Open-Meteo pada koordinat referensi negara dan ikut menjadi komponen cuaca berbobot 35% pada Risk Score. Nilai yang lebih tinggi antara cuaca negara dan terminal lokal digunakan untuk menghindari dampak badai terlewat.</p>
                        @else
                            <p class="mb-0 text-white-50">Snapshot cuaca belum tersedia untuk koordinat referensi negara ini.</p>
                        @endif
                    </div>
                </div>

                <!-- 3. WORLD BANK ECONOMICS MATRIX -->
                <div class="col-12 mt-4">
                    <div class="p-4 rounded bg-black bg-opacity-40 border border-secondary border-opacity-25">
                        <h5 class="text-warning fw-bold mb-3 border-bottom border-warning border-opacity-25 pb-2"><i class="bi bi-cash-stack me-1"></i> Market Intelligence (Ekonomi)</h5>
                        
                        <div class="row g-3 text-center mt-2">
                            <div class="col-6 col-md-4">
                                <div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15 shadow-sm">
                                    <small class="text-white-50 fw-semibold d-block mb-2 text-uppercase font-monospace" style="font-size: 11px;">Gross Domestic Product (GDP)</small>
                                    <span class="fs-4 text-white fw-bold font-monospace">
                                        {{ $apiData['gdp'] !== null ? '$' . number_format($apiData['gdp'] / 1e9, 2) . ' B' : 'Data N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15 shadow-sm">
                                    <small class="text-white-50 fw-semibold d-block mb-2 text-uppercase font-monospace" style="font-size: 11px;">Inflation Rate</small>
                                    <span class="fs-4 text-danger fw-bold font-monospace">
                                        {{ $apiData['inflation'] !== null ? number_format($apiData['inflation'], 2) . '%' : 'Data N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15 shadow-sm">
                                    <small class="text-white-50 fw-semibold d-block mb-2 text-uppercase font-monospace" style="font-size: 11px;">Total Population</small>
                                    <span class="fs-4 text-info fw-bold font-monospace">
                                        {{ $apiData['population'] !== null ? number_format($apiData['population']) . ' Soul' : 'Data N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Hub Neraca Dagang (Ekspor/Impor) -->
                        <div class="row g-3 text-center mt-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-success bg-opacity-10 rounded border border-success border-opacity-25 shadow-sm">
                                    <small class="text-success fw-bold d-block mb-2 text-uppercase tracking-wide font-monospace" style="font-size: 11px;"><i class="bi bi-box-arrow-up"></i> Global Export Volume</small>
                                    <span class="fs-4 text-success fw-bold font-monospace">
                                        {{ $apiData['export'] !== null ? '$' . number_format($apiData['export'] / 1e9, 2) . ' Billion' : 'Data Hub N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-info bg-opacity-10 rounded border border-info border-opacity-25 shadow-sm">
                                    <small class="text-info fw-bold d-block mb-2 text-uppercase tracking-wide font-monospace" style="font-size: 11px;"><i class="bi bi-box-arrow-in-down"></i> Global Import Volume</small>
                                    <span class="fs-4 text-info fw-bold font-monospace">
                                        {{ $apiData['import'] !== null ? '$' . number_format($apiData['import'] / 1e9, 2) . ' Billion' : 'Data Hub N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 4. GRAFIK KURS: LEVEL NEGARA -->
                <div class="col-12 mt-4">
                    <div class="p-4 rounded bg-black bg-opacity-40 border border-success border-opacity-25">
                        <h5 class="text-success fw-bold mb-2 border-bottom border-success border-opacity-25 pb-2"><i class="bi bi-graph-up-arrow me-1"></i> Live Forex Weekly Trend</h5>
                        <p class="text-white-50 small mb-3">Tren nilai tukar 1 USD terhadap {{ $apiData['currency'] ?? 'mata uang negara' }}.</p>
                        <div style="height:260px; position:relative;"><canvas id="countryForexChart"></canvas></div>
                    </div>
                </div>

                <!-- 5. LOGISTIK INTERNAL: PORT HUB YANG TERDAFTAR -->
                <div class="col-12 mt-4">
                    <h5 class="text-white fw-bold mb-3"><i class="bi bi-anchor text-primary"></i> Registered Supply Chain Terminal Hubs</h5>
                    <div class="port-hub-grid shadow-sm">
                        @forelse($relatedPorts as $port)
                            <div class="port-hub-item text-white">
                                <strong><i class="bi bi-node-plus-fill text-primary me-2"></i>{{ $port->name }}</strong>
                                <small>GPS: {{ $port->latitude }}, {{ $port->longitude }}</small>
                            </div>
                        @empty
                            <div class="port-hub-item text-white-50 text-center py-4" style="grid-column:1/-1;">
                                <i class="bi bi-exclamation-circle text-warning me-1 fs-5 d-block mb-2"></i> Tidak ada infrastruktur pelabuhan logistik lokal terdaftar di negara ini.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('countryForexChart');
        const labels = @json($apiData['forex_labels'] ?? []);
        const values = @json($apiData['forex_data'] ?? []);
        if (!canvas || !values.length || typeof Chart === 'undefined') return;
        new Chart(canvas, { type: 'line', data: { labels, datasets: [{ label: 'Kurs vs USD', data: values, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,.12)', borderWidth: 2, pointRadius: 3, fill: true, tension: .3 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#cbd5e1' } } }, scales: { x: { ticks: { color: '#94a3b8' }, grid: { color: '#33415566' } }, y: { ticks: { color: '#94a3b8' }, grid: { color: '#33415566' } } } } });
    });
</script>
@endpush
