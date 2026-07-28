<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Port;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PortController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $totalPorts = Port::count();
        $countryCount = Port::query()->distinct('country_code')->count('country_code');
        $highRiskCount = Port::where('risk_score', '>=', 60)->count();
        $weatherReady = Port::whereNotNull('temp')->whereNotNull('wind_speed')->whereNotNull('rain')->count();
        $ports = Port::with('country:code,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('country_code', 'like', "%{$search}%")
                ->orWhereHas('country', fn ($country) => $country->where('name', 'like', "%{$search}%"))))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $weatherCoverage = $totalPorts > 0 ? round(($weatherReady / $totalPorts) * 100) : 0;

        return view('admin.ports.index', compact(
            'ports', 'search', 'totalPorts', 'countryCount', 'highRiskCount', 'weatherReady', 'weatherCoverage'
        ));
    }

    public function create(): View
    {
        return view('admin.ports.create', ['countries' => $this->countries()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Port::create($this->validated($request));

        return redirect()->route('admin.ports.index')->with('success', 'Dataset pelabuhan berhasil ditambahkan.');
    }

    public function edit(Port $port): View
    {
        return view('admin.ports.edit', ['port' => $port, 'countries' => $this->countries()]);
    }

    public function update(Request $request, Port $port): RedirectResponse
    {
        $port->update($this->validated($request));

        return redirect()->route('admin.ports.index')->with('success', 'Dataset pelabuhan berhasil diperbarui.');
    }

    public function destroy(Port $port): RedirectResponse
    {
        if ($port->inboundShipments()->exists() || $port->outboundShipments()->exists()) {
            return back()->with('error', 'Pelabuhan tidak dapat dihapus karena masih digunakan oleh data shipment.');
        }

        $port->delete();

        return back()->with('success', 'Dataset pelabuhan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:3', 'exists:countries,code'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'temp' => ['nullable', 'numeric', 'between:-100,100'],
            'rain' => ['nullable', 'numeric', 'min:0'],
            'wind_speed' => ['nullable', 'numeric', 'min:0'],
            'storm_risk_status' => ['required', Rule::in(['Low', 'Medium', 'High'])],
            'risk_score' => ['required', 'integer', 'between:0,100'],
        ]);
    }

    private function countries()
    {
        return Country::orderBy('name')->get(['code', 'name']);
    }
}
