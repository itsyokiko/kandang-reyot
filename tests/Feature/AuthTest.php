<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // Override: skip auto-login — this class tests both guest and auth states explicitly
    protected function setUp(): void
    {
        // Call grandparent setUp only (skip the auto-login in Tests\TestCase)
        \Illuminate\Foundation\Testing\TestCase::setUp();
    }

    public function test_can_view_login_page()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Kandang Reyot');
        $response->assertSee('Masuk dengan Google');
        $response->assertSee('kandangreyot.firebaseapp.com');
    }

    public function test_authenticated_user_redirected_from_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_can_login_via_firebase_endpoint()
    {
        $payload = [
            'name' => 'Peternak Hebat',
            'email' => 'peternak@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
            'uid' => 'firebase-uid-12345',
        ];

        $response = $this->postJson(route('login.firebase'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'peternak@example.com',
            'name' => 'Peternak Hebat',
            'firebase_uid' => 'firebase-uid-12345',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $this->assertAuthenticated();
    }

    public function test_firebase_login_for_existing_user_updates_info()
    {
        $user = User::factory()->create([
            'email' => 'lama@example.com',
            'name' => 'Nama Lama',
            'firebase_uid' => null,
            'avatar' => null,
        ]);

        $payload = [
            'name' => 'Nama Baru',
            'email' => 'lama@example.com',
            'avatar' => 'https://example.com/new-avatar.jpg',
            'uid' => 'firebase-uid-updated',
        ];

        $response = $this->postJson(route('login.firebase'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'lama@example.com',
            'name' => 'Nama Baru',
            'firebase_uid' => 'firebase-uid-updated',
        ]);

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
