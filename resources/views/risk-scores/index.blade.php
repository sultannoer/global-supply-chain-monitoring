@extends('layouts.app')

@section('content')
<div class="min-vh-100 text-white" style="background: #0b0f19; font-family: 'Segoe UI', Roboto, sans-serif;">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <a href="{{ route('ports.index') }}" class="back-dashboard"><i class="bi bi-arrow-left"></i>Kembali ke dashboard</a>
                <h2 class="fw-bold mb-1 mt-2"><i class="bi bi-shield-exclamation text-warning me-2"></i>Risk Score Engine</h2>
                <p class="text-white-50 mb-0 small">Skor berbobot: cuaca 35% · inflasi 25% · kurs 15% · berita 25%.</p>
            </div>
            <div class="d-flex gap-2"><a href="#country-ranking" class="btn btn-info btn-sm"><i class="bi bi-list-ol me-1"></i>Lihat Ranking Negara</a><a href="{{ url('/api/risk') }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-code-slash me-1"></i>Lihat API Risk</a></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3"><div class="card h-100 bg-dark border-secondary border-opacity-25"><div class="card-body"><small class="text-white-50 text-uppercase">Negara dinilai</small><div class="fs-3 fw-bold text-info">{{ $summary['total'] }}</div></div></div></div>
            <div class="col-6 col-lg-3"><div class="card h-100 bg-dark border-secondary border-opacity-25"><div class="card-body"><small class="text-white-50 text-uppercase">Rata-rata risiko</small><div class="fs-3 fw-bold text-warning">{{ $summary['average'] }}<small class="fs-6">/100</small></div></div></div></div>
            <div class="col-6 col-lg-3"><div class="card h-100 bg-dark border-secondary border-opacity-25"><div class="card-body"><small class="text-white-50 text-uppercase">High / Critical</small><div class="fs-3 fw-bold text-danger">{{ $summary['high'] + $summary['critical'] }}</div><small class="text-white-50">{{ $summary['high'] }} high · {{ $summary['critical'] }} critical</small></div></div></div>
            <div class="col-6 col-lg-3"><div class="card h-100 bg-dark border-secondary border-opacity-25"><div class="card-body"><small class="text-white-50 text-uppercase">Data coverage</small><div class="fs-3 fw-bold text-success">{{ $summary['coverage'] }}%</div></div></div></div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div id="country-ranking" class="card bg-dark border-secondary border-opacity-25 shadow-sm">
                    <div class="card-header bg-transparent border-secondary border-opacity-25">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                            <span class="fw-semibold">Ranking Risiko Negara</span>
                            <small class="text-white-50 text-end">Klik nama negara atau gunakan tombol Lihat Tren</small>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label for="riskCountrySearch" class="visually-hidden">Cari negara</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-black border-secondary text-info"><i class="bi bi-search"></i></span>
                                    <input id="riskCountrySearch" type="search" class="form-control bg-black border-secondary text-white" placeholder="Cari nama negara atau kode ISO..." autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="riskLevelFilter" class="visually-hidden">Filter level risiko</label>
                                <select id="riskLevelFilter" class="form-select form-select-sm bg-black border-secondary text-white">
                                    <option value="">Semua level risiko</option>
                                    <option value="LOW">Low</option>
                                    <option value="MEDIUM">Medium</option>
                                    <option value="HIGH">High</option>
                                    <option value="CRITICAL">Critical</option>
                                    <option value="UNKNOWN">Unknown</option>
                                </select>
                            </div>
                        </div>
                        <small id="riskFilterStatus" class="text-white-50 d-block mt-2"></small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="min-width: 760px;">
                            <thead class="text-white-50 small"><tr><th>#</th><th>Negara</th><th>Score</th><th>Level</th><th>Cuaca</th><th>Inflasi</th><th>Kurs</th><th>Berita</th><th>Coverage</th><th>Dihitung</th><th class="text-end">Aksi</th></tr></thead>
                            <tbody>
                                @forelse($latestScores as $score)
                                    @php($color = match($score->risk_level) { 'CRITICAL' => 'danger', 'HIGH' => 'warning', 'MEDIUM' => 'info', 'LOW' => 'success', default => 'secondary' })
                                    <tr data-risk-row data-country-search="{{ strtolower(($score->country?->name ?? $score->country_code).' '.$score->country_code) }}" data-risk-level="{{ $score->risk_level }}">
                                        <td class="text-center text-white-50 fw-bold">{{ $loop->iteration }}</td>
                                        <td><a href="{{ route('trends.index', ['country' => $score->country_code]) }}" class="text-white text-decoration-none fw-semibold">{{ $score->country?->name ?? $score->country_code }} <i class="bi bi-graph-up-arrow text-success ms-1 small"></i></a><small class="d-block text-white-50">{{ $score->country_code }}</small></td>
                                        <td class="fw-bold text-{{ $color }}">{{ number_format($score->total_score, 1) }}</td>
                                        <td><span class="badge text-bg-{{ $color }}">{{ $score->risk_level }}</span></td>
                                        <td>{{ $score->weather_score !== null ? number_format($score->weather_score, 1) : 'N/A' }}</td>
                                        <td>{{ $score->inflation_score !== null ? number_format($score->inflation_score, 1) : 'N/A' }}</td>
                                        <td>{{ $score->exchange_score !== null ? number_format($score->exchange_score, 1) : 'N/A' }}</td>
                                        <td>{{ $score->news_score !== null ? number_format($score->news_score, 1) : 'N/A' }}</td>
                                        <td><span class="text-{{ $score->data_coverage >= 75 ? 'success' : 'warning' }}">{{ $score->data_coverage }}%</span></td>
                                        <td class="small text-white-50">{{ $score->calculated_at?->diffForHumans() }}</td>
                                        <td class="text-end"><div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('trends.index', ['country' => $score->country_code]) }}" class="btn btn-sm btn-outline-success text-nowrap"><i class="bi bi-graph-up-arrow me-1"></i>Tren</a>
                                            <a href="{{ route('news-sentiment.index', ['country' => $score->country_code]) }}" class="btn btn-sm btn-outline-danger text-nowrap"><i class="bi bi-newspaper me-1"></i>Berita</a>
                                        </div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="text-center text-white-50 py-5">Belum ada risk score. Batch sinkronisasi akan menghasilkan skor pertama.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="riskPagination" class="card-footer bg-transparent border-secondary border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small id="riskPageStatus" class="text-white-50"></small>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Navigasi halaman negara">
                            <button id="riskPrevPage" type="button" class="btn btn-outline-secondary">Sebelumnya</button>
                            <button id="riskNextPage" type="button" class="btn btn-outline-info">Berikutnya</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
                    <div class="card-header bg-transparent border-secondary border-opacity-25"><span class="fw-semibold text-danger"><i class="bi bi-bell-fill me-1"></i>Alert Aktif</span></div>
                    <div class="card-body p-3">
                        @forelse($alerts as $alert)
                            @php($color = $alert->alert_level === 'CRITICAL' ? 'danger' : 'warning')
                            <div class="border-start border-3 border-{{ $color }} bg-{{ $color }} bg-opacity-10 rounded p-3 mb-3">
                                <div class="d-flex justify-content-between gap-2"><span class="fw-bold text-{{ $color }} small">{{ $alert->alert_level }} · {{ $alert->risk_type }}</span><small class="text-white-50">{{ $alert->created_at->diffForHumans() }}</small></div>
                                <p class="small text-white-50 mb-0 mt-2">{{ $alert->message }}</p>
                            </div>
                        @empty
                            <div class="text-center text-white-50 py-5"><i class="bi bi-shield-check text-success fs-2 d-block mb-2"></i>Tidak ada alert aktif.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const search = document.getElementById('riskCountrySearch');
    const level = document.getElementById('riskLevelFilter');
    const status = document.getElementById('riskFilterStatus');
    const pageStatus = document.getElementById('riskPageStatus');
    const pagination = document.getElementById('riskPagination');
    const previous = document.getElementById('riskPrevPage');
    const next = document.getElementById('riskNextPage');
    const pageSize = 20;
    let currentPage = 1;
    const rows = () => Array.from(document.querySelectorAll('[data-risk-row]'));

    function renderRiskRows() {
        const query = (search.value || '').trim().toLowerCase();
        const selectedLevel = level.value;
        const matchingRows = rows().filter((row) => {
            const matchesText = !query || row.dataset.countrySearch.includes(query);
            const matchesLevel = !selectedLevel || row.dataset.riskLevel === selectedLevel;
            return matchesText && matchesLevel;
        });

        const totalPages = Math.max(1, Math.ceil(matchingRows.length / pageSize));
        currentPage = Math.min(currentPage, totalPages);
        rows().forEach((row) => row.classList.add('d-none'));
        matchingRows.slice((currentPage - 1) * pageSize, currentPage * pageSize)
            .forEach((row) => row.classList.remove('d-none'));

        const first = matchingRows.length ? ((currentPage - 1) * pageSize) + 1 : 0;
        const last = Math.min(currentPage * pageSize, matchingRows.length);
        status.textContent = query || selectedLevel ? `${matchingRows.length} negara sesuai filter` : '';
        pageStatus.textContent = matchingRows.length ? `Menampilkan ${first}–${last} dari ${matchingRows.length} negara · Halaman ${currentPage}/${totalPages}` : 'Tidak ada negara yang sesuai.';
        pagination.classList.toggle('d-none', matchingRows.length === 0);
        previous.disabled = currentPage <= 1;
        next.disabled = currentPage >= totalPages;
    }

    search?.addEventListener('input', () => { currentPage = 1; renderRiskRows(); });
    level?.addEventListener('change', () => { currentPage = 1; renderRiskRows(); });
    previous?.addEventListener('click', () => { currentPage -= 1; renderRiskRows(); });
    next?.addEventListener('click', () => { currentPage += 1; renderRiskRows(); });
    renderRiskRows();
})();
</script>
@endsection
