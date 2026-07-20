@extends('layouts.app')

@section('content')
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
                <small class="text-white-50 font-monospace">INTELLIGENCE DATA SYNCED LIVE FROM EXTERNAL APIS</small>
            </div>
            <div class="text-end">
                <!-- FIX BENDERA: Menggunakan kode cca2 hasil fetch API riil eksternal -->
                <img src="https://flagcdn.com/w80/{{ $apiData['cca2'] }}.png" class="rounded shadow border border-secondary border-opacity-50" style="max-height: 45px;">
                <span class="d-block font-monospace text-warning fw-bold small mt-1">{{ strtoupper($country->name) }} ({{ strtoupper($country->code) }})</span>
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
                        <p class="mb-2 text-white-50">National Currency   : <span class="text-white fw-bold ms-1">{{ $apiData['currency_name'] }} ({{ $apiData['currency'] }})</span></p>
                    </div>
                </div>

                <!-- 2. LIVE EXCHANGE RATE METRICS -->
                <div class="col-md-6">
                    <div class="p-4 rounded bg-black bg-opacity-30 h-100 border border-secondary border-opacity-10 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="text-success fw-bold mb-3 border-bottom border-success border-opacity-25 pb-2"><i class="bi bi-cash-coin"></i> Real-Time Forex Tracker</h5>
                            <p class="mb-2 text-white-50 fw-semibold small text-uppercase tracking-wider">Kurs Mata Uang Saat Ini (Terhadap USD):</p>
                            <span class="fs-2 fw-bold text-success font-monospace">
                                1 USD = {{ number_format($apiData['rate_to_usd'], 2) }} <span class="fs-5 text-white-50">{{ $apiData['currency'] }}</span>
                            </span>
                        </div>
                        <small class="text-white-50 font-monospace mt-3" style="font-size: 11px;">*Diperbarui secara otomatis via open.er-api.com</small>
                    </div>
                </div>

                <!-- 3. WORLD BANK ECONOMICS MATRIX -->
                <div class="col-12 mt-4">
                    <div class="p-4 rounded bg-black bg-opacity-40 border border-secondary border-opacity-25">
                        <h5 class="text-warning fw-bold mb-3 border-bottom border-warning border-opacity-25 pb-2"><i class="bi bi-graph-up-arrow"></i> World Bank Macroeconomic Indicators</h5>
                        
                        <div class="row g-3 text-center mt-2">
                            <div class="col-6 col-md-4">
                                <div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15 shadow-sm">
                                    <small class="text-white-50 fw-semibold d-block mb-2 text-uppercase font-monospace" style="font-size: 11px;">Gross Domestic Product (GDP)</small>
                                    <span class="fs-4 text-white fw-bold font-monospace">
                                        {{ $apiData['gdp'] > 0 ? '$' . number_format($apiData['gdp'] / 1e9, 2) . ' B' : 'Data N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15 shadow-sm">
                                    <small class="text-white-50 fw-semibold d-block mb-2 text-uppercase font-monospace" style="font-size: 11px;">Inflation Rate</small>
                                    <span class="fs-4 text-danger fw-bold font-monospace">
                                        {{ $apiData['inflation'] > 0 ? number_format($apiData['inflation'], 2) . '%' : '0.0%' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 bg-dark bg-opacity-60 rounded border border-secondary border-opacity-15 shadow-sm">
                                    <small class="text-white-50 fw-semibold d-block mb-2 text-uppercase font-monospace" style="font-size: 11px;">Total Population</small>
                                    <span class="fs-4 text-info fw-bold font-monospace">
                                        {{ $apiData['population'] > 0 ? number_format($apiData['population']) . ' Soul' : 'Integrated' }}
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
                                        {{ $apiData['export'] > 0 ? '$' . number_format($apiData['export'] / 1e9, 2) . ' Billion' : 'Data Hub N/A' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-info bg-opacity-10 rounded border border-info border-opacity-25 shadow-sm">
                                    <small class="text-info fw-bold d-block mb-2 text-uppercase tracking-wide font-monospace" style="font-size: 11px;"><i class="bi bi-box-arrow-in-down"></i> Global Import Volume</small>
                                    <span class="fs-4 text-info fw-bold font-monospace">
                                        {{ $apiData['import'] > 0 ? '$' . number_format($apiData['import'] / 1e9, 2) . ' Billion' : 'Data Hub N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 4. LOGISTIK INTERNAL: PORT HUB YANG TERDAFTAR -->
                <div class="col-12 mt-4">
                    <h5 class="text-white fw-bold mb-3"><i class="bi bi-anchor text-primary"></i> Registered Supply Chain Terminal Hubs</h5>
                    <div class="list-group shadow-sm">
                        @forelse($relatedPorts as $port)
                            <div class="list-group-item bg-dark bg-opacity-40 border-secondary border-opacity-25 text-white d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <i class="bi bi-node-plus-fill text-primary me-2"></i><strong>{{ $port->name }}</strong>
                                </div>
                                <span class="badge bg-secondary font-monospace bg-opacity-25 text-white-50 p-2">GPS: {{ $port->latitude }}, {{ $port->longitude }}</span>
                            </div>
                        @empty
                            <div class="list-group-item bg-dark bg-opacity-40 border-secondary border-opacity-25 text-white-50 text-center py-4">
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