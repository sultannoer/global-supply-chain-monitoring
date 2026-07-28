<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_main_radar(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_admin_can_open_dashboard_and_management_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/ports')->assertOk();
        $this->actingAs($admin)->get('/admin/articles')->assertOk();
    }

    public function test_regular_user_cannot_open_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_authenticated_user_can_open_main_radar(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/')->assertOk();
    }

    public function test_admin_and_user_roles_use_their_selected_login_flow(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin-login@example.test']);
        $user = User::factory()->create(['role' => 'user', 'email' => 'user-login@example.test']);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'role' => 'admin',
        ])->assertRedirect('/admin');

        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'user',
        ])->assertRedirect('/');
    }

    public function test_admin_login_after_visiting_main_url_still_opens_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->get('/')->assertRedirect('/login');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'role' => 'admin',
        ])->assertRedirect('/admin');
    }

    public function test_guest_can_register_a_user_account(): void
    {
        $this->get('/register')->assertOk();

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'new-user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/login')->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.test',
            'role' => 'user',
        ]);
    }

    public function test_authenticated_user_can_open_login_to_switch_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/login')->assertOk();
        $this->actingAs($user)->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'role' => 'admin',
        ])->assertRedirect('/admin');
    }

    public function test_user_cannot_login_through_admin_role_choice(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'admin',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');
    }

    public function test_watchlists_are_owned_by_users_and_visible_to_admin(): void
    {
        $country = Country::create([
            'code' => 'IDN',
            'name' => 'Indonesia',
            'region' => 'Asia',
            'currency_code' => 'IDR',
            'language' => 'Indonesian',
        ]);
        $userA = User::factory()->create(['role' => 'user', 'name' => 'User A']);
        $userB = User::factory()->create(['role' => 'user', 'name' => 'User B']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($userA)->post('/watchlists/IDN/toggle')->assertRedirect();

        $this->assertDatabaseHas('watchlists', [
            'user_id' => $userA->id,
            'country_code' => $country->code,
        ]);
        $this->actingAs($userB)->get('/watchlists')->assertOk()->assertSee('Belum ada negara favorit');
        $this->actingAs($admin)->get('/admin/watchlists')->assertOk()->assertSee('User A')->assertSee('Indonesia');

        $this->assertSame(1, Watchlist::count());
    }
}
