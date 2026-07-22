@extends('layouts.app')

@section('content')
<div class="min-vh-100 text-white" style="background:#0b0f19;font-family:'Segoe UI',Roboto,sans-serif;">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <a href="{{ route('ports.index') }}" class="back-dashboard"><i class="bi bi-arrow-left"></i>Kembali ke dashboard</a>
                <h2 class="fw-bold mb-1 mt-2"><i class="bi bi-graph-up-arrow text-success me-2"></i>Historical Trends</h2>
                <p class="text-white-50 mb-0 small">Riwayat aktual dari Risk Score, Open-Meteo, ExchangeRate, dan World Bank. Tidak menggunakan data simulasi.</p>
            </div>
            <span class="badge text-bg-dark border border-secondary border-opacity-50 px-3 py-2">{{ $country?->name ?? 'Belum ada negara' }}</span>
        </div>

        <form id="trendFilterForm" method="GET" action="{{ route('trends.index') }}" class="card bg-dark border-secondary border-opacity-25 shadow-sm mb-4">
            <div class="card-body row g-3 align-items-end">
                <div class="col-md-5"><label for="country" class="form-label small text-white-50">Negara</label><select id="country" name="country" class="form-select bg-black text-white border-secondary">@foreach($countries as $item)<option value="{{ $item->code }}" @selected($country?->code === $item->code)>{{ $item->name }} ({{ $item->code }})</option>@endforeach</select></div>
                <div class="col-md-5"><label for="port" class="form-label small text-white-50">Pelabuhan untuk tren cuaca</label><select id="port" name="port" class="form-select bg-black text-white border-secondary"><option value="">Pilih otomatis (data terbaru)</option>@foreach($ports as $port)<option value="{{ $port->id }}" @selected($selectedPort?->id === $port->id)>{{ $port->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Tampilkan</button></div>
            </div>
        </form>

        <div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 text-info small mb-4"><i class="bi bi-info-circle me-1"></i>Grafik baru mulai menyimpan titik data saat scheduler menjalankan batch sinkronisasi. Interval saat ini: setiap 10 menit.</div>

        <div class="row g-4">
            <div class="col-xl-6"><div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100"><div class="card-header bg-transparent border-secondary border-opacity-25"><span class="fw-semibold"><i class="bi bi-shield-exclamation text-warning me-1"></i>Risk Trend</span><small class="float-end text-white-50">Skala 0–100</small></div><div class="card-body"><div class="trend-canvas"><canvas id="riskChart"></canvas></div></div></div></div>
            <div class="col-xl-6"><div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100"><div class="card-header bg-transparent border-secondary border-opacity-25"><span class="fw-semibold"><i class="bi bi-cloud-sun text-info me-1"></i>Weather Trend</span><small class="float-end text-white-50">{{ $selectedPort?->name ?? 'Belum ada port' }}</small></div><div class="card-body"><div class="trend-canvas"><canvas id="weatherChart"></canvas></div></div></div></div>
            <div class="col-xl-6"><div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100"><div class="card-header bg-transparent border-secondary border-opacity-25"><span class="fw-semibold"><i class="bi bi-currency-exchange text-success me-1"></i>Currency Trend</span><small class="float-end text-white-50">{{ $country?->currency_code ?? 'N/A' }} per USD</small></div><div class="card-body"><div class="trend-canvas"><canvas id="currencyChart"></canvas></div></div></div></div>
            <div class="col-xl-6"><div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100"><div class="card-header bg-transparent border-secondary border-opacity-25"><span class="fw-semibold"><i class="bi bi-bar-chart-line text-primary me-1"></i>GDP & Inflation Trend</span><small class="float-end text-white-50">World Bank snapshots</small></div><div class="card-body"><div class="trend-canvas"><canvas id="economicChart"></canvas></div></div></div></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>.trend-canvas { height: 300px; position: relative; }</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($charts);
    const colors = ['#f59e0b', '#38bdf8', '#22c55e'];
    const noDataPlugin = {
        id: 'noData',
        afterDraw(chart) {
            if (chart.data.datasets.some(set => set.data.some(value => value !== null))) return;
            const {ctx, chartArea} = chart;
            ctx.save(); ctx.fillStyle = '#94a3b8'; ctx.textAlign = 'center'; ctx.font = '14px Segoe UI';
            ctx.fillText('Belum ada histori nyata untuk grafik ini.', (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2);
            ctx.restore();
        }
    };
    function createTrend(id, data, options = {}) {
        new Chart(document.getElementById(id), {
            type: 'line', data: {
                labels: data.labels,
                datasets: data.datasets.map((set, index) => ({ ...set, borderColor: colors[index], backgroundColor: colors[index] + '22', borderWidth: 2, pointRadius: 2, tension: .28, spanGaps: true, fill: false }))
            }, options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#cbd5e1' } }, tooltip: { mode: 'index', intersect: false } },
                scales: { x: { ticks: { color: '#94a3b8', maxTicksLimit: 7 }, grid: { color: '#33415566' } }, y: { beginAtZero: options.beginAtZero ?? false, ticks: { color: '#94a3b8' }, grid: { color: '#33415566' } } }
            }, plugins: [noDataPlugin]
        });
    }
    createTrend('riskChart', chartData.risk, { beginAtZero: true });
    createTrend('weatherChart', chartData.weather, { beginAtZero: true });
    createTrend('currencyChart', chartData.currency);
    new Chart(document.getElementById('economicChart'), {
        type: 'line', data: {
            labels: chartData.economic.labels,
            datasets: chartData.economic.datasets.map((set, index) => ({
                ...set, borderColor: colors[index], backgroundColor: colors[index] + '22', borderWidth: 2,
                pointRadius: 2, tension: .28, spanGaps: true, fill: false, yAxisID: index === 0 ? 'gdp' : 'inflation'
            }))
        }, options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#cbd5e1' } }, tooltip: { mode: 'index', intersect: false } },
            scales: {
                x: { ticks: { color: '#94a3b8', maxTicksLimit: 7 }, grid: { color: '#33415566' } },
                gdp: { type: 'linear', position: 'left', beginAtZero: true, ticks: { color: '#f59e0b', callback: value => '$' + new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 }).format(value) }, grid: { color: '#33415566' } },
                inflation: { type: 'linear', position: 'right', beginAtZero: true, ticks: { color: '#38bdf8', callback: value => value + '%' }, grid: { drawOnChartArea: false } }
            }
        }, plugins: [noDataPlugin]
    });
    const countrySelect = document.getElementById('country');
    const portSelect = document.getElementById('port');
    const trendFilterForm = document.getElementById('trendFilterForm');
    countrySelect.addEventListener('change', () => {
        // A port ID belongs to a country. Clear the old selection before loading the new country's ports.
        portSelect.value = '';
        trendFilterForm.submit();
    });
</script>
@endpush
