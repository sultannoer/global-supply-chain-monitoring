@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 bg-dark text-white min-vh-100 history-theme" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    <div class="row g-0 min-vh-100">
       
        <div class="col-lg-2 history-sidebar border-end border-secondary border-opacity-25 d-flex flex-column justify-content-between p-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4 px-2">
                    <i class="bi bi-anchor-fill text-info fs-3"></i>
                    <div class="lh-sm">
                        <span class="d-block fs-5 fw-bold tracking-wider text-uppercase text-white">GeoPort Analytics</span>
                        <small class="text-info text-uppercase" style="font-size: 9px; letter-spacing: .08em;">Global Supply Chain</small>
                    </div>
                </div>
                <ul class="nav flex-column gap-1">
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ url('/') }}"><i class="bi bi-grid-1x2-fill"></i> Live Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('cargo.create') }}"><i class="bi bi-box-seam"></i> Input Cargo</a></li>
                    <li class="nav-item"><a class="nav-link active rounded bg-primary text-white d-flex align-items-center gap-3 px-3 py-2.5 small fw-semibold" href="{{ route('cargo.history') }}"><i class="bi bi-clock-history"></i> Log Riwayat</a></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-10 d-flex flex-column p-4 p-xl-5 history-content">
      
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                <div>
                    <h4 class="mb-0 fw-bold text-info"><i class="bi bi-journal-check"></i> Archival Expedition Logbook</h4>
                    <small class="text-white-50 text-uppercase" style="font-size: 11px;">Data Arsip Kliring dan Penyelesaian Manifest Pelayaran</small>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-2 px-3 rounded-pill fw-semibold">
                    <i class="bi bi-database-check me-1"></i> Data Authenticated
                </span>
            </div>

            @if($selectedVessel)
                <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 text-info d-flex align-items-center justify-content-between gap-3">
                    <span><i class="bi bi-crosshair me-2"></i>Menampilkan riwayat kapal terpilih{{ !empty($completedVessels[0]['name']) ? ': <strong>'.e($completedVessels[0]['name']).'</strong>' : '' }}.</span>
                    <a href="{{ route('cargo.history') }}" class="btn btn-sm btn-outline-info">Tampilkan semua</a>
                </div>
            @endif

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card history-metric history-metric-primary border shadow-sm">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3 fs-3"><i class="bi bi-check2-circle"></i></div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Pelayaran Sukses</small>
                                <h3 class="fw-bold mb-0 text-white">{{ $totalCompleted }} Armada</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card history-metric history-metric-success border shadow-sm">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3 fs-3"><i class="bi bi-box-seam"></i></div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Total Volume Muatan</small>
                                <h3 class="fw-bold mb-0 text-white">{{ number_format($totalCargoDelivered) }} Tons</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card history-metric history-metric-warning border shadow-sm">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3 fs-3"><i class="bi bi-cash-coin"></i></div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Kapitalisasi Nilai Kargo</small>
                                <h3 class="fw-bold mb-0 text-white">${{ number_format($totalOperationalCost, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card history-table-card border shadow-sm rounded-3 overflow-hidden">
                <div class="card-header history-table-header border-secondary border-opacity-25 py-3">
                    <h6 class="mb-0 fw-bold small text-uppercase tracking-wider text-white-50"><i class="bi bi-table text-primary"></i> Manifest Audit Pelayaran Selesai</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0 text-nowrap" style="border-color: rgba(255,255,255,0.05);">
                            <thead class="table-dark" style="background-color: #0f1115;">
                                <tr>
                                    <th>ID Ekspedisi</th>
                                    <th>Nama Kapal / Carrier</th>
                                    <th>Pelabuhan Keberangkatan</th>
                                    <th>Pelabuhan Tujuan</th>
                                    <th>Kargo Tonase</th>
                                    <th>Valuasi Finansial</th>
                                    <th>ETA Baseline</th>
                                    <th>ETA Adaptif</th>
                                    <th>Status Pelaporan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($completedVessels as $vessel)
                                    <tr class="{{ $selectedVessel === (int) $vessel['id'] ? 'table-info bg-opacity-10' : '' }}">
                                        <td class="text-primary font-monospace small">#LOG-{{ substr($vessel['id'], 0, 6) }}</td>
                                        <td class="fw-bold text-white"><i class="bi bi-check-circle-fill text-success me-2"></i> {{ $vessel['name'] }}</td>
                                       
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25 text-white-50 p-2">
                                                <i class="bi bi-box-arrow-up text-warning me-1"></i> 
                                                {{ $vessel['origin_name'] }} 
                                                <small class="text-warning font-monospace fw-bold ms-2 bg-dark bg-opacity-50 px-1.5 py-0.5 rounded" style="font-size: 10px; border: 1px solid rgba(255, 193, 7, 0.2);">
                                                    {{ $vessel['origin_country_iso'] }}
                                                </small>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary p-2">
                                                <i class="bi bi-anchor text-info me-1"></i> 
                                                {{ $vessel['dest_name'] }} 
                                                <small class="text-info font-monospace fw-bold ms-2 bg-dark bg-opacity-50 px-1.5 py-0.5 rounded" style="font-size: 10px; border: 1px solid rgba(56, 189, 248, 0.2);">
                                                    {{ $vessel['dest_country_iso'] }}
                                                </small>
                                            </span>
                                        </td>
                                        
                                        <td>{{ number_format($vessel['cargo_weight']) }} Ton</td>
                                        <td class="text-success fw-bold">${{ number_format($vessel['currency_value'], 2) }}</td>
                                        <td>{{ $vessel['baseline_eta'] ? \Carbon\Carbon::parse($vessel['baseline_eta'])->format('d M Y') : 'N/A' }}</td>
                                        <td><span class="{{ $vessel['weather_delay_hours'] > 0 ? 'text-warning fw-bold' : 'text-success' }}">{{ $vessel['adaptive_eta'] ? \Carbon\Carbon::parse($vessel['adaptive_eta'])->format('d M Y') : 'N/A' }}</span>@if($vessel['weather_delay_hours'] > 0)<small class="d-block text-warning">+{{ $vessel['weather_delay_hours'] }} jam · {{ $vessel['route_weather_status'] }}</small>@endif</td>
                                        <td><span class="badge bg-success p-2 text-uppercase fw-semibold"><i class="bi bi-shield-fill-check"></i> BERTHED SUCCESS</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-clock-history fs-1 d-block mb-3 text-secondary"></i>
                                            Belum ada catatan armada kapal kustom yang menyelesaikan seluruh rute pelayaran penuh ke pelabuhan tujuan hari ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous">
<style>
    .hover-light:hover { background-color: rgba(255, 255, 255, 0.05); color: #ffffff !important; }
    .history-theme { background:radial-gradient(circle at 72% 0%,rgba(8,145,178,.13),transparent 34rem),linear-gradient(135deg,#080d18,#101827 58%,#0b1220); }
    .history-sidebar { background:linear-gradient(180deg,rgba(2,6,23,.94),rgba(10,18,32,.9)); }
    .history-content { background-image:linear-gradient(rgba(34,211,238,.018) 1px,transparent 1px),linear-gradient(90deg,rgba(34,211,238,.018) 1px,transparent 1px); background-size:44px 44px; }
    .history-content > .d-flex:first-child { border-color:rgba(148,163,184,.2)!important; }
    .history-metric { min-height:118px; overflow:hidden; position:relative; color:#eaf2ff!important; background:linear-gradient(145deg,#121d30,#0b1220)!important; border-color:rgba(125,211,252,.22)!important; box-shadow:0 12px 28px rgba(0,0,0,.22)!important; }
    .history-metric::after { content:""; position:absolute; inset:auto -10% -70% 45%; height:150px; background:radial-gradient(circle,rgba(34,211,238,.16),transparent 67%); pointer-events:none; }
    .history-metric .card-body { position:relative; z-index:1; }
    .history-metric .text-muted { color:#9fb0c9!important; }
    .history-metric-primary { border-top:2px solid #38bdf8!important; }
    .history-metric-success { border-top:2px solid #22c55e!important; }
    .history-metric-warning { border-top:2px solid #fbbf24!important; }
    .history-metric .bg-primary { background:rgba(14,165,233,.14)!important; border:1px solid rgba(56,189,248,.22); color:#38bdf8!important; }
    .history-metric .bg-success { background:rgba(34,197,94,.13)!important; border:1px solid rgba(74,222,128,.2); color:#4ade80!important; }
    .history-metric .bg-warning { background:rgba(245,158,11,.13)!important; border:1px solid rgba(251,191,36,.2); color:#fbbf24!important; }
    .history-metric .bg-primary i,.history-metric .bg-success i,.history-metric .bg-warning i { color:inherit!important; opacity:1!important; }
    .history-table-card { background:linear-gradient(145deg,rgba(15,23,42,.98),rgba(8,13,24,.98))!important; border-color:rgba(125,211,252,.24)!important; }
    .history-table-header { background:linear-gradient(90deg,rgba(8,47,73,.74),rgba(15,23,42,.78))!important; color:#dbeafe!important; }
    .history-table-card .table { --bs-table-bg:transparent; --bs-table-color:#e5edf8; --bs-table-border-color:rgba(148,163,184,.2); }
    .history-table-card .table thead th { background:rgba(2,6,23,.76)!important; color:#dbeafe!important; border-bottom:1px solid rgba(56,189,248,.3)!important; font-size:.76rem; letter-spacing:.03em; white-space:nowrap; }
    .history-table-card .table tbody tr { background:transparent!important; }
    .history-table-card .table tbody tr:hover { background:rgba(34,211,238,.07)!important; }
    .history-table-card .table td { color:#dbeafe!important; vertical-align:middle; }
    .history-table-card .table-responsive { border-top:1px solid rgba(148,163,184,.14); }
    .history-table-card .text-muted { color:#9fb0c9!important; }
    .history-table-card code { color:#67e8f9!important; }
    @media(max-width:991px){ .history-sidebar { min-height:auto; } .history-content { padding:1.25rem!important; } }
</style>
@endpush
