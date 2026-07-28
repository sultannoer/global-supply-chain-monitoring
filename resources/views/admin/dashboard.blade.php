@extends('admin.layout')

@section('title', 'Ringkasan')
@section('page-heading', 'Ringkasan Sistem')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div><h1 class="h3 fw-bold mb-1">Admin Dashboard</h1><p class="text-secondary mb-0">Kelola user, dataset pelabuhan, dan artikel analisis dari satu tempat.</p></div>
    <span class="badge rounded-pill text-bg-success px-3 py-2"><i class="bi bi-database-check me-1"></i>Sistem Aktif</span>
</div>
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Total User','value'=>$stats['users'],'icon'=>'people-fill','color'=>'info'],
        ['label'=>'Negara','value'=>$stats['countries'],'icon'=>'globe2','color'=>'primary'],
        ['label'=>'Dataset Port','value'=>$stats['ports'],'icon'=>'anchor','color'=>'warning'],
        ['label'=>'Artikel Analisis','value'=>$stats['articles'],'icon'=>'file-earmark-bar-graph-fill','color'=>'success'],
    ] as $card)
        <div class="col-sm-6 col-xl-3"><div class="admin-card p-3 h-100"><div class="d-flex justify-content-between"><div><div class="text-secondary small text-uppercase">{{ $card['label'] }}</div><div class="display-6 fw-bold mt-2">{{ number_format($card['value']) }}</div></div><i class="bi bi-{{ $card['icon'] }} fs-2 text-{{ $card['color'] }}"></i></div></div></div>
    @endforeach
</div>
<div class="row g-4">
    <div class="col-xl-7"><div class="admin-card h-100"><div class="p-3 border-bottom border-secondary border-opacity-25 d-flex justify-content-between"><strong>Artikel Analisis Terbaru</strong><a href="{{ route('admin.articles.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-lg me-1"></i>Artikel</a></div><div class="table-responsive"><table class="table admin-table align-middle"><thead><tr><th>Judul</th><th>Status</th><th>Penulis</th></tr></thead><tbody>@forelse($latestArticles as $article)<tr><td><div class="fw-semibold">{{ $article->title }}</div><small class="text-secondary">{{ $article->category }}</small></td><td><span class="badge text-bg-{{ $article->status === 'published' ? 'success' : 'secondary' }}">{{ ucfirst($article->status) }}</span></td><td>{{ $article->author?->name ?? 'Sistem' }}</td></tr>@empty<tr><td colspan="3" class="text-center text-secondary py-5">Belum ada artikel analisis.</td></tr>@endforelse</tbody></table></div></div></div>
    <div class="col-xl-5"><div class="admin-card h-100"><div class="p-3 border-bottom border-secondary border-opacity-25"><strong>Risiko Negara Tertinggi</strong></div><div class="p-3">@forelse($latestRisks as $risk)<div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-25"><div><div class="fw-semibold">{{ $risk->country?->name ?? $risk->country_code }}</div><small class="text-secondary">{{ $risk->country_code }}</small></div><div class="text-end"><div class="fw-bold text-warning">{{ number_format($risk->total_score, 1) }}</div><small class="text-secondary">{{ $risk->risk_level }}</small></div></div>@empty<div class="text-secondary text-center py-5">Belum ada hasil risk score.</div>@endforelse</div></div></div>
</div>
@endsection
