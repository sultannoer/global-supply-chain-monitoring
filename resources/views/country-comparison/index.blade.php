@extends('layouts.app')

@section('content')
<style>
    .comparison-hero { background: linear-gradient(135deg, #14233a 0%, #101827 55%, #1f2937 100%); border: 1px solid rgba(45,212,191,.25); }
    .comparison-picker { background: linear-gradient(135deg, rgba(31,41,55,.98), rgba(17,24,39,.98)); }
    .comparison-table th, .comparison-table td { border-color: rgba(148,163,184,.2); padding: 1rem; text-align: center; vertical-align: middle; }
    .comparison-table thead th:nth-child(1), .comparison-table tbody td:nth-child(1), .comparison-table thead th:nth-child(3), .comparison-table tbody td:nth-child(3) { background: transparent; border-left: 0; border-right: 0; }
    .comparison-table thead th:nth-child(2), .comparison-table tbody th { background: transparent; text-align: center; width: 24%; }
    .comparison-table thead th:nth-child(1), .comparison-table thead th:nth-child(3) { border-top: 0; }
    .comparison-table tbody td:nth-child(1), .comparison-table tbody td:nth-child(3) { font-size: 1.04rem; }
    .comparison-table tbody tr:hover td { background: rgba(148,163,184,.08); }
    .comparison-table tbody th { color: #e2e8f0; font-weight: 700; }
    .metric-icon { display: none; }
    .country-heading { letter-spacing: .01em; }
    .country-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .14em; display: inline-block; margin-bottom: .35rem; font-weight: 800; }
    .label-a { color: #67e8f9; }
    .label-b { color: #fcd34d; }
    .country-code { font-size: .78rem; color: rgba(255,255,255,.65); font-weight: 600; margin-left: .5rem; }
    .duel-card { display: grid; grid-template-columns: 1fr minmax(150px, .55fr) 1fr; align-items: center; gap: 1rem; background: linear-gradient(135deg, #172536, #111827 50%, #302719); border: 1px solid rgba(148,163,184,.25); border-radius: 1rem; padding: 1.35rem 1.5rem; margin-bottom: 1rem; box-shadow: 0 12px 30px rgba(0,0,0,.2); }
    .duel-side { min-height: 88px; display:flex; flex-direction:column; justify-content:center; }
    .duel-side-a { border-left: 4px solid #22d3ee; padding-left: 1rem; }
    .duel-side-b { border-right: 4px solid #fbbf24; padding-right: 1rem; text-align:right; }
    .duel-side .duel-label { font-size:.7rem; letter-spacing:.15em; font-weight:800; color:rgba(255,255,255,.55); }
    .duel-side-a strong { color:#22d3ee; }
    .duel-side-b strong { color:#fbbf24; }
    .duel-side strong { font-size:1.6rem; line-height:1.2; }
    .duel-side small { color:rgba(255,255,255,.6); margin-top:.25rem; }
    .duel-flag { width: 76px; height: 48px; object-fit: cover; border-radius: .35rem; border: 1px solid rgba(255,255,255,.25); margin-bottom: .55rem; }
    .duel-center { text-align:center; color:rgba(255,255,255,.65); }
    .duel-center .vs { color:#fff; font-size:1.8rem; font-weight:900; letter-spacing:.18em; margin:.15rem 0; }
    @media (max-width: 640px) { .duel-card { grid-template-columns:1fr; text-align:center; } .duel-side-a,.duel-side-b { border:0; border-top:3px solid; padding: .75rem 0 0; text-align:center; } .duel-side-a { border-color:#22d3ee; } .duel-side-b { border-color:#fbbf24; } }
</style>
<div class="min-vh-100 text-white" style="background:#0b0f19;font-family:'Segoe UI',Roboto,sans-serif;">
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="comparison-hero rounded-4 p-4 flex-grow-1"><a href="{{ route('ports.index') }}" class="back-dashboard"><i class="bi bi-arrow-left"></i>Kembali ke dashboard</a><h2 class="fw-bold mb-1 mt-2"><i class="bi bi-bar-chart text-info me-2"></i>Country Comparison Engine</h2><p class="text-white-50 mb-0 small">Bandingkan data aktual dua negara dari sumber API yang sama dengan Risk Score Engine.</p></div>
        </div>

        <form method="GET" action="{{ route('country-comparison.index') }}" class="card comparison-picker border-info border-opacity-25 shadow-lg mb-4 rounded-4"><div class="card-body p-4"><div class="row g-3 align-items-end">
            <div class="col-md-5"><label class="form-label small text-white-50" for="countryA">Negara pertama (Negara A)</label><select id="countryA" name="country_a" class="form-select bg-black border-secondary text-white"><option value="">Pilih negara...</option>@foreach($countries as $country)<option value="{{ $country->code }}" @selected($leftCode === $country->code)>{{ $country->name }} ({{ $country->code }})</option>@endforeach</select></div>
            <div class="col-md-2 text-center text-info fs-4 pb-1 fw-bold">VS</div>
            <div class="col-md-5"><label class="form-label small text-white-50" for="countryB">Negara kedua (Negara B)</label><select id="countryB" name="country_b" class="form-select bg-black border-secondary text-white"><option value="">Pilih negara...</option>@foreach($countries as $country)<option value="{{ $country->code }}" @selected($rightCode === $country->code)>{{ $country->name }} ({{ $country->code }})</option>@endforeach</select></div>
            <div class="col-12 d-grid"><button class="btn btn-info btn-lg"><i class="bi bi-columns-gap me-1"></i>Bandingkan Negara</button></div>
        </div></div></form>

        @if($left && $right)
            <div class="duel-card">
                <div class="duel-side duel-side-a">@if($left['flag_code'])<img class="duel-flag" src="https://flagcdn.com/w80/{{ $left['flag_code'] }}.png" alt="Bendera {{ $left['country']->name }}">@endif<span class="duel-label">NEGARA A</span><strong>{{ $left['country']->name }}</strong><small>{{ $left['country']->code }}</small></div>
                <div class="duel-center"><small>COUNTRY COMPARISON</small><div class="vs">VS</div><small>Data aktual</small></div>
                <div class="duel-side duel-side-b">@if($right['flag_code'])<img class="duel-flag ms-auto" src="https://flagcdn.com/w80/{{ $right['flag_code'] }}.png" alt="Bendera {{ $right['country']->name }}">@endif<span class="duel-label">NEGARA B</span><strong>{{ $right['country']->name }}</strong><small>{{ $right['country']->code }}</small></div>
            </div>
            <div class="card bg-dark border-secondary border-opacity-25 shadow-lg overflow-hidden rounded-4"><div class="table-responsive"><table class="table table-dark table-hover align-middle mb-0 comparison-table" style="min-width:800px;"><thead><tr><th class="text-info fs-5 country-heading">Nilai Negara A</th><th class="text-white-50 text-center">Indikator perbandingan</th><th class="text-warning fs-5 country-heading">Nilai Negara B</th></tr></thead><tbody>
                <tr><td class="fw-semibold">{{ $left['gdp'] !== null ? '$'.number_format($left['gdp'], 0) : 'N/A' }}</td><th><span class="metric-icon bg-success bg-opacity-25 text-success"><i class="bi bi-currency-dollar"></i></span>GDP (USD)</th><td class="fw-semibold">{{ $right['gdp'] !== null ? '$'.number_format($right['gdp'], 0) : 'N/A' }}</td></tr>
                <tr><td>{{ $left['inflation'] !== null ? number_format($left['inflation'], 2).'%' : 'N/A' }}</td><th><span class="metric-icon bg-danger bg-opacity-25 text-danger"><i class="bi bi-graph-down-arrow"></i></span>Inflasi</th><td>{{ $right['inflation'] !== null ? number_format($right['inflation'], 2).'%' : 'N/A' }}</td></tr>
                <tr><td>{{ $left['weather'] ? number_format($left['weather']->temp, 1).' °C · angin '.number_format($left['weather']->wind_speed, 1).' km/h' : 'N/A' }}</td><th><span class="metric-icon bg-info bg-opacity-25 text-info"><i class="bi bi-cloud-sun"></i></span>Cuaca referensi</th><td>{{ $right['weather'] ? number_format($right['weather']->temp, 1).' °C · angin '.number_format($right['weather']->wind_speed, 1).' km/h' : 'N/A' }}</td></tr>
                <tr><td>{{ $left['currency']?->rate_to_usd !== null ? number_format($left['currency']->rate_to_usd, 4).' '.$left['country']->currency_code : 'N/A' }}</td><th><span class="metric-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-cash-coin"></i></span>Kurs (1 USD)</th><td>{{ $right['currency']?->rate_to_usd !== null ? number_format($right['currency']->rate_to_usd, 4).' '.$right['country']->currency_code : 'N/A' }}</td></tr>
                <tr><td class="fw-bold text-info">{{ $left['risk']?->total_score !== null ? number_format($left['risk']->total_score, 1).' ('.$left['risk']->risk_level.')' : 'N/A' }}</td><th><span class="metric-icon bg-primary bg-opacity-25 text-primary"><i class="bi bi-shield-check"></i></span>Risk score</th><td class="fw-bold text-warning">{{ $right['risk']?->total_score !== null ? number_format($right['risk']->total_score, 1).' ('.$right['risk']->risk_level.')' : 'N/A' }}</td></tr>
                <tr><td><span class="text-success">+{{ $left['news']['positive_percentage'] }}%</span> · <span class="text-secondary">{{ $left['news']['neutral_percentage'] }}%</span> · <span class="text-danger">-{{ $left['news']['negative_percentage'] }}%</span></td><th><span class="metric-icon bg-danger bg-opacity-25 text-danger"><i class="bi bi-newspaper"></i></span>News sentiment</th><td><span class="text-success">+{{ $right['news']['positive_percentage'] }}%</span> · <span class="text-secondary">{{ $right['news']['neutral_percentage'] }}%</span> · <span class="text-danger">-{{ $right['news']['negative_percentage'] }}%</span></td></tr>
            </tbody></table></div></div>
        @else
            <div class="card bg-dark border-secondary border-opacity-25 text-center text-white-50 py-5"><i class="bi bi-columns-gap text-info fs-1 mb-3"></i><p class="mb-0">Pilih dua negara untuk memulai perbandingan.</p></div>
        @endif
    </div>
</div>
@endsection
