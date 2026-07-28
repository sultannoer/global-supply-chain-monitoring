@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 bg-dark text-white min-vh-100 overflow-x-hidden cargo-estimator-theme" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    <div class="row g-0 min-vh-100">
        
        
        <div class="col-lg-2 bg-black bg-opacity-50 border-end border-secondary border-opacity-25 d-flex flex-column justify-content-between p-3" style="min-height: 100vh;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4 px-2">
                    <i class="bi bi-anchor-fill text-info fs-3"></i>
                    <span class="fs-4 fw-bold tracking-wider text-uppercase text-white">⚓ GeoPort Analytics
</span>
                </div>
                <ul class="nav flex-column gap-1">
                    <li class="nav-item">
                        <a class="nav-link text-white-50 hover-light rounded d-flex align-items-center gap-3 px-3 py-2.5 small" href="{{ url('/') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Live Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active rounded bg-primary text-white d-flex align-items-center gap-3 px-3 py-2.5 small fw-semibold" href="{{ route('cargo.create') }}">
                            <i class="bi bi-box-seam"></i> Input Cargo
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    
        <div class="col-lg-10 d-flex flex-column h-100" style="min-height: 100vh;">
           
            <div class="bg-black bg-opacity-25 border-bottom border-secondary border-opacity-25 px-4 py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-box-seam text-primary me-2"></i>Automated Logistics Router</h5>
                <small class="text-white-50 text-uppercase" style="font-size: 11px;">Pilih pelabuhan asal untuk memanggil armada kapal yang sedang bersandar secara real-time</small>
                <div class="tactical-strip">
                    <span><i class="bi bi-broadcast-pin text-info"></i> Routing <strong>LIVE</strong></span>
                    <span><i class="bi bi-shield-check text-success"></i> Model <strong>DETERMINISTIC</strong></span>
                    <span><i class="bi bi-clock text-warning"></i> Sync <strong>{{ now()->format('H:i') }}</strong></span>
                </div>
            </div>

          
            <div class="p-4 flex-grow-1" style="background-color: #0f1115;">
                
                @if(session('success'))
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success d-flex align-items-center gap-2 mb-4 shadow-sm animate__animated animate__fadeIn">
                        <i class="bi bi-check-circle-fill"></i>
                        <div class="fw-bold" style="font-family: monospace; font-size: 12px;">{{ session('success') }}</div>
                    </div>
                @endif
                @if(session('estimate_summary'))
                    <div class="alert bg-info bg-opacity-10 border border-info border-opacity-25 text-info mb-4 font-monospace small">
                        <i class="bi bi-calculator me-2"></i>{{ session('estimate_summary') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger mb-4 small">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
                    </div>
                @endif

                <div class="row g-3 mb-4" style="font-family: 'Courier New', monospace;">
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-black bg-opacity-30 border border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="cargo-card-label d-block">ESTIMASI BAHAN BAKAR</small>
                                <span class="fw-bold fs-5 text-white" id="fuel-cost">$0</span>
                                <small class="cargo-card-note d-block">Rute × konsumsi kapal × harga BBM</small>
                            </div>
                            <i class="bi bi-fuel-pump text-white-50 fs-3"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-black bg-opacity-30 border border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="cargo-card-label cargo-card-label-warning d-block">PREMI ASURANSI KARGO</small>
                                <span class="fw-bold fs-5 text-warning" id="insurance-cost">$0</span>
                                <small class="cargo-card-note d-block">Nilai kargo × tarif risiko</small>
                            </div>
                            <i class="bi bi-shield-check text-warning fs-3"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-black bg-opacity-30 border border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="cargo-card-label cargo-card-label-success d-block">PROYEKSI MARGIN BERSIH</small>
                                <span class="fw-bold fs-5 text-success" id="net-profit">$0</span>
                                <small class="cargo-card-note d-block">Pendapatan − seluruh biaya</small>
                            </div>
                            <i class="bi bi-graph-up-arrow text-success fs-3"></i>
                        </div>
                    </div>
                </div>

                <div id="voyage-estimate-meta" class="small text-info bg-info bg-opacity-10 border border-info border-opacity-25 rounded p-3 mb-4 font-monospace">
                    Pilih port asal, kapal, port tujuan, berat muatan, dan nilai kargo untuk melihat asumsi estimasi deterministik.
                </div>

                
                <div class="cargo-form-panel bg-black bg-opacity-30 border border-secondary border-opacity-25 rounded p-4 shadow-sm">
                    <form action="{{ route('cargo.store') }}" method="POST" id="cargoForm">
                        @csrf
                        
                      
                        <input type="hidden" name="vessel_name_hidden" id="vessel_name_hidden">

                        <div class="d-flex align-items-center justify-content-between gap-3 border-bottom border-secondary border-opacity-10 pb-2 mb-3">
                            <h6 class="text-primary text-uppercase small fw-bold tracking-wider mb-0">
                                <i class="bi bi-sliders"></i> Konfigurasi Penjadwalan Muatan Internasional
                            </h6>
                            <span class="cargo-step-badge">ROUTE PLANNER · STEP 1/3</span>
                        </div>

                        <div class="cargo-flow mb-4" aria-label="Alur input manifest">
                            <div class="cargo-flow-step is-active" data-target="origin_port" role="button" tabindex="0"><span>01</span><strong>ORIGIN</strong><small>Pilih pelabuhan asal</small></div>
                            <i class="bi bi-chevron-right cargo-flow-arrow"></i>
                            <div class="cargo-flow-step" data-target="vessel_id" role="button" tabindex="0"><span>02</span><strong>VESSEL</strong><small>Hubungkan armada</small></div>
                            <i class="bi bi-chevron-right cargo-flow-arrow"></i>
                            <div class="cargo-flow-step" data-target="destination_port" role="button" tabindex="0"><span>03</span><strong>DESTINATION</strong><small>Tentukan tujuan</small></div>
                        </div>

                        <div class="row g-3 mb-3">
                            
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-white-50">1. Pelabuhan Asal (Origin Port)</label>
                                <select name="origin_port" id="origin_port" class="form-select bg-dark border-secondary border-opacity-25 text-white" required>
                                    <option value="" disabled selected>Pilih Pelabuhan Asal...</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}">{{ $port->name }} ({{ $port->country->code ?? 'INT' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-white-50">2. Armada Kapal yang Bersandar (Available Vessel)</label>
                                <select name="vessel_id" id="vessel_id" class="form-select bg-dark border-secondary border-opacity-25 text-white" disabled required>
                                    <option value="" disabled selected>Pilih Pelabuhan Asal Dahulu...</option>
                                </select>
                                <div id="vessel-status" class="cargo-field-status"><i class="bi bi-info-circle"></i> Menunggu pelabuhan asal.</div>
                                <div id="vessel-capacity" class="cargo-capacity-readout"><i class="bi bi-box-seam"></i> Kapasitas: pilih armada untuk melihat batas tonase.</div>
                            </div>


                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-white-50">3. Pelabuhan Tujuan (Destination Port)</label>
                                <select name="destination_port" id="destination_port" class="form-select bg-dark border-secondary border-opacity-25 text-white" required>
                                    <option value="" disabled selected>Pilih Pelabuhan Tujuan...</option>
                                    @foreach($ports as $port)
                                        <option value="{{ $port->id }}">{{ $port->name }} ({{ $port->country->code ?? 'INT' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-white-50">Total Bobot Muatan Kargo (Ton)</label>
                                <input type="number" id="cargo_weight" name="cargo_weight" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Masukkan berat tonase..." min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-white-50">Nilai Kargo / Commodity Value ($ USD)</label>
                                <input type="number" id="currency_value" name="currency_value" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Masukkan harga komparasi barang..." min="1" required>
                                <div class="cargo-field-status"><i class="bi bi-info-circle"></i> Nilai ini menentukan premi asuransi, bukan kapasitas kapal.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" id="cargo-submit" class="btn btn-primary px-4 py-2.5 rounded shadow fw-bold text-uppercase border-0">
                                <i class="bi bi-file-earmark-plus-fill me-2"></i> Daftarkan Manifest Ke Radar Pelayaran
                            </button>
                            <a href="{{ route('ports.index') }}" class="back-dashboard">
                                <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .cargo-estimator-theme { position:relative; isolation:isolate; }
    .cargo-estimator-theme::after { content:""; position:absolute; inset:-100% 0; pointer-events:none; z-index:3; opacity:.04; background:repeating-linear-gradient(0deg, transparent 0, transparent 4px, rgba(255,255,255,.035) 5px); animation:cargo-scan 18s linear infinite; }
    .cargo-estimator-theme > .row { position:relative; z-index:1; }
    .cargo-estimator-theme .tactical-strip { display:flex; flex-wrap:wrap; gap:.5rem .8rem; margin-top:.85rem; padding:.6rem .75rem; border:1px solid rgba(34,211,238,.2); border-radius:.45rem; background:linear-gradient(90deg, rgba(8,47,73,.65), rgba(15,23,42,.5)); font:600 .66rem/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; }
    .cargo-estimator-theme .tactical-strip span { display:inline-flex; align-items:center; gap:.35rem; }
    .cargo-estimator-theme .tactical-strip strong { color:#67e8f9; }
    .cargo-estimator-theme .cargo-step-badge { flex:0 0 auto; padding:.28rem .55rem; border:1px solid rgba(34,211,238,.35); border-radius:.35rem; color:#67e8f9; background:rgba(8,47,73,.45); font:700 .62rem/1 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:.08em; }
    .cargo-estimator-theme .cargo-flow { display:flex; align-items:center; gap:.55rem; padding:.75rem .85rem; border:1px solid rgba(148,163,184,.16); border-radius:.5rem; background:rgba(2,6,23,.42); }
    .cargo-estimator-theme .cargo-flow-step { min-width:0; display:grid; grid-template-columns:auto 1fr; column-gap:.5rem; align-items:center; color:#64748b; }
    .cargo-estimator-theme .cargo-flow-step span { grid-row:span 2; display:grid; place-items:center; width:1.8rem; height:1.8rem; border:1px solid rgba(148,163,184,.28); border-radius:50%; font:700 .68rem ui-monospace, SFMono-Regular, Menlo, monospace; }
    .cargo-estimator-theme .cargo-flow-step strong { font:700 .67rem/1.1 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:.08em; }
    .cargo-estimator-theme .cargo-flow-step small { font-size:.68rem; color:#64748b; white-space:nowrap; }
    .cargo-estimator-theme .cargo-flow-step { cursor:pointer; transition:color .2s ease, transform .2s ease; }
    .cargo-estimator-theme .cargo-flow-step:hover { color:#bae6fd; transform:translateY(-1px); }
    .cargo-estimator-theme .cargo-flow-step.is-active { color:#67e8f9; }
    .cargo-estimator-theme .cargo-flow-step.is-active span { border-color:#22d3ee; color:#22d3ee; box-shadow:0 0 12px rgba(34,211,238,.2); }
    .cargo-estimator-theme .cargo-flow-step.is-active strong { text-shadow:0 0 12px rgba(34,211,238,.35); }
    .cargo-estimator-theme .cargo-flow-arrow { color:#475569; font-size:.8rem; }
    .cargo-estimator-theme .cargo-field-status { min-height:1.1rem; margin-top:.4rem; color:#64748b; font:600 .66rem/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; }
    .cargo-estimator-theme .cargo-field-status.is-ready { color:#34d399; }
    .cargo-estimator-theme .cargo-capacity-readout { margin-top:.35rem; color:#94a3b8; font:600 .66rem/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; }
    .cargo-estimator-theme .cargo-capacity-readout strong { color:#facc15; }
    .cargo-estimator-theme .cargo-capacity-readout.is-over { color:#fda4af; }
    .cargo-estimator-theme .cargo-card-label { color:#67e8f9 !important; font:700 .63rem/1.2 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:.06em; }
    .cargo-estimator-theme .cargo-card-label-warning { color:#facc15 !important; }
    .cargo-estimator-theme .cargo-card-label-success { color:#34d399 !important; }
    .cargo-estimator-theme .cargo-card-note { margin-top:.2rem; color:#94a3b8; font-size:.58rem; line-height:1.2; }
    @keyframes cargo-scan { from { transform:translateY(-18%); } to { transform:translateY(18%); } }
    .hover-light:hover { background-color: rgba(255, 255, 255, 0.05); color: #ffffff !important; }
    .cargo-estimator-theme { background-color:#080d18 !important; background-image:linear-gradient(rgba(34,211,238,.022) 1px, transparent 1px),linear-gradient(90deg, rgba(34,211,238,.022) 1px, transparent 1px),radial-gradient(circle at 70% 0%, rgba(8,145,178,.13), transparent 36rem); background-size:42px 42px,42px 42px,100% 100%; }
    .cargo-estimator-theme .row.g-3 > [class*="col-"] > div, .cargo-estimator-theme form { background-image:linear-gradient(145deg, rgba(30,41,59,.9), rgba(15,23,42,.94)) !important; border-color:rgba(56,189,248,.2) !important; box-shadow:0 12px 28px rgba(0,0,0,.24), inset 0 1px 0 rgba(125,211,252,.06); transition:transform .2s ease, border-color .2s ease, box-shadow .2s ease; }
    .cargo-estimator-theme .cargo-form-panel { position:relative; overflow:hidden; background-color:#0d172a !important; background-image:linear-gradient(145deg, rgba(30,64,105,.96) 0%, rgba(15,35,65,.97) 48%, rgba(8,22,44,.99) 100%) !important; border-color:rgba(56,189,248,.3) !important; box-shadow:0 14px 34px rgba(0,0,0,.34), inset 0 1px 0 rgba(125,211,252,.1), 0 0 0 1px rgba(15,23,42,.65); transition:border-color .25s ease, box-shadow .25s ease, transform .25s ease; }
    .cargo-estimator-theme .cargo-form-panel::before { content:""; position:absolute; inset:0; pointer-events:none; opacity:0; background:linear-gradient(115deg, transparent 20%, rgba(34,211,238,.1) 45%, transparent 70%); transform:translateX(-100%); transition:opacity .25s ease, transform .7s ease; }
    .cargo-estimator-theme .cargo-form-panel:hover { border-color:rgba(34,211,238,.65) !important; box-shadow:0 18px 42px rgba(0,0,0,.42), 0 0 24px rgba(34,211,238,.08), inset 0 1px 0 rgba(125,211,252,.15); transform:translateY(-1px); }
    .cargo-estimator-theme .cargo-form-panel:hover::before { opacity:1; transform:translateX(100%); }
    .cargo-estimator-theme .cargo-form-panel form { background:transparent !important; border-color:transparent !important; box-shadow:none !important; }
    .cargo-estimator-theme .row.g-3 > [class*="col-"] > div:hover { transform:translateY(-3px); border-color:rgba(34,211,238,.6) !important; box-shadow:0 16px 34px rgba(0,0,0,.34), 0 0 18px rgba(34,211,238,.08); }
    .cargo-estimator-theme .form-control, .cargo-estimator-theme .form-select { transition:border-color .2s ease, box-shadow .2s ease; }
    .cargo-estimator-theme .form-control:focus, .cargo-estimator-theme .form-select:focus { border-color:#22d3ee !important; box-shadow:0 0 0 .2rem rgba(34,211,238,.13) !important; }
    .cargo-estimator-theme #voyage-estimate-meta { background-image:linear-gradient(90deg, rgba(8,47,73,.7), rgba(15,23,42,.45)); transition:border-color .25s ease, box-shadow .25s ease; }
    .cargo-estimator-theme #voyage-estimate-meta.is-live { border-color:rgba(52,211,153,.5) !important; color:#6ee7b7 !important; box-shadow:0 0 18px rgba(52,211,153,.08); }
    .cargo-estimator-theme .form-control:hover, .cargo-estimator-theme .form-select:hover { border-color:rgba(56,189,248,.55) !important; }
    .cargo-estimator-theme .btn { transition:transform .18s ease, box-shadow .18s ease, filter .18s ease; }
    .cargo-estimator-theme .btn:hover { transform:translateY(-2px); filter:brightness(1.1); box-shadow:0 0 16px rgba(34,211,238,.2); }
    @media (max-width: 640px) { .cargo-estimator-theme .cargo-flow { align-items:stretch; flex-direction:column; } .cargo-estimator-theme .cargo-flow-arrow { transform:rotate(90deg); align-self:center; } .cargo-estimator-theme .cargo-step-badge { display:none; } }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var vesselsMap = @json($vesselsByPort);
        var portMetrics = @json($portMetrics);
        var estimationConfig = @json($estimationConfig);
        
        var originSelect = document.getElementById('origin_port');
        var destinationSelect = document.getElementById('destination_port');
        var vesselSelect = document.getElementById('vessel_id');
        var hiddenVesselName = document.getElementById('vessel_name_hidden');
        var vesselStatus = document.getElementById('vessel-status');
        var vesselCapacityEl = document.getElementById('vessel-capacity');
        var submitButton = document.getElementById('cargo-submit');
        
        var inputWeight = document.getElementById('cargo_weight');
        var inputValue = document.getElementById('currency_value');
        
        var fuelCostEl = document.getElementById('fuel-cost');
        var insuranceCostEl = document.getElementById('insurance-cost');
        var netProfitEl = document.getElementById('net-profit');
        var estimateMetaEl = document.getElementById('voyage-estimate-meta');
        var flowSteps = document.querySelectorAll('.cargo-flow-step');
        var vesselManuallyChosen = false;

        function refreshFlow() {
            var values = [originSelect.value, vesselSelect.value, destinationSelect.value];
            flowSteps.forEach(function (step, index) {
                step.classList.toggle('is-active', Boolean(values[index]));
            });
        }

        flowSteps.forEach(function (step) {
            function focusTarget() {
                var target = document.getElementById(step.dataset.target);
                if (target && !target.disabled) target.focus();
            }
            step.addEventListener('click', focusTarget);
            step.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    focusTarget();
                }
            });
        });

        
        originSelect.addEventListener('change', function () {
            var selectedPortId = this.value;
            vesselManuallyChosen = false;
            
            vesselSelect.innerHTML = '';
            vesselSelect.disabled = false;
            
            if (vesselsMap[selectedPortId] && vesselsMap[selectedPortId].length > 0) {
                vesselsMap[selectedPortId].forEach(function (vessel) {
                    var option = document.createElement('option');
                    option.value = vessel.id;
                    option.text = vessel.name;
                    vesselSelect.appendChild(option);
                });
                if (vesselStatus) {
                    vesselStatus.classList.add('is-ready');
                    vesselStatus.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + vesselSelect.options.length + ' armada siap dipilih dari pelabuhan ini.';
                }
            } else {
                var defaultVessels = ['GLOBAL-VOYAGER X (Ready Stand-by)', 'PACIFIC-CARGO TRADER (Ready Stand-by)', 'ATLANTIC-SHUTTLE (Ready Stand-by)'];
                defaultVessels.forEach(function (name, index) {
                    var option = document.createElement('option');
                    option.value = '999' + index;
                    option.text = name;
                    vesselSelect.appendChild(option);
                });
                if (vesselStatus) {
                    vesselStatus.classList.add('is-ready');
                    vesselStatus.innerHTML = '<i class="bi bi-broadcast-pin"></i> Armada standby tersedia untuk skenario rute.';
                }
            }
            
            if(vesselSelect.options.length > 0) {
                hiddenVesselName.value = vesselSelect.options[0].text;
            }
            calculateSimulation();
            refreshFlow();
        });

        vesselSelect.addEventListener('change', function() {
            vesselManuallyChosen = true;
            if(this.selectedIndex >= 0) {
                hiddenVesselName.value = this.options[this.selectedIndex].text;
            }
            calculateSimulation();
            refreshFlow();
        });

        function calculateSimulation() {
            var weight = parseFloat(inputWeight.value) || 0;
            var currencyValue = parseFloat(inputValue.value) || 0;

            var origin = portMetrics[originSelect.value], destination = portMetrics[destinationSelect.value];
            if (!vesselManuallyChosen && vesselSelect.options.length > 0 && weight > 0) {
                var preferredToken = weight <= 20000 ? 'MARU' : (weight <= 60000 ? 'EXPLORER' : 'EXPRESS');
                var suggested = Array.from(vesselSelect.options).find(function (option) {
                    return option.text.toUpperCase().includes(preferredToken);
                });
                if (suggested) {
                    vesselSelect.value = suggested.value;
                    hiddenVesselName.value = suggested.text;
                }
            }
            var vesselName = vesselSelect.options[vesselSelect.selectedIndex]?.text || '';
            var profile = vesselName.toUpperCase().includes('EXPRESS') || vesselName.toUpperCase().includes('BULK')
                ? { className: 'Bulk Carrier', capacity: 80000, speed: 14, fuelDay: 45, freight: 35 }
                : (vesselName.toUpperCase().includes('MARU') || vesselName.toUpperCase().includes('GENERAL')
                    ? { className: 'General Cargo', capacity: 20000, speed: 16, fuelDay: 38, freight: 38 }
                    : { className: 'Container Vessel', capacity: 60000, speed: 18, fuelDay: 60, freight: 45 });
            var capacityValid = !vesselName || weight <= profile.capacity;
            inputWeight.max = profile.capacity;
            if (vesselCapacityEl) {
                vesselCapacityEl.classList.toggle('is-over', !capacityValid);
                vesselCapacityEl.innerHTML = vesselName
                    ? '<i class="bi bi-box-seam"></i> ' + profile.className + ' · kapasitas maksimum <strong>' + profile.capacity.toLocaleString('en-US') + ' ton</strong>' + (capacityValid ? '' : ' · MUATAN MELEBIHI KAPASITAS')
                    : '<i class="bi bi-box-seam"></i> Kapasitas: pilih armada untuk melihat batas tonase.';
            }
            if (submitButton) submitButton.disabled = !capacityValid;
            var fuelCost = 0, insuranceCost = 0, portFees = 0, grossRevenue = 0, netProfit = 0, routeKm = 0, transitDays = 0;
            if (origin && destination && Number.isFinite(Number(origin.lat)) && Number.isFinite(Number(destination.lat))) {
                var rad = Math.PI / 180, dLat = (destination.lat - origin.lat) * rad, dLng = (destination.lng - origin.lng) * rad;
                var a = Math.sin(dLat / 2) ** 2 + Math.cos(origin.lat * rad) * Math.cos(destination.lat * rad) * Math.sin(dLng / 2) ** 2;
                routeKm = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)) * estimationConfig.sea_route_factor;
                transitDays = Math.max(.5, (routeKm * .539957) / profile.speed / 24);
                fuelCost = transitDays * profile.fuelDay * estimationConfig.fuel_price_usd_per_ton;
                var riskMap = estimationConfig.risk_multipliers || {}, riskMultiplier = Math.max(riskMap[origin.storm_risk_status] || 1, riskMap[destination.storm_risk_status] || 1);
                insuranceCost = currencyValue > 0 ? Math.max(currencyValue * estimationConfig.insurance_base_rate * riskMultiplier, estimationConfig.insurance_minimum_usd || 0) : 0;
                portFees = currencyValue > 0 ? currencyValue * estimationConfig.port_fee_rate : 0;
                grossRevenue = weight > 0 ? weight * profile.freight : 0;
                netProfit = weight > 0 && currencyValue > 0 && capacityValid ? grossRevenue - fuelCost - insuranceCost - portFees : 0;
            }

            fuelCostEl.innerText = routeKm > 0 ? '$' + fuelCost.toLocaleString('en-US', {maximumFractionDigits: 0}) : '—';
            insuranceCostEl.innerText = currencyValue > 0 ? '$' + insuranceCost.toLocaleString('en-US', {maximumFractionDigits: 0}) : '—';
            var estimateReady = routeKm > 0 && weight > 0 && currencyValue > 0 && capacityValid;
            netProfitEl.innerText = estimateReady ? '$' + netProfit.toLocaleString('en-US', {maximumFractionDigits: 0}) : '—';
            netProfitEl.classList.toggle('text-danger', estimateReady && netProfit < 0);
            netProfitEl.classList.toggle('text-success', estimateReady && netProfit >= 0);
            netProfitEl.classList.toggle('text-white', !estimateReady);
            if (estimateMetaEl) {
                estimateMetaEl.classList.toggle('is-live', estimateReady);
                estimateMetaEl.innerHTML = routeKm > 0
                    ? 'ROUTE <strong>' + routeKm.toLocaleString('en-US', {maximumFractionDigits: 0}) + ' km</strong> · TRANSIT <strong>' + transitDays.toFixed(1) + ' hari</strong> · KAPAL <strong>' + profile.className + '</strong> · FUEL <strong>$' + estimationConfig.fuel_price_usd_per_ton + '/ton</strong> · PORT FEE <strong>' + (estimationConfig.port_fee_rate * 100).toFixed(1) + '%</strong>'
                    : 'Pilih port asal, kapal, port tujuan, berat muatan, dan nilai kargo untuk melihat asumsi estimasi deterministik.';
            }
        }

        inputWeight.addEventListener('input', calculateSimulation);
        inputValue.addEventListener('input', calculateSimulation);
        document.getElementById('destination_port').addEventListener('change', function () {
            calculateSimulation();
            refreshFlow();
        });
        if (originSelect.value) {
            originSelect.dispatchEvent(new Event('change'));
        } else {
            calculateSimulation();
        }
        refreshFlow();
    });
</script>
@endpush
