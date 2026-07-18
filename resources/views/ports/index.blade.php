@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Global Ports Registry</h2>
            <p class="text-muted mb-0">Manajemen lokasi dan pemantauan logistik pelabuhan internasional.</p>
        </div>
    </div>

    <!-- Kotak Pencarian -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('ports.index') }}" method="GET" class="row g-2">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            🔍
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                               placeholder="Cari berdasarkan nama pelabuhan, kota, atau negara..." 
                               value="{{ $search ?? '' }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Cari Pelabuhan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-muted">
                    <tr>
                        <th class="ps-4 py-3">Nama Pelabuhan</th>
                        <th class="py-3">Kota</th>
                        <th class="py-3">Negara</th>
                        <th class="py-3">Koordinat (Lat, Lng)</th>
                        <th class="text-end pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ports as $p)
                        @php
                            // 1. Cek & Decode Kolom Country (Jika berbentuk string JSON, ubah ke array)
                            $countryData = is_string($p->country) ? json_decode($p->country, true) : $p->country;
                            
                            // Ambil nama negara dari object JSON, jika gagal gunakan teks mentah/fallback
                            $countryName = isset($countryData['name']) ? $countryData['name'] : ($p->country ?? 'N/A');

                            // 2. Cek & Decode Kolom City (Lakukan hal yang sama jika tipenya JSON)
                            $cityData = is_string($p->city) ? json_decode($p->city, true) : $p->city;
                            $cityName = isset($cityData['name']) ? $cityData['name'] : ($p->city ?? 'N/A');
                        @endphp
                        <tr>
                            <td class="ps-4 py-3 fw-bold text-dark">{{ $p->name }}</td>
                            <td class="py-3 text-secondary">{{ $cityName }}</td>
                            <td class="py-3">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1.5">
                                    {{ $countryName }}
                                </span>
                            </td>
                            <td class="py-3 text-muted font-monospace small">
                                {{ $p->latitude }}, {{ $p->longitude }}
                            </td>
                            <td class="text-end pe-4 py-3">
                                <a href="{{ route('ports.show', $p->id) }}" class="btn btn-sm btn-outline-dark px-3 rounded-pill fw-medium shadow-sm">
                                    Buka Dashboard ⚓
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                📭 Tidak ada data pelabuhan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
       
        @if($ports->hasPages())
            <div class="card-footer bg-white border-top-0 py-3 px-4">
                {{ $ports->appends(['search' => $search])->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection