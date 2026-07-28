<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\CountryEconomicHistory;
use App\Models\CountryWeatherHistory;
use App\Models\CurrencyRateHistory;
use App\Models\NegativeWord;
use App\Models\NewsSentiment;
use App\Models\Port;
use App\Models\PortWeatherHistory;
use App\Models\RiskScore;
use App\Models\RiskAlert;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Watchlist;
use App\Services\RiskAssessmentService;
use App\Services\SentimentAnalysisService;
use Illuminate\Console\Application as ArtisanApplication;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnalyticsAndApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_api_endpoints_return_local_json_data(): void
    {
        $country = $this->country();
        $port = $this->port($country);
        RiskScore::create([
            'country_code' => $country->code,
            'weather_score' => 10,
            'inflation_score' => 20,
            'exchange_score' => 0,
            'news_score' => 5,
            'exchange_rate' => 16000,
            'total_score' => 12.5,
            'data_coverage' => 100,
            'risk_level' => 'LOW',
        ]);

        $this->getJson('/api/countries')->assertOk()->assertJsonPath('data.0.code', $country->code);
        $this->getJson('/api/ports')->assertOk()->assertJsonPath('data.0.id', $port->id);
        $this->getJson('/api/risk')->assertOk()->assertJsonPath('data.0.country_code', $country->code);

        Http::fake(['https://open.er-api.com/*' => Http::response([
            'base_code' => 'USD',
            'rates' => ['IDR' => 16000],
            'time_last_update_utc' => now()->toIso8601String(),
        ], 200)]);
        $this->getJson('/api/live-metrics')->assertOk()->assertJsonPath('total_nodes', 1);
        $this->getJson('/api/currency')->assertOk()->assertJsonPath('rates.IDR', 16000);
    }

    public function test_external_backed_marker_and_news_endpoints_are_parsed(): void
    {
        $country = $this->country();
        $port = $this->port($country);
        config(['services.gnews.key' => 'test-key']);
        Http::fake([
            'https://api.worldbank.org/*' => Http::response([[], [['value' => 123]]], 200),
            'https://api.open-meteo.com/*' => Http::response(['current' => [
                'temperature_2m' => 27,
                'rain' => 0,
                'wind_speed_10m' => 8,
            ]], 200),
            'https://open.er-api.com/*' => Http::response(['base_code' => 'USD', 'rates' => ['IDR' => 16000]], 200),
            'https://gnews.io/*' => Http::response(['articles' => [[
                'title' => 'Port operations remain stable',
                'description' => 'Shipping services continue normally.',
                'url' => 'https://example.test/article',
                'publishedAt' => now()->toIso8601String(),
                'source' => ['name' => 'Test Wire'],
            ]]], 200),
        ]);

        $this->getJson('/api/live/markers/countries/'.$country->code)
            ->assertOk()->assertJsonPath('data.code', $country->code);
        $this->getJson('/api/live/markers/ports/'.$port->id)
            ->assertOk()->assertJsonPath('data.temp', 27);
        $this->getJson('/api/news')->assertOk()->assertJsonPath('articles.0.title', 'Port operations remain stable');
    }

    public function test_analytics_pages_render_with_snapshot_data(): void
    {
        $country = $this->country();
        $port = $this->port($country);
        CountryEconomicHistory::create(['country_code' => $country->code, 'gdp' => 1000000, 'inflation_rate' => 3.2]);
        CountryWeatherHistory::create(['country_code' => $country->code, 'temp' => 27, 'rain' => 0, 'wind_speed' => 8, 'storm_risk_status' => 'Low', 'risk_score' => 10]);
        CurrencyRateHistory::create(['currency_code' => 'IDR', 'rate_to_usd' => 16000, 'source' => 'test']);
        PortWeatherHistory::create(['port_id' => $port->id, 'temp' => 27, 'rain' => 0, 'wind_speed' => 8, 'storm_risk_status' => 'Low', 'risk_score' => 10]);
        RiskScore::create(['country_code' => $country->code, 'weather_score' => 10, 'inflation_score' => 16, 'exchange_score' => 0, 'news_score' => 0, 'exchange_rate' => 16000, 'total_score' => 7.3, 'data_coverage' => 100, 'risk_level' => 'LOW']);

        config(['services.gnews.key' => 'test-key']);
        Http::fake([
            'https://gnews.io/*' => Http::response(['articles' => []], 200),
            'https://open.er-api.com/*' => Http::response(['base_code' => 'USD', 'rates' => ['IDR' => 16000]], 200),
        ]);

        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get('/risk-scores')->assertOk()->assertSee('Risk Score Engine');
        $this->actingAs($user)->get('/news-sentiment')->assertOk()->assertSee('News Sentiment');
        $this->actingAs($user)->get('/country-comparison?country_a=IDN&country_b=IDN')->assertOk()->assertSee('Country Comparison Engine');
        $this->actingAs($user)->get('/trends?country=IDN&port='.$port->id)->assertOk()->assertSee('Historical Trends');
        $this->actingAs($user)->get('/countries/IDN')->assertOk()->assertSee('Indonesia');
    }

    public function test_watchlist_cargo_and_vessel_actions_work(): void
    {
        $country = $this->country();
        $origin = $this->port($country);
        $destination = Port::create([
            'name' => 'Destination Port', 'country_code' => $country->code,
            'latitude' => -7, 'longitude' => 110, 'storm_risk_status' => 'Low', 'risk_score' => 5,
        ]);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post('/watchlists/IDN/toggle')->assertRedirect();
        $this->assertDatabaseHas('watchlists', ['user_id' => $user->id, 'country_code' => 'IDN']);
        $this->actingAs($user)->delete('/watchlists/IDN')->assertRedirect('/watchlists');
        $this->assertDatabaseMissing('watchlists', ['user_id' => $user->id, 'country_code' => 'IDN']);

        $this->actingAs($user)->get('/cargo/create')->assertOk()->assertSee('Input Cargo');
        $this->actingAs($user)->post('/cargo/store', [
            'origin_port' => $origin->id, 'destination_port' => $destination->id,
            'vessel_id' => $origin->id.'01', 'vessel_name_hidden' => 'TEST-EXPLORER',
            'cargo_weight' => 100, 'currency_value' => 50000,
        ])->assertRedirect('/cargo/create');
        $shipment = Shipment::latest('id')->first();
        $this->assertNotNull($shipment);
        $this->actingAs($user)->post('/cargo/vessel/'.$shipment->id.'/update-coordinates', ['live_lat' => -6.2, 'live_lng' => 106.9])->assertOk()->assertJsonPath('status', 'success');
        $this->assertEquals(-6.2, (float) Shipment::findOrFail($shipment->id)->current_lat);
        $this->actingAs($user)->delete('/cargo/vessel/'.$shipment->id)->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseMissing('shipments', ['id' => $shipment->id]);
    }

    public function test_sentiment_engine_persists_positive_and_negative_classification(): void
    {
        NegativeWord::create(['word' => 'accident']);
        config(['services.gnews.key' => '']);
        app(SentimentAnalysisService::class)->analyzeArticle([
            'title' => 'Port accident causes delay',
            'description' => 'A major accident disrupted cargo operations.',
            'url' => 'https://example.test/negative',
            'publishedAt' => now()->toIso8601String(),
            'source' => ['name' => 'Test Wire'],
        ], 'Test Port');

        $this->assertDatabaseHas('news_sentiments', [
            'query' => 'Test Port', 'sentiment' => 'Negative', 'negative_score' => 2,
        ]);
        $this->assertSame(1, NewsSentiment::count());
    }

    public function test_country_risk_calculation_excludes_missing_news_without_zeroing_other_data(): void
    {
        $country = $this->country();
        $this->port($country);
        config(['services.gnews.key' => '']);
        Http::fake(['https://open.er-api.com/*' => Http::response(['base_code' => 'USD', 'rates' => ['IDR' => 16000]], 200)]);

        $score = app(RiskAssessmentService::class)->calculateCountryRisk($country);

        $this->assertNull($score->news_score);
        $this->assertSame(75, $score->data_coverage);
        $this->assertNotNull($score->total_score);
        $this->assertNotSame('UNKNOWN', $score->risk_level);
    }

    public function test_port_weather_backfill_command_stores_missing_snapshot(): void
    {
        $country = $this->country();
        $port = Port::create([
            'name' => 'Unobserved Port', 'country_code' => $country->code,
            'latitude' => -6, 'longitude' => 107, 'storm_risk_status' => 'Low', 'risk_score' => 0,
        ]);
        Http::fake(['https://api.open-meteo.com/*' => Http::response(['current' => [
            'temperature_2m' => 28, 'rain' => 0, 'wind_speed_10m' => 7,
        ]], 200)]);

        $this->artisan('ports:weather-backfill')->assertExitCode(0);

        $this->assertDatabaseHas('port_weather_histories', ['port_id' => $port->id, 'temp' => 28]);
    }

    public function test_currency_snapshot_command_stores_current_rates(): void
    {
        $this->country();
        Http::fake(['https://open.er-api.com/*' => Http::response([
            'base_code' => 'USD', 'rates' => ['IDR' => 16000],
        ], 200)]);

        $this->artisan('currency:snapshot')->assertExitCode(0);

        $this->assertDatabaseHas('currency_rate_histories', [
            'currency_code' => 'IDR', 'rate_to_usd' => 16000,
        ]);
    }

    public function test_metrics_backfill_command_creates_all_snapshot_types_for_a_country(): void
    {
        $this->country();
        config(['services.gnews.key' => '']);
        Http::fake([
            'https://api.worldbank.org/*' => Http::response([[], [['value' => 123]]], 200),
            'https://api.open-meteo.com/*' => Http::response(['current' => [
                'temperature_2m' => 28, 'rain' => 0, 'wind_speed_10m' => 7,
            ]], 200),
            'https://open.er-api.com/*' => Http::response(['base_code' => 'USD', 'rates' => ['IDR' => 16000]], 200),
        ]);

        $this->artisan('metrics:backfill', ['--limit' => 1])->assertExitCode(0);

        $this->assertDatabaseHas('country_economic_histories', ['country_code' => 'IDN']);
        $this->assertDatabaseHas('country_weather_histories', ['country_code' => 'IDN', 'temp' => 28]);
        $this->assertDatabaseHas('currency_rate_histories', ['currency_code' => 'IDR']);
        $this->assertDatabaseHas('risk_scores', ['country_code' => 'IDN']);
    }

    public function test_missing_api_resources_return_expected_not_found_responses(): void
    {
        $this->getJson('/api/live/markers/countries/XXX')->assertNotFound();
        $this->getJson('/api/live/markers/ports/999999')->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => 'user']))->get('/countries/XXX')->assertNotFound();
    }

    public function test_risk_alerts_older_than_24_hours_are_purged(): void
    {
        $country = $this->country();
        $port = $this->port($country);

        $expired = RiskAlert::create([
            'port_id' => $port->id,
            'alert_level' => 'CRITICAL',
            'risk_type' => 'WEATHER',
            'message' => 'Expired alert',
            'is_resolved' => false,
        ]);
        $expired->forceFill(['created_at' => now()->subHours(25)])->saveQuietly();

        $recent = RiskAlert::create([
            'port_id' => $port->id,
            'alert_level' => 'WARNING',
            'risk_type' => 'WEATHER',
            'message' => 'Recent alert',
            'is_resolved' => false,
        ]);

        $this->artisan('risk-alerts:purge')->assertExitCode(0);

        $this->assertDatabaseMissing('risk_alerts', ['id' => $expired->id]);
        $this->assertDatabaseHas('risk_alerts', ['id' => $recent->id]);
        $this->assertSame(1, RiskAlert::active()->count());
    }

    private function country(): Country
    {
        return Country::create([
            'code' => 'IDN',
            'alpha2_code' => 'ID',
            'name' => 'Indonesia',
            'region' => 'Asia',
            'currency_code' => 'IDR',
            'language' => 'Indonesian',
            'latitude' => -2.5,
            'longitude' => 118.0,
            'gdp' => 1000000,
            'inflation_rate' => 3.2,
            'population' => 100000,
            'export_volume' => 500000,
            'import_volume' => 450000,
        ]);
    }

    private function port(Country $country): Port
    {
        return Port::create([
            'name' => 'Test Port',
            'country_code' => $country->code,
            'latitude' => -6.1,
            'longitude' => 106.8,
            'temp' => 27,
            'rain' => 0,
            'wind_speed' => 8,
            'storm_risk_status' => 'Low',
            'risk_score' => 10,
        ]);
    }
}
