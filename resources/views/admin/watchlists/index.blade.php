@extends('admin.layout')

@section('title', 'Monitoring Favorit User')
@section('page-heading', 'Monitoring Favorit User')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Favorite Monitoring User</h1>
        <p class="text-secondary mb-0">Lihat negara yang dipantau oleh setiap akun user.</p>
    </div>
    <span class="badge text-bg-info px-3 py-2">{{ $watchlists->total() }} monitoring</span>
</div>

<div class="admin-card p-3 mb-3">
    <form method="GET" class="row g-2">
        <div class="col-md">
            <div class="input-group">
                <span class="input-group-text bg-black border-secondary text-info"><i class="bi bi-search"></i></span>
                <input name="search" value="{{ $search }}" class="form-control" placeholder="Cari user, email, negara, atau kode...">
            </div>
        </div>
        <div class="col-auto"><button class="btn btn-info px-4"><i class="bi bi-funnel me-1"></i>Filter</button>@if($search)<a href="{{ route('admin.watchlists.index') }}" class="btn btn-outline-secondary ms-1">Reset</a>@endif</div>
    </form>
</div>

<div class="admin-card overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead><tr><th>User</th><th>Negara</th><th>Wilayah</th><th>Ditambahkan</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($watchlists as $watchlist)
                <tr>
                    <td><div class="fw-semibold">{{ $watchlist->user?->name ?? 'Legacy / tidak diketahui' }}</div><small class="text-secondary">{{ $watchlist->user?->email ?? '—' }}</small></td>
                    <td>@if($watchlist->country)<a class="text-info text-decoration-none fw-semibold" href="{{ route('countries.show', $watchlist->country->code) }}">{{ $watchlist->country->name }}</a><div><small class="font-monospace text-secondary">{{ $watchlist->country->code }}</small></div>@else N/A @endif</td>
                    <td class="text-secondary">{{ $watchlist->country?->region ?: 'N/A' }}</td>
                    <td class="text-secondary">{{ $watchlist->created_at?->format('d M Y H:i') }}</td>
                    <td class="text-end"><form method="POST" action="{{ route('admin.watchlists.destroy', $watchlist) }}" onsubmit="return confirm('Hapus negara ini dari monitoring user?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3 me-1"></i>Hapus</button></form></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-5"><i class="bi bi-star fs-2 d-block mb-2"></i>Belum ada favorit user.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($watchlists->hasPages())<div class="p-3 border-top border-secondary border-opacity-25">{{ $watchlists->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
