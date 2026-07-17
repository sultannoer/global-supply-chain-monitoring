<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Services\WeatherService;
use App\Services\WorldBankService;
use App\Services\ExchangeRateService;
use App\Services\MarineTrafficService;
use App\Services\NewsService;
use App\Services\CountryService; // 1. Import CountryService di sini
use Illuminate\Http\Request;

class PortController extends Controller
{
    protected $weatherService;
    protected $worldBankService;
    protected $exchangeRateService;
    protected $marineService;
    protected $newsService;
    protected $countryService; // 2. Tambahkan properti untuk CountryService

    // Inject SEMUA 6 service backend ke dalam Constructor
    public function __construct(
        WeatherService $weatherService,
        WorldBankService $worldBankService,
        ExchangeRateService $exchangeRateService,
        MarineTrafficService $marineService,
        NewsService $newsService,
        CountryService $countryService // 3. Inject di sini
    ) {
        $this->weatherService = $weatherService;
        $this->worldBankService = $worldBankService;
        $this->exchangeRateService = $exchangeRateService;
        $this->marineService = $marineService;
        $this->newsService = $newsService;
        $this->countryService = $countryService; // 4. Inisialisasi properti
    }

    /**
     * Menampilkan daftar semua pelabuhan dengan fitur pencarian (Tetap Aman)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $ports = Port::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('city', 'like', "%{$search}%")
                         ->orWhere('country', 'like', "%{$search}%");
        })->paginate(10);

        return view('ports.index', compact('ports', 'search'));
    }

    /**
     * Menampilkan detail satu pelabuhan BESERTA gabungan seluruh API Live Backend
     */
    public function show($id)
    {
        // 1. Ambil data pelabuhan utama dari database internal
        $port = Port::findOrFail($id);

        // 2. API 1: Cuaca live Open-Meteo berdasarkan koordinat pelabuhan
        $weatherData = $this->weatherService->getWeatherByCoordinates($port->latitude, $port->longitude);

        // 3. API 2: Data Makro Ekonomi World Bank
        $economicData = $this->worldBankService->getCountryData($port->country_code ?? substr($port->country, 0, 2));

        // 4. API 3: Profil Negara & Bendera (REST Countries / Safety-net Fallback)
        $countryData = $this->countryService->getCountryData($port->country_code ?? substr($port->country, 0, 2));

        // 5. API 4: Data Live Kurs Mata Uang terhadap USD
        $currencyCode = $port->currency_code ?? 'IDR';
        $exchangeData = $this->exchangeRateService->getRateAgainstUsd($currencyCode);

        // 6. API 5: Data Hibrida Marine Traffic (Angka Operasional Simulator + Peta Embed Live)
        $radarData = $this->marineService->getPortTrafficData($port->latitude, $port->longitude);

        // 7. API 6: 3 Berita Logistik & Dagang Global Terbaru
        $newsData = $this->newsService->getLatestNews($port->country);

        // Kirim seluruh paket data lengkap ini ke halaman Frontend (View Blade)
        return view('ports.show', compact(
            'port', 
            'weatherData', 
            'economicData', 
            'countryData', // Siap dipakai untuk detail negara & bendera
            'exchangeData', 
            'radarData',   // Sekarang sudah berisi data angka operasional + iframe peta
            'newsData'
        ));
    }
}