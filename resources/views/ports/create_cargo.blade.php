@extends('layouts.app')

@section('content')
<div class="container-fluid p-0 bg-dark text-white min-vh-100 overflow-x-hidden" style="font-family: 'Segoe UI', Roboto, sans-serif;">
    <div class="row g-0 min-vh-100">
        
        
        <div class="col-lg-2 bg-black bg-opacity-50 border-end border-secondary border-opacity-25 d-flex flex-column justify-content-between p-3" style="min-height: 100vh;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4 px-2">
                    <i class="bi bi-shield-shaded text-primary fs-3"></i>
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
            </div>

          
            <div class="p-4 flex-grow-1" style="background-color: #0f1115;">
                
                @if(session('success'))
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success d-flex align-items-center gap-2 mb-4 shadow-sm animate__animated animate__fadeIn">
                        <i class="bi bi-check-circle-fill"></i>
                        <div class="fw-bold" style="font-family: monospace; font-size: 12px;">{{ session('success') }}</div>
                    </div>
                @endif

                <div class="row g-3 mb-4" style="font-family: 'Courier New', monospace;">
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-black bg-opacity-30 border border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block" style="font-size: 10px;">ESTIMASI BIAYA BAHAN BAKAR</small>
                                <span class="fw-bold fs-5 text-white" id="fuel-cost">$0</span>
                            </div>
                            <i class="bi bi-fuel-pump text-white-50 fs-3"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-black bg-opacity-30 border border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block" style="font-size: 10px;">ASURANSI RISIKO PELAYANAN</small>
                                <span class="fw-bold fs-5 text-warning" id="insurance-cost">$0</span>
                            </div>
                            <i class="bi bi-shield-check text-warning fs-3"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-black bg-opacity-30 border border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block" style="font-size: 10px;">PROYEKSI PENDAPATAN BERSIH</small>
                                <span class="fw-bold fs-5 text-success" id="net-profit">$0</span>
                            </div>
                            <i class="bi bi-graph-up-arrow text-success fs-3"></i>
                        </div>
                    </div>
                </div>

                
                <div class="bg-black bg-opacity-30 border border-secondary border-opacity-25 rounded p-4 shadow-sm">
                    <form action="{{ route('cargo.store') }}" method="POST" id="cargoForm">
                        @csrf
                        
                      
                        <input type="hidden" name="vessel_name_hidden" id="vessel_name_hidden">

                        <h6 class="text-primary text-uppercase small fw-bold tracking-wider mb-4 border-bottom border-secondary border-opacity-10 pb-2">
                            <i class="bi bi-sliders"></i> Konfigurasi Penjadwalan Muatan Internasional
                        </h6>

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
                                <label class="form-label small fw-semibold text-white-50">Valuasi Devisa Komoditas ($ USD)</label>
                                <input type="number" id="currency_value" name="currency_value" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Masukkan harga komparasi barang..." min="1" required>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2.5 rounded shadow fw-bold text-uppercase border-0">
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
    .hover-light:hover { background-color: rgba(255, 255, 255, 0.05); color: #ffffff !important; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var vesselsMap = @json($vesselsByPort);
        
        var originSelect = document.getElementById('origin_port');
        var vesselSelect = document.getElementById('vessel_id');
        var hiddenVesselName = document.getElementById('vessel_name_hidden');
        
        var inputWeight = document.getElementById('cargo_weight');
        var inputValue = document.getElementById('currency_value');
        
        var fuelCostEl = document.getElementById('fuel-cost');
        var insuranceCostEl = document.getElementById('insurance-cost');
        var netProfitEl = document.getElementById('net-profit');

        
        originSelect.addEventListener('change', function () {
            var selectedPortId = this.value;
            
            vesselSelect.innerHTML = '';
            vesselSelect.disabled = false;
            
            if (vesselsMap[selectedPortId] && vesselsMap[selectedPortId].length > 0) {
                vesselsMap[selectedPortId].forEach(function (vessel) {
                    var option = document.createElement('option');
                    option.value = vessel.id;
                    option.text = vessel.name;
                    vesselSelect.appendChild(option);
                });
            } else {
                var defaultVessels = ['GLOBAL-VOYAGER X (Ready Stand-by)', 'PACIFIC-CARGO TRADER (Ready Stand-by)', 'ATLANTIC-SHUTTLE (Ready Stand-by)'];
                defaultVessels.forEach(function (name, index) {
                    var option = document.createElement('option');
                    option.value = '999' + index;
                    option.text = name;
                    vesselSelect.appendChild(option);
                });
            }
            
            if(vesselSelect.options.length > 0) {
                hiddenVesselName.value = vesselSelect.options[0].text;
            }
        });

        vesselSelect.addEventListener('change', function() {
            if(this.selectedIndex >= 0) {
                hiddenVesselName.value = this.options[this.selectedIndex].text;
            }
        });

        function calculateSimulation() {
            var weight = parseFloat(inputWeight.value) || 0;
            var currencyValue = parseFloat(inputValue.value) || 0;

            var fuelCost = weight * 14.2; 
            var insuranceCost = currencyValue * 0.0018; 
            var grossRevenue = weight * 45; 
            var netProfit = grossRevenue - fuelCost; 

            fuelCostEl.innerText = '$' + fuelCost.toLocaleString('en-US', {maximumFractionDigits: 0});
            insuranceCostEl.innerText = '$' + insuranceCost.toLocaleString('en-US', {maximumFractionDigits: 0});
            netProfitEl.innerText = '$' + (netProfit > 0 ? netProfit.toLocaleString('en-US', {maximumFractionDigits: 0}) : '0');
        }

        inputWeight.addEventListener('input', calculateSimulation);
        inputValue.addEventListener('input', calculateSimulation);
    });
</script>
@endpush
