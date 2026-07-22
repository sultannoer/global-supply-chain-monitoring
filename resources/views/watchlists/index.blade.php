@extends('layouts.app')

@section('content')
<div class="min-vh-100 text-white" style="background:#0b0f19;font-family:'Segoe UI',Roboto,sans-serif;">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <a href="{{ route('ports.index') }}" class="back-dashboard"><i class="bi bi-arrow-left"></i>Kembali ke dashboard</a>
                <h2 class="fw-bold mb-1 mt-3"><i class="bi bi-star-fill text-warning me-2"></i>Favorite Monitoring List</h2>
                <p class="text-white-50 mb-0">Daftar negara yang dipilih untuk pemantauan cepat.</p>
            </div>
            <span class="badge text-bg-warning text-dark px-3 py-2">{{ $watchlists->count() }} negara dipantau</span>
        </div>

        @if(session('watchlist_message'))
            <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success">{{ session('watchlist_message') }}</div>
        @endif

        @if($watchlists->isEmpty())
            <div class="card bg-dark border-secondary border-opacity-25 text-center py-5">
                <i class="bi bi-star text-warning fs-1 mb-3"></i>
                <h5>Belum ada negara favorit</h5>
                <p class="text-white-50 mb-3">Buka detail negara lalu tekan “Tambah Favorit”.</p>
                <a href="{{ route('ports.index') }}" class="btn btn-outline-info mx-auto">Buka dashboard</a>
            </div>
        @else
            <div class="row g-3">
                @foreach($watchlists as $watchlist)
                    @php($country = $watchlist->country)
                    @if($country)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 bg-dark border-warning border-opacity-25 shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div><span class="badge bg-warning text-dark mb-2"><i class="bi bi-star-fill me-1"></i>Dipantau</span><h5 class="mb-1 text-warning">{{ $country->name }}</h5><small class="text-white-50 font-monospace">{{ $country->code }} · {{ $country->region ?: 'Wilayah belum tersedia' }}</small></div>
                                        <form method="POST" action="{{ route('watchlists.destroy', $country->code) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Hapus dari favorit"><i class="bi bi-trash3"></i></button></form>
                                    </div>
                                    <div class="mt-3 d-flex gap-2 mt-auto"><a href="{{ route('countries.show', $country->code) }}" class="btn btn-warning btn-sm text-dark flex-grow-1"><i class="bi bi-eye me-1"></i>Lihat detail</a><a href="{{ route('trends.index', ['country' => $country->code]) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-graph-up-arrow"></i></a></div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
