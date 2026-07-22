@extends('layouts.app')

@section('content')
<style>
    .port-hub-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.55rem; max-height:360px; overflow-y:auto; padding:.15rem; }
    .port-hub-item { min-width:0; background:rgba(15,23,42,.72); border:1px solid rgba(148,163,184,.2); border-radius:.45rem; padding:.7rem .8rem; }
    .port-hub-item strong { display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:.9rem; }
    .port-hub-item small { display:block; color:rgba(255,255,255,.52); font-family:monospace; font-size:.68rem; margin-top:.25rem; }
    .port-hub-item:hover { border-color:rgba(56,189,248,.65); background:rgba(14,116,144,.18); }
    .country-flag { width: 108px; height: 68px; object-fit: cover; border-radius: .45rem; border: 1px solid rgba(255,255,255,.25); box-shadow: 0 5px 14px rgba(0,0,0,.25); }
    @media (max-width: 991px) { .port-hub-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575px) { .port-hub-grid { grid-template-columns:1fr; max-height:420px; } }
</style>
<div class="container py-5 text-white" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0b0f19; min-height: 100vh;">
    
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
            <div class="text-end d-flex align-items-center gap-3">
                @if(!empty($apiData['flag_code']))
                    <img class="country-flag" src="https://flagcdn.com/w160/{{ strtolower($apiData['flag_code']) }}.png" alt="Bendera {{ $country->name }}">
                @endif
                <div><i class="bi bi-globe2 text-info fs-2"></i><span class="d-block font-monospace text-warning fw-bold small mt-1">{{ strtoupper($country->name) }} ({{ strtoupper($country->code) }})</span><span class="d-block text-white-50 small mt-1">Wilayah: {{ $apiData['region'] ?: 'N/A' }}</span><form method="POST" action="{{ route('watchlists.toggle', $country->code) }}" class="mt-2">@csrf<button class="btn btn-sm {{ $isWatched ? 'btn-warning text-dark' : 'btn-outline-warning' }} rounded-pill px-3"><i class="bi {{ $isWatched ? 'bi-star-fill' : 'bi-star' }} me-1"></i>{{ $isWatched ? 'Dipantau' : 'Tambah Favorit' }}</button></form></div>
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
