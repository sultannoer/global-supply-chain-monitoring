@extends('layouts.app')

@section('content')
<div class="container py-4">
    @php
        $countryName = $port->country->name ?? 'Indonesia';
        $dbCode = $port->country->code ?? 'id';
        $dbCode = strtolower($dbCode);

        if ($dbCode === 'idn' || $dbCode === 'id') {
            $cCode = 'id';
        } elseif ($dbCode === 'chn' || $dbCode === 'cn') {
            $cCode = 'cn';
        } else {
            $cCode = substr($dbCode, 0, 2);
        }

        $liveRate = $exchangeData['rate_against_usd'] ?? $exchangeData['rate'] ?? null;
    @endphp

    <div class="card shadow-sm border-0 mb-4 bg-dark text-white p-4" style="border-radius: 12px;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-primary mb-2">Maritime Port Dashboard</span>
                <h1 class="h2 mb-1 fw-bold">{{ $port->name }}</h1>
                <p class="mb-0 text-white-50">
                    <i class="bi bi-geo-alt-fill"></i> 
                    {{ $port->name }}, {{ $countryName }} 
                    <span class="ms-2">({{ $port->latitude }}, {{ $port->longitude }})</span>
                </p>
            </div>
            
            <div class="d-flex align-items-center gap-3 bg-secondary bg-opacity-25 p-3 rounded border border-secondary" style="min-width: 280px; max-width: 350px;">
                <div class="position-relative shadow-sm rounded border border-secondary bg-secondary d-flex align-items-center justify-content-center text-uppercase fw-bold text-white" style="width: 65px; height: 40px; font-size: 0.85rem; overflow: hidden; flex-shrink: 0;">
                    <span class="position-absolute">{{ $cCode }}</span>
                    <img src="https://flagcdn.com/w160/{{ $cCode }}.png" alt="Flag" style="width: 100%; height: 100%; object-fit: cover; z-index: 2;" onerror="this.style.display='none'">
                </div>
                
                <div>
                    <h6 class="mb-0 text-warning fw-bold text-truncate" style="font-size: 0.9rem; max-width: 220px;">{{ $cCode === 'cn' ? "People's Republic of China" : ($cCode === 'id' ? "Republic of Indonesia" : $countryName) }}</h6>
                    <small class="d-block text-white-50" style="font-size: 0.8rem;">{{ $cCode === 'cn' ? "Eastern Asia" : ($cCode === 'id' ? "Southeast Asia" : "Global Maritime Region") }}</small>
                    <small class="d-block text-white-50" style="font-size: 0.75rem;">
                        <i class="bi bi-translate text-info"></i> {{ $cCode === 'cn' ? "Chinese (Mandarin)" : ($cCode === 'id' ? "Indonesian (Bahasa)" : "Local Language") }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Status Kepadatan</h6>
                    <span class="badge bg-{{ $radarData['warna_status'] ?? 'secondary' }} fs-6 py-2 px-3 mt-2 rounded-pill">
                        {{ $radarData['status_kepadatan'] ?? 'Unknown' }}
                    </span>
                    <small class="d-block text-muted mt-3"><i class="bi bi-clock"></i> Estimasi: <strong>{{ $radarData['estimasi_antrean_sandar'] ?? '-' }}</strong></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Kapal Aktif</h6>
                    <h3 class="fw-bold text-primary mt-2">{{ $radarData['total_kapal_aktif'] ?? '0' }}</h3>
                    <small class="text-muted">Jarak Pandang: {{ $radarData['jarak_pandang_laut'] ?? '-' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">Breakdown Komposisi Armada</h6>
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <span class="d-block fw-bold text-dark fs-5">{{ $radarData['kargo_kontainer'] ?? 0 }}</span>
                            <small class="text-muted text-uppercase small d-block" style="font-size: 0.75rem;">Kargo/Kontainer</small>
                        </div>
                        <div class="col-4 border-end">
                            <span class="d-block fw-bold text-dark fs-5">{{ $radarData['tanker_minyak'] ?? 0 }}</span>
                            <small class="text-muted text-uppercase small d-block" style="font-size: 0.75rem;">Tanker Gas/Minyak</small>
                        </div>
                        <div class="col-4">
                            <span class="d-block fw-bold text-dark fs-5">{{ $radarData['kapal_tunda'] ?? 0 }}</span>
                            <small class="text-muted text-uppercase small d-block" style="font-size: 0.75rem;">Kapal Tunda/Logistik</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
        <div class="card-header bg-transparent border-0 pt-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="bi bi-box-seam-fill text-warning"></i> Manifest Ekspedisi Aktif & Pelacakan Otomatis</h5>
            <span class="badge bg-secondary">Total: {{ $port->destinationShipments->count() }} Kontainer</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>No. Kontainer</th>
                            <th>Tgl Keberangkatan</th>
                            <th>ETA Awal</th>
                            <th>ETA Adaptif (Live Radar)</th>
                            <th>Biaya Awal (USD)</th>
                            <th>Estimasi Biaya Saat Ini</th>
                            <th>Core Risk Scoring</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($port->destinationShipments as $shipment)
                            <tr>
                                <td class="fw-bold text-primary">{{ $shipment->shipment_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($shipment->departure_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shipment->estimated_arrival_date)->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $adaptiveEta = \Carbon\Carbon::parse($shipment->estimated_arrival_date);
                                        $isCongested = isset($radarData['status_kepadatan']) && stripos($radarData['status_kepadatan'], 'High') !== false;
                                        if($isCongested || $shipment->risk_status === 'HIGH') {
                                            $adaptiveEta->addDays(2);
                                        }
                                    @endphp
                                    <span class="{{ ($isCongested || $shipment->risk_status === 'HIGH') ? 'text-danger fw-bold text-decoration-blink' : 'text-success fw-bold' }}">
                                        {{ $adaptiveEta->format('d M Y') }}
                                        @if($isCongested || $shipment->risk_status === 'HIGH') <small class="d-block text-muted" style="font-size: 11px;">(Penundaan Rute Terdeteksi)</small> @endif
                                    </span>
                                </td>
                                <td>${{ number_format($shipment->initial_cost, 2) }}</td>
                                <td>
                                    @php
                                        $costAdjustment = $liveRate ? ($shipment->initial_cost * ($liveRate / 16000)) : $shipment->initial_cost;
                                    @endphp
                                    <span class="fw-bold text-dark">${{ number_format($costAdjustment, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $shipment->risk_status === 'HIGH' ? 'danger' : ($shipment->risk_status === 'MEDIUM' ? 'warning' : 'success') }} p-2">
                                        {{ $shipment->risk_status }}
                                    </span>
                                    <small class="d-block text-wrap text-muted mt-1" style="max-width: 200px; font-size: 11px;">
                                        {{ $shipment->risk_reason }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inboxes fs-2 d-block mb-2"></i>
                                    Tidak ada kontainer/ekspedisi yang sedang aktif menuju ke pelabuhan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-cloud-sun text-info"></i> Live Weather Forecast</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @if(isset($weatherData) && isset($weatherData['hourly']['time']) && count($weatherData['hourly']['time']) > 0)
                        <div style="position: relative; height:280px; width:100%">
                            <canvas id="weatherChart"></canvas>
                        </div>
                    @else
                        <div class="alert alert-light text-center border p-4 my-auto">
                            <i class="bi bi-cloud-slash fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted fw-bold">Data Cuaca Live Sedang Tidak Tersedia</h6>
                            <small class="text-muted">Koneksi ke satelit cuaca terputus atau batas API tercapai.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-cash-stack text-success"></i> Market Intelligence (Devisa & Ekonomi)</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="bg-light p-3 rounded mb-3 border border-light-subtle">
                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem;">Live Currency Rate</small>
                        <h4 class="fw-bold mb-1">
                            1 USD = {{ $liveRate ? number_format($liveRate, 2) : 'N/A' }} {{ $currencyCode }}
                        </h4>
                        <small class="text-muted small">Update: {{ $exchangeData['last_update'] ?? $exchangeData['date'] ?? 'API Unreachable' }}</small>
                    </div>

                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted ps-0">PDB / GDP Negara:</td>
                                <td class="text-end fw-bold text-dark">{{ $port->country->gdp ?? $economicData['gdp'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Tingkat Inflasi:</td>
                                <td class="text-end fw-bold text-danger">{{ isset($port->country->inflation_rate) ? $port->country->inflation_rate . '%' : ($economicData['inflasi'] ?? 'N/A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-radar text-danger"></i> Live AIS Vessel Radar</h5>
                </div>
                <div class="card-body p-2">
                    <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm" style="height: 400px;">
                        {!! $radarData['html_iframe'] ?? '<div class="alert alert-warning m-3">Radar widget tidak tersedia.</div>' !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title fw-bold mb-0"><i class="bi bi-map-fill text-primary"></i> Geographic Mapping</h5>
                </div>
                <div class="card-body p-2">
                    <div id="leafletMap" class="rounded shadow-sm" style="height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-transparent border-0 pt-3">
            <h5 class="card-title fw-bold mb-0"><i class="bi bi-newspaper text-warning"></i> Geopolitical News Feed</h5>
        </div>
        <div class="card-body">
            @php
                $articles = [];
                if (isset($newsData) && is_array($newsData)) {
                    $articles = $newsData['articles'] ?? $newsData['data'] ?? $newsData['news'] ?? (!isset($newsData['success']) ? $newsData : []);
                }
            @endphp

            @if(count($articles) > 0)
                <div class="row g-3">
                    @foreach(array_slice($articles, 0, 3) as $article)
                        <div class="col-md-4">
                            <div class="card h-100 border border-light-subtle bg-light bg-opacity-50">
                                <div class="card-body d-flex flex-column justify-content-between p-3">
                                    <div>
                                        <h6 class="card-title fw-bold text-dark line-clamp-2 mb-2">
                                            {{ $article['title'] ?? 'No Title' }}
                                        </h6>
                                        <p class="card-text text-muted small line-clamp-3 mb-3">
                                            {{ $article['description'] ?? 'Tidak ada deskripsi.' }}
                                        </p>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            {{ $article['source']['name'] ?? 'Global News' }}
                                        </small>
                                        <a href="{{ $article['url'] ?? '#' }}" target="_blank" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 0.75rem;">
                                            Baca <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light text-center border py-4 mb-0">
                    <i class="bi bi-info-circle fs-4 text-muted d-block mb-2"></i>
                    <p class="mb-0 text-muted">Tidak ada berita logistik perdagangan terbaru untuk wilayah ini.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    #leafletMap { z-index: 1; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var lat = {{ $port->latitude }};
        var lng = {{ $port->longitude }};
        var map = L.map('leafletMap').setView([lat, lng], 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup("<strong>{{ $port->name }}</strong><br>{{ $countryName }}").openPopup();

        @if(isset($weatherData) && isset($weatherData['hourly']['time']) && count($weatherData['hourly']['time']) > 0)
            var hourlyLabels = @json(array_slice($weatherData['hourly']['time'], 0, 7));
            var tempData = @json(array_slice($weatherData['hourly']['temperature_2m'] ?? [], 0, 7));
            var windData = @json(array_slice($weatherData['hourly']['wind_speed_10m'] ?? [], 0, 7));

            var formattedLabels = hourlyLabels.map(function(datetime) {
                return datetime.substring(11, 16);
            });

            var ctx = document.getElementById('weatherChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: formattedLabels,
                    datasets: [
                        {
                            label: 'Suhu Udara (°C)',
                            data: tempData,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3,
                            yAxisID: 'yTemp'
                        },
                        {
                            label: 'Kecepatan Angin (km/h)',
                            data: windData,
                            borderColor: 'rgba(255, 159, 64, 1)',
                            backgroundColor: 'rgba(255, 159, 64, 0)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.1,
                            yAxisID: 'yWind'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        yTemp: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Suhu (°C)' } },
                        yWind: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Angin (km/h)' } }
                    }
                }
            });
        @endif
    });
</script>
@endpush
@endsection