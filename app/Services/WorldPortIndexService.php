<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldPortIndexService
{
    /** WPI country labels that differ from REST Countries display names. */
    private const COUNTRY_ALIASES = [
        'BOLIVIA' => 'BOL', 'BRUNEI' => 'BRN', 'CAPE VERDE' => 'CPV',
        'CZECH REPUBLIC' => 'CZE', 'IRAN' => 'IRN', 'KOREA, NORTH' => 'PRK',
        'KOREA, SOUTH' => 'KOR', 'LAOS' => 'LAO', 'MOLDOVA' => 'MDA',
        'RUSSIA' => 'RUS', 'SYRIA' => 'SYR', 'TAIWAN' => 'TWN',
        'TANZANIA' => 'TZA', 'UNITED STATES' => 'USA', 'VENEZUELA' => 'VEN',
        'VIETNAM' => 'VNM',
    ];

    private string $lastSource = 'fallback';

    /** @var array<string, string|null> */
    private array $resolvedCountryCodes = [];

    /** @return array<int, array<string, mixed>> */
    public function fetchPorts(int $limit = 0): array
    {
        // A zero limit means "all records".  The WPI feature service limits
        // each individual response, therefore asking for a large record count
        // alone only returns its first page.
        $limit = max(0, $limit);

        $apiPorts = [];

        try {
            $client = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->timeout(config('services.world_port_index.timeout', 30))
                ->retry(2, 500);

            if (!config('services.world_port_index.verify_ssl', false)) {
                $client = $client->withoutVerifying();
            }

            $pageSize = max(1, min((int) config('services.world_port_index.page_size', 1000), 2000));
            $offset = 0;
            $ports = [];

            do {
                $response = $client->get(config('services.world_port_index.query_url'), [
                    'where' => '1=1',
                    'outFields' => '*',
                    'returnGeometry' => 'true',
                    'f' => 'json',
                    'resultOffset' => $offset,
                    'resultRecordCount' => $pageSize,
                    'orderByFields' => 'OBJECTID ASC',
                ]);

                if (! $response->successful() || ! is_array($response->json('features'))) {
                    break;
                }

                $features = $response->json('features');
                if ($features === []) {
                    break;
                }

                foreach ($features as $feature) {
                    $port = $this->normaliseFeature($feature);

                    if ($port !== null) {
                        $ports[] = $port;
                    }

                    if ($limit > 0 && count($ports) >= $limit) {
                        break 2;
                    }
                }

                $offset += count($features);
                $hasMore = (bool) $response->json('exceededTransferLimit', false)
                    || count($features) === $pageSize;
            } while ($hasMore);

            $apiPorts = collect($ports)
                ->unique(fn (array $port) => strtoupper($port['country_code']).'|'.strtoupper($port['name']).'|'.$port['latitude'].'|'.$port['longitude'])
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            Log::warning('World Port Index API unavailable; trying the WPI CSV download.', ['message' => $exception->getMessage()]);
        }

        $csvPorts = $this->fetchPortsFromCsv($limit);
        if ($csvPorts !== []) {
            // The feature viewer can return only a partial subset. Merge it
            // with the complete official CSV so countries such as USA retain
            // every WPI port rather than a single viewer record.
            $ports = collect(array_merge($apiPorts, $csvPorts))
                ->unique(fn (array $port) => strtoupper($port['country_code']).'|'.strtoupper($port['name']).'|'.$port['latitude'].'|'.$port['longitude'])
                ->values();
            if ($limit > 0) {
                $ports = $ports->take($limit)->values();
            }

            $this->lastSource = $apiPorts === [] ? 'world-port-index-csv' : 'world-port-index-merged';

            return $ports->all();
        }

        if ($apiPorts !== []) {
            $this->lastSource = 'world-port-index';

            return $apiPorts;
        }

        $this->lastSource = 'fallback';
        return $this->loadFallbackPorts();
    }

    /** @return array{countries: int, ports: int, source: string} */
    public function sync(int $limit = 0): array
    {
        $countryCodes = [];
        $portCount = 0;

        foreach ($this->fetchPorts($limit) as $portData) {
            $countryCode = $portData['country_code'];

            // Negara sudah dimuat lebih dahulu (251 negara). Jangan membuat
            // negara "UNK" akibat record WPI yang tidak bisa dipetakan.
            $country = Country::withoutGlobalScopes()->where('code', $countryCode)->first();

            if (!$country) {
                continue;
            }

            $countryCodes[$countryCode] = true;

            Port::withoutGlobalScopes()->updateOrCreate(
                [
                    'name' => $portData['name'],
                    'country_code' => $countryCode,
                    // WPI can contain ports with the same name in a country.
                    // Coordinates distinguish those real, separate locations.
                    'latitude' => $portData['latitude'],
                    'longitude' => $portData['longitude'],
                ],
                []
            );
            $portCount++;
        }

        return ['countries' => count($countryCodes), 'ports' => $portCount, 'source' => $this->lastSource];
    }

    public function exportFallbackPorts(): int
    {
        $ports = Port::withoutGlobalScopes()
            ->with('country')
            ->orderBy('country_code')
            ->orderBy('name')
            ->get()
            ->map(fn (Port $port) => [
                'port' => $port->name,
                'code' => $port->country_code,
                'country' => $port->country?->name ?? $port->country_code,
                'lat' => (float) $port->latitude,
                'lon' => (float) $port->longitude,
            ])
            ->values()
            ->all();

        $file = database_path(config('services.world_port_index.fallback_file', 'ports.json'));
        File::put(
            $file,
            json_encode($ports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        return count($ports);
    }

    /** Remove stale local entries for one country after a full WPI source comparison. */
    public function reconcileCountry(string $countryCode): int
    {
        $countryCode = strtoupper($countryCode);
        $sourceKeys = collect($this->fetchPorts())
            ->where('country_code', $countryCode)
            ->mapWithKeys(fn (array $port) => [$this->portKey($port['name'], $port['latitude'], $port['longitude']) => true])
            ->all();

        if ($sourceKeys === []) {
            return 0;
        }

        $removed = 0;
        $seen = [];
        foreach (Port::withoutGlobalScopes()->where('country_code', $countryCode)->orderBy('id')->get() as $port) {
            $key = $this->portKey($port->name, $port->latitude, $port->longitude);
            $isDuplicate = isset($seen[$key]);
            $seen[$key] = true;

            if (! $isDuplicate && isset($sourceKeys[$key])) {
                continue;
            }

            // Preserve ports that are referenced by an actual shipment.
            if ($port->inboundShipments()->exists() || $port->outboundShipments()->exists()) {
                continue;
            }

            $port->delete();
            $removed++;
        }

        return $removed;
    }

    private function portKey(string $name, float|string $latitude, float|string $longitude): string
    {
        return strtoupper(trim($name)).'|'.number_format((float) $latitude, 8, '.', '').'|'.number_format((float) $longitude, 8, '.', '');
    }

    private function normaliseFeature(array $feature): ?array
    {
        $attributes = array_change_key_case($feature['attributes'] ?? [], CASE_LOWER);
        $geometry = $feature['geometry'] ?? [];

        // Parsing variatif nama pelabuhan
        $name = $attributes['main_port_name']
            ?? $attributes['main_port_']
            ?? $attributes['port_name']
            ?? $attributes['portname']
            ?? $attributes['name']
            ?? $attributes['port']
            ?? null;

        // Parsing variatif nama negara
        $country = $attributes['country']
            ?? $attributes['country_name']
            ?? $attributes['countryname']
            ?? $attributes['nation']
            ?? null;

        // Parsing variatif kode negara
        $code = $attributes['countrycode']
            ?? $attributes['country_code']
            ?? $attributes['iso3']
            ?? $attributes['iso_3']
            ?? $attributes['code']
            ?? null;

        $unlocode = $attributes['unlocode'] ?? null;

        // Parsing koordinat (Mendukung Arcgis geometry & attribute flat)
        $longitude = $geometry['x'] ?? $attributes['longitude'] ?? $attributes['lon'] ?? null;
        $latitude = $geometry['y'] ?? $attributes['latitude'] ?? $attributes['lat'] ?? null;

        // Resolusi Kode ISO3
        $iso3 = $this->resolveIso3Code($code, $country, $unlocode) 
            ?? (strlen((string) $code) === 3 ? strtoupper((string) $code) : null);

        if (!$name || !$iso3 || !is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }

        return [
            'name' => trim((string) $name),
            'country_code' => $iso3,
            'country_name' => trim((string) ($country ?? 'Unknown')),
            'latitude' => round((float) $latitude, 8),
            'longitude' => round((float) $longitude, 8),
        ];
    }

    /**
     * The NGA viewer is occasionally unavailable from application servers.
     * Its complete WPI CSV is used as a secondary source, not as a replacement
     * for the local fallback. The CSV contains several thousand port records.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchPortsFromCsv(int $limit): array
    {
        $url = config('services.world_port_index.csv_url');
        if (! is_string($url) || $url === '') {
            return [];
        }

        try {
            $response = Http::timeout(config('services.world_port_index.timeout', 30))
                ->retry(2, 500)
                ->get($url);

            if (! $response->successful() || trim($response->body()) === '') {
                return [];
            }

            $lines = preg_split('/\r\n|\r|\n/', $response->body()) ?: [];
            $header = array_map(
                fn (string $column) => trim((string) preg_replace(
                    '/[^a-z0-9]+/',
                    '_',
                    strtolower(trim(str_replace("\xEF\xBB\xBF", '', $column)))
                ), '_'),
                str_getcsv((string) array_shift($lines))
            );

            $ports = [];
            foreach ($lines as $line) {
                if (trim($line) === '') {
                    continue;
                }

                $row = str_getcsv($line);
                if (count($row) !== count($header)) {
                    continue;
                }

                $attributes = array_combine($header, $row);
                $port = $this->normaliseFeature(['attributes' => $attributes]);

                if ($port !== null) {
                    $ports[] = $port;
                }

                if ($limit > 0 && count($ports) >= $limit) {
                    break;
                }
            }

            return collect($ports)
                ->unique(fn (array $port) => strtoupper($port['country_code']).'|'.strtoupper($port['name']).'|'.$port['latitude'].'|'.$port['longitude'])
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            Log::warning('World Port Index CSV unavailable; using local dataset.', ['message' => $exception->getMessage()]);

            return [];
        }
    }

    private function resolveIso3Code(mixed $candidate, mixed $countryName, mixed $unlocode): ?string
    {
        $candidate = strtoupper(trim((string) $candidate));
        
        // 1. Jika sudah 3 huruf ISO3
        if (preg_match('/^[A-Z]{3}$/', $candidate)) {
            return $candidate;
        }

        // 2. Use exact country-name matching first. A broad LIKE here mapped
        // "United States" to "United States Minor Outlying Islands" (UMI).
        if ($candidate !== '') {
            if (array_key_exists($candidate, $this->resolvedCountryCodes)) {
                return $this->resolvedCountryCodes[$candidate];
            }

            if (isset(self::COUNTRY_ALIASES[$candidate])) {
                return $this->resolvedCountryCodes[$candidate] = self::COUNTRY_ALIASES[$candidate];
            }

            $dbCountry = Country::withoutGlobalScopes()
                ->where('code', $candidate)
                ->orWhereRaw('LOWER(name) = ?', [strtolower($candidate)])
                ->first();

            if ($dbCountry) {
                return $this->resolvedCountryCodes[$candidate] = $dbCountry->code;
            }
        }

        // 3. Resolve a separately supplied country name using the same exact
        // rule; only then use a broad fallback for longer descriptive labels.
        if ($countryName) {
            $cName = strtoupper(trim((string) $countryName));
            if (isset(self::COUNTRY_ALIASES[$cName])) {
                return $this->resolvedCountryCodes[$candidate] = self::COUNTRY_ALIASES[$cName];
            }

            $dbCountry = Country::withoutGlobalScopes()
                ->whereRaw('LOWER(name) = ?', [strtolower($cName)])
                ->first();

            if (! $dbCountry && strlen($cName) > 3) {
                $dbCountry = Country::withoutGlobalScopes()
                    ->where('name', 'LIKE', "%{$cName}%")
                    ->first();
            }

            if ($dbCountry) {
                return $this->resolvedCountryCodes[$candidate] = $dbCountry->code;
            }
        }

        return null;
    }

    private function loadFallbackPorts(): array
    {
        $file = database_path(config('services.world_port_index.fallback_file', 'ports.json'));
        
        if (!File::exists($file)) {
            Log::error('World Port Index fallback file is missing.', ['file' => $file]);
            return [];
        }

        return collect(json_decode(File::get($file), true) ?: [])
            ->map(function (array $item): ?array {
                // 1. Ambil nama pelabuhan (Mendukung 'port' atau 'name')
                $name = $item['port'] ?? $item['name'] ?? null;
                
                // 2. Ambil kode ISO3 negara
                $code = strtoupper(trim((string) ($item['code'] ?? $item['country_code'] ?? 'UNK')));
                
                // 3. Ambil nama negara (Mendukung 'country' atau 'raw_country')
                $countryName = $item['country'] ?? $item['raw_country'] ?? $code;

                // 4. Ambil koordinat Latitude & Longitude
                $lat = $item['lat'] ?? $item['latitude'] ?? null;
                $lon = $item['lon'] ?? $item['longitude'] ?? null;

                // Validasi minimal: Nama pelabuhan dan koordinat harus ada
                if (empty($name) || !is_numeric($lat) || !is_numeric($lon)) {
                    return null;
                }

                return [
                    'name' => trim((string) $name),
                    'country_code' => $code !== '' ? $code : 'UNK',
                    'country_name' => trim((string) $countryName),
                    'latitude' => (float) $lat,
                    'longitude' => (float) $lon,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
