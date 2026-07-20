@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 bg-dark text-white min-vh-100" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    <div class="row g-0 min-vh-100">
       
        <div class="col-lg-2 bg-black bg-opacity-50 border-end border-secondary border-opacity-25 d-flex flex-column justify-content-between p-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4 px-2">
                    <i class="bi bi-shield-shaded text-primary fs-3"></i>
                    <span class="fs-4 fw-bold tracking-wider text-uppercase text-white">LOGIXCHAIN</span>
                </div>
                <ul class="nav flex-column gap-1">
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ url('/') }}"><i class="bi bi-grid-1x2-fill"></i> Live Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ route('cargo.create') }}"><i class="bi bi-box-seam"></i> Input Cargo</a></li>
                    <li class="nav-item"><a class="nav-link active rounded bg-primary text-white d-flex align-items-center gap-3 px-3 py-2.5 small fw-semibold" href="{{ route('cargo.history') }}"><i class="bi bi-clock-history"></i> Log Riwayat</a></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-10 d-flex flex-column p-4">
      
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                <div>
                    <h4 class="mb-0 fw-bold text-info"><i class="bi bi-journal-check"></i> Archival Expedition Logbook</h4>
                    <small class="text-white-50 text-uppercase" style="font-size: 11px;">Data Arsip Kliring dan Penyelesaian Manifest Pelayaran</small>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-2 px-3 rounded-pill fw-semibold">
                    <i class="bi bi-database-check me-1"></i> Data Authenticated
                </span>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card bg-black bg-opacity-30 border border-secondary border-opacity-25 shadow-sm">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3 fs-3"><i class="bi bi-ship"></i></div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Pelayaran Sukses</small>
                                <h3 class="fw-bold mb-0 text-white">{{ $totalCompleted }} Armada</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-black bg-opacity-30 border border-secondary border-opacity-25 shadow-sm">
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
                    <div class="card bg-black bg-opacity-30 border border-secondary border-opacity-25 shadow-sm">
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

            <div class="card bg-black bg-opacity-20 border border-secondary border-opacity-25 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-transparent border-secondary border-opacity-25 py-3">
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
                                    <th>Status Pelaporan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($completedVessels as $vessel)
                                    <tr>
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
                                        <td><span class="badge bg-success p-2 text-uppercase fw-semibold"><i class="bi bi-shield-fill-check"></i> BERTHED SUCCESS</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
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
    .table-hover tbody tr:hover { background-color: rgba(255, 255, 255, 0.02) !important; }
</style>
@endpush