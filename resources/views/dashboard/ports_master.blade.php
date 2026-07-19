@extends('layouts.app')

@section('content')
<div class="container-fluid p-4 bg-dark text-white min-vh-100" style="font-family: 'Segoe UI', Roboto, sans-serif; background-color: #0f1115 !important;">
    
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 border-bottom border-secondary border-opacity-25 pb-3">
        <div>
            <h4 class="mb-1 fw-bold text-warning"><i class="bi bi-anchor text-warning me-2"></i> Ports Master Node Matrix</h4>
            <p class="mb-0 text-white-50 small">Manajemen pangkalan data terpusat, sinkronisasi kliring forex, dan risiko meteorologi pelabuhan global.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-white">
                <i class="bi bi-arrow-left"></i> Live Radar Map
            </a>
            <a href="{{ url('/api/live-metrics') }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                <i class="bi bi-code-slash"></i> View Raw JSON API
            </a>
        </div>
    </div>

  
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="p-3 bg-black bg-opacity-40 border border-secondary border-opacity-25 rounded-3">
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Total Terdaftar</small>
                <h3 class="fw-bold my-1 text-primary">{{ count($metricsData) }} Pangkalan Node</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-black bg-opacity-40 border border-secondary border-opacity-25 rounded-3">
                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Sinkronisasi Waktu Server</small>
                <h6 class="fw-bold font-monospace text-success my-2">{{ now()->format('d M Y - H:i:s') }} WITA</h6>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-3 bg-black bg-opacity-40 border border-secondary border-opacity-25 rounded-3">
                <input type="text" id="tableSearchInput" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill mt-1" placeholder="Cari nama pelabuhan, kode, atau status...">
            </div>
        </div>
    </div>

    <div class="card bg-black bg-opacity-30 border border-secondary border-opacity-25 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap table-dark" id="portsMasterTable">
                <thead class="table-light bg-secondary bg-opacity-10 border-bottom border-secondary border-opacity-25 text-muted font-monospace" style="font-size: 11px;">
                    <tr>
                        <th class="ps-3 py-3 text-white-50">ID</th>
                        <th class="text-white-50">NAMA PELABUHAN / NODE</th>
                        <th class="text-white-50">KOORDINAT GPS</th>
                        <th class="text-white-50">METEOROLOGI STATUS</th>
                        <th class="text-white-50">KURS LIVE FOREX (VS USD)</th>
                        <th class="text-white-50">KEAMANAN & INTELLIGENCE DATA</th>
                    </tr>
                </thead>
                <tbody style="font-size: 12.5px;">
                    @foreach($metricsData as $node)
                    <tr class="border-bottom border-secondary border-opacity-10">
                        <td class="ps-3 fw-bold font-monospace text-muted">#{{ $node['port_id'] }}</td>
                        <td>
                            <div class="fw-bold text-white">{{ $node['name'] }}</div>
                            <small class="text-muted font-monospace">Code: {{ $node['code'] }}</small>
                        </td>
                        <td class="font-monospace text-info">
                            Lat: {{ $node['coordinates']['latitude'] }}<br>
                            Lng: {{ $node['coordinates']['longitude'] }}
                        </td>
                        <td>
                            <span class="d-block">Suhu: <strong class="text-warning">{{ $node['climate']['temperature_celsius'] }}°C</strong></span>
                            <small class="text-muted d-block">Angin: {{ $node['climate']['wind_speed_kmh'] }} km/h</small>
                            <span class="badge bg-{{ $node['climate']['storm_risk'] !== 'Low' ? 'danger' : 'success' }} bg-opacity-10 text-{{ $node['climate']['storm_risk'] !== 'Low' ? 'danger' : 'success' }} border border-{{ $node['climate']['storm_risk'] !== 'Low' ? 'danger' : 'success' }} border-opacity-25 py-0.5 px-2 mt-1" style="font-size: 10px;">
                                Badai: {{ $node['climate']['storm_risk'] }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold text-success font-monospace">
                                1 USD = {{ number_format($node['finance']['exchange_rate_vs_usd'], 2) }} {{ $node['finance']['currency'] }}
                            </div>
                            <small class="text-muted text-uppercase" style="font-size: 10px;">Kliring Aktif</small>
                        </td>
                        <td style="max-width: 300px; white-space: normal; line-height: 1.4;">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 mb-1 d-inline-block" style="font-size: 9px; font-family: monospace;">GNEWS SHIELD</span>
                            <p class="mb-0 text-white-50 small" style="font-size: 11.5px; font-style: italic;">"{{ $node['security_intelligence'] }}"</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        T 
        var searchInput = document.getElementById('tableSearchInput');
        var table = document.getElementById('portsMasterTable');
        var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            var filter = searchInput.value.toLowerCase();
            for (var i = 0; i < rows.length; i++) {
                var rowText = rows[i].textContent.toLowerCase();
                if (rowText.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    });
</script>
@endpush
