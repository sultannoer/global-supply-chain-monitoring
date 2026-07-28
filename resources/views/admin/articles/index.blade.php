@extends('admin.layout')
@section('title', 'Artikel Analisis')
@section('page-heading', 'Artikel Analisis')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 fw-bold mb-1">Artikel Analisis</h1><p class="text-secondary mb-0">Kelola hasil kajian logistik, ekonomi, dan geopolitik.</p></div><a href="{{ route('admin.articles.create') }}" class="btn btn-info fw-semibold"><i class="bi bi-file-earmark-plus-fill me-1"></i>Tulis Artikel</a></div>
<div class="admin-card overflow-hidden">
    <div class="p-3 border-bottom border-secondary border-opacity-25"><form method="GET" class="d-flex gap-2"><input name="search" value="{{ $search }}" class="form-control" placeholder="Cari judul atau kategori..."><button class="btn btn-outline-info"><i class="bi bi-search"></i></button>@if($search)<a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary">Reset</a>@endif</form></div>
    <div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Artikel</th><th>Sentimen</th><th>Status</th><th>Penulis</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @forelse($articles as $article)<tr><td style="min-width:280px"><div class="fw-semibold">{{ $article->title }}</div><small class="text-secondary">{{ $article->category }} · {{ $article->created_at?->format('d M Y H:i') }}</small></td><td><span class="badge text-bg-{{ $article->sentiment === 'Positive' ? 'success' : ($article->sentiment === 'Negative' ? 'danger' : 'secondary') }}">{{ $article->sentiment }}</span></td><td><span class="badge text-bg-{{ $article->status === 'published' ? 'info' : 'secondary' }}">{{ ucfirst($article->status) }}</span></td><td>{{ $article->author?->name ?? 'Sistem' }}</td><td class="text-end"><a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil"></i></a><form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="d-inline" onsubmit="return confirm('Hapus artikel analisis ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="5" class="text-center text-secondary py-5">Belum ada artikel analisis.</td></tr>@endforelse
    </tbody></table></div>
    @if($articles->hasPages())<div class="p-3 border-top border-secondary border-opacity-25">{{ $articles->onEachSide(1)->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
