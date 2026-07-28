@extends('admin.layout')
@section('title', 'Dataset Pelabuhan')
@section('page-heading', 'Dataset Pelabuhan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2"><span class="badge rounded-pill text-bg-info"><i class="bi bi-database-check me-1"></i>WPI Local Dataset</span><span class="badge rounded-pill text-bg-dark border border-secondary">{{ number_format($totalPorts) }} records</span></div>
        <h1 class="h3 fw-bold mb-1">Master Pelabuhan</h1>
        <p class="text-secondary mb-0">Kelola lokasi, cuaca terakhir, dan skor risiko port secara terpusat.</p>
    </div>
    <a href="{{ route('admin.ports.create') }}" class="btn btn-info fw-semibold"><i class="bi bi-plus-circle-fill me-1"></i>Tambah Pelabuhan</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="admin-card p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-secondary small text-uppercase">Total Port</div><div class="fs-3 fw-bold mt-1">{{ number_format($totalPorts) }}</div></div><i class="bi bi-anchor-fill fs-4 text-info"></i></div><small class="text-secondary">{{ number_format($countryCount) }} negara terwakili</small></div></div>
    <div class="col-6 col-xl-3"><div class="admin-card p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-secondary small text-uppercase">Weather Ready</div><div class="fs-3 fw-bold mt-1 text-success">{{ $weatherCoverage }}%</div></div><i class="bi bi-cloud-sun-fill fs-4 text-success"></i></div><small class="text-secondary">{{ number_format($weatherReady) }} port memiliki data lengkap</small></div></div>
    <div class="col-6 col-xl-3"><div class="admin-card p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-secondary small text-uppercase">High Risk</div><div class="fs-3 fw-bold mt-1 text-danger">{{ number_format($highRiskCount) }}</div></div><i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i></div><small class="text-secondary">Skor risiko ≥ 60</small></div></div>
    <div class="col-6 col-xl-3"><div class="admin-card p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-secondary small text-uppercase">Halaman Aktif</div><div class="fs-3 fw-bold mt-1 text-warning">{{ $ports->currentPage() }}</div></div><i class="bi bi-list-ol fs-4 text-warning"></i></div><small class="text-secondary">{{ $ports->perPage() }} record per halaman</small></div></div>
</div>

<div class="admin-card overflow-hidden">
    <div class="p-3 p-lg-4 border-bottom border-secondary border-opacity-25">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h2 class="h6 fw-bold mb-1">Daftar Port Terdaftar</h2><small class="text-secondary">{{ $ports->total() }} hasil{{ $search ? ' untuk pencarian “'.$search.'”' : '' }}</small></div><span class="small text-secondary"><i class="bi bi-info-circle me-1"></i>Gunakan nama port, negara, atau kode ISO3</span></div>
        <form method="GET" class="row g-2"><div class="col-md"><div class="input-group"><span class="input-group-text bg-black border-secondary text-info"><i class="bi bi-search"></i></span><input name="search" value="{{ $search }}" class="form-control" placeholder="Cari nama pelabuhan, negara, atau kode..."></div></div><div class="col-auto"><button class="btn btn-info px-4"><i class="bi bi-funnel me-1"></i>Filter</button>@if($search)<a href="{{ route('admin.ports.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>@endif</div></form>
    </div>
    <div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Pelabuhan</th><th>Negara</th><th>Koordinat</th><th>Kondisi Cuaca</th><th>Risiko</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @forelse($ports as $port)
            <tr>
                <td><div class="fw-semibold text-white">{{ $port->name }}</div><small class="text-secondary">ID #{{ $port->id }}</small></td>
                <td><div class="fw-semibold">{{ $port->country?->name ?? 'Unknown' }}</div><span class="badge rounded-pill text-bg-dark border border-secondary">{{ $port->country_code }}</span></td>
                <td><code class="text-info">{{ number_format($port->latitude, 4) }}</code><small class="d-block text-secondary"><code class="text-info">{{ number_format($port->longitude, 4) }}</code></small></td>
                <td>@if($port->temp !== null && $port->wind_speed !== null && $port->rain !== null)<span class="badge rounded-pill text-bg-success"><i class="bi bi-check-circle me-1"></i>Lengkap</span><small class="d-block text-secondary mt-1">{{ number_format($port->temp, 1) }}°C · {{ number_format($port->wind_speed, 1) }} km/h</small>@else<span class="badge rounded-pill text-bg-secondary"><i class="bi bi-dash-circle me-1"></i>Belum lengkap</span><small class="d-block text-secondary mt-1">{{ $port->temp !== null ? 'Suhu tersedia' : 'Menunggu sinkronisasi' }}</small>@endif</td>
                <td><span class="badge rounded-pill text-bg-{{ $port->risk_score >= 60 ? 'danger' : ($port->risk_score >= 30 ? 'warning' : 'success') }}">{{ $port->risk_score }}/100</span><small class="d-block text-secondary mt-1">{{ $port->storm_risk_status ?? 'Low' }} storm</small></td>
                <td class="text-end text-nowrap"><a href="{{ route('admin.ports.edit', $port) }}" class="btn btn-outline-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a><form method="POST" action="{{ route('admin.ports.destroy', $port) }}" class="d-inline" onsubmit="return confirm('Hapus dataset pelabuhan ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" title="Hapus"><i class="bi bi-trash3"></i></button></form></td>
            </tr>
        @empty<tr><td colspan="6" class="text-center text-secondary py-5"><i class="bi bi-search display-6 d-block mb-2"></i>Pelabuhan tidak ditemukan.<div class="small mt-2">Coba ubah kata kunci pencarian.</div></td></tr>@endforelse
    </tbody></table></div>
    @if($ports->hasPages())<div class="p-3 p-lg-4 border-top border-secondary border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-2"><small class="text-secondary">Menampilkan {{ $ports->firstItem() }}–{{ $ports->lastItem() }} dari {{ $ports->total() }} port</small>{{ $ports->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
