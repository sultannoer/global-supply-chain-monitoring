<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Country;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_port_and_article_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $country = Country::create([
            'code' => 'IDN', 'name' => 'Indonesia', 'region' => 'Asia',
            'currency_code' => 'IDR', 'language' => 'Indonesian',
        ]);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Operator User', 'email' => 'operator@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'user',
        ])->assertRedirect('/admin/users');

        $this->actingAs($admin)->post('/admin/ports', [
            'name' => 'Admin Port', 'country_code' => $country->code,
            'latitude' => -6.1, 'longitude' => 106.8, 'temp' => 27,
            'rain' => 0, 'wind_speed' => 8, 'storm_risk_status' => 'Low', 'risk_score' => 10,
        ])->assertRedirect('/admin/ports');

        $this->actingAs($admin)->post('/admin/articles', [
            'title' => 'Supply Chain Update', 'category' => 'Logistics',
            'sentiment' => 'Neutral', 'status' => 'published',
            'summary' => 'Summary', 'content' => 'Article content',
        ])->assertRedirect('/admin/articles');

        $this->assertDatabaseHas('users', ['email' => 'operator@example.test', 'role' => 'user']);
        $this->assertDatabaseHas('ports', ['name' => 'Admin Port', 'country_code' => 'IDN']);
        $this->assertDatabaseHas('articles', ['title' => 'Supply Chain Update', 'user_id' => $admin->id]);
        $this->assertSame(1, Port::where('name', 'Admin Port')->count());
        $this->assertSame(1, Article::where('title', 'Supply Chain Update')->count());
    }

    public function test_admin_can_update_and_delete_port_and_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $country = Country::create([
            'code' => 'IDN', 'name' => 'Indonesia', 'region' => 'Asia',
            'currency_code' => 'IDR', 'language' => 'Indonesian',
        ]);
        $port = Port::create([
            'name' => 'Old Port', 'country_code' => 'IDN', 'latitude' => -6,
            'longitude' => 106, 'storm_risk_status' => 'Low', 'risk_score' => 5,
        ]);
        $article = Article::create([
            'user_id' => $admin->id, 'title' => 'Old Article', 'slug' => 'old-article',
            'category' => 'Logistics', 'sentiment' => 'Neutral', 'status' => 'draft', 'content' => 'Old content',
        ]);

        $this->actingAs($admin)->put('/admin/ports/'.$port->id, [
            'name' => 'Updated Port', 'country_code' => $country->code, 'latitude' => -6.2,
            'longitude' => 106.8, 'storm_risk_status' => 'Medium', 'risk_score' => 25,
        ])->assertRedirect('/admin/ports');
        $this->actingAs($admin)->put('/admin/articles/'.$article->id, [
            'title' => 'Updated Article', 'category' => 'Risk', 'sentiment' => 'Negative',
            'status' => 'published', 'content' => 'Updated content',
        ])->assertRedirect('/admin/articles');

        $this->assertDatabaseHas('ports', ['id' => $port->id, 'name' => 'Updated Port', 'risk_score' => 25]);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'title' => 'Updated Article', 'sentiment' => 'Negative']);
        $this->actingAs($admin)->delete('/admin/articles/'.$article->id)->assertRedirect();
        $this->actingAs($admin)->delete('/admin/ports/'.$port->id)->assertRedirect();
        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('ports', ['id' => $port->id]);
    }
}
