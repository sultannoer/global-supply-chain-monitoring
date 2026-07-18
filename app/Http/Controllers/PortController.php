<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Services\WeatherService;
use App\Services\EconomicService; 
use App\Services\ExchangeRateService;
use App\Services\MarineTrafficService;
use App\Services\NewsService;
use App\Services\CountryService; 
use Illuminate\Http\Request;

class PortController extends Controller
{
    protected $weatherService;
    protected $economicService; 
    protected $exchangeRateService;
    protected $marineService;
    protected $newsService;
    protected $countryService; 

    public function __construct(
        WeatherService $weatherService,
        EconomicService $economicService, 
        ExchangeRateService $exchangeRateService,
        MarineTrafficService $marineService,
        NewsService $newsService,
        CountryService $countryService 
    ) {
        $this->weatherService = $weatherService;
        $this->economicService = $economicService; 
        $this->exchangeRateService = $exchangeRateService;
        $this->marineService = $marineService;
        $this->newsService = $newsService;
        $this->countryService = $countryService; 
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

      
        $ports = Port::with('country')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                             ->orWhere('port_code', 'like', "%{$search}%")
                             ->orWhereHas('country', function ($q) use ($search) {
                                 $q->where('name', 'like', "%{$search}%");
                             });
            })->paginate(10);

        return view('ports.index', compact('ports', 'search'));
    }

    public function show($id)
    {
       
        $port = Port::with(['country', 'destinationShipments'])->findOrFail($id);

       
        $derivedCountryCode = $port->country->code ?? 'ID'; 
        $derivedCountryName = $port->country->name ?? 'Indonesia';
        $currencyCode = $port->country->currency_code ?? 'IDR';

       
        $weatherData = $this->weatherService->getWeatherByCoordinates($port->latitude, $port->longitude);
        $economicData = $this->economicService->getEconomicData($derivedCountryCode);
        $countryData = $this->countryService->getCountryData($derivedCountryCode);
        $exchangeData = $this->exchangeRateService->getRateAgainstUsd($currencyCode);
        $radarData = $this->marineService->getPortTrafficData($port->latitude, $port->longitude);
        $newsData = $this->newsService->getLatestNews($derivedCountryName);

        
        return view('ports.show', compact(
            'port', 'weatherData', 'economicData', 'countryData', 
            'exchangeData', 'radarData', 'newsData', 'currencyCode'
        ));
    }
}