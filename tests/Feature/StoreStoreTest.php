<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_store_with_valid_data(): void
    {   
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'name' => 'Aberdeen Central',
            'latitude' => 57.1460,
            'longitude' => -2.1030,
            'delivery_radius_km' => 10,
        ];

        $response = $this->postJson('/api/stores', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Aberdeen Central')
            ->assertJsonPath('data.latitude', 57.146)
            ->assertJsonPath('data.longitude', -2.103);

        $this->assertDatabaseHas('stores', [
            'name' => 'Aberdeen Central',
            'delivery_radius_km' => 10,
        ]);
    }

    public function test_it_fails_to_create_store_without_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/stores', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'latitude', 'longitude', 'delivery_radius_km']);
    }

    public function test_it_fails_with_invalid_coordinates(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'name' => 'Impossible Shop',
            'latitude' => 120.0,
            'longitude' => -200.0,
            'delivery_radius_km' => 5,
        ];

        $response = $this->postJson('/api/stores', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_it_fails_with_invalid_radius(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'name' => 'Tiny Radius',
            'latitude' => 57.1460,
            'longitude' => -2.1030,
            'delivery_radius_km' => 0,
        ];

        $response = $this->postJson('/api/stores', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_radius_km']);
    }

    public function test_it_returns_401_when_not_authorised(): void
    {
        $payload = [
            'name' => 'Aberdeen Central',
            'latitude' => 57.1460,
            'longitude' => -2.1030,
            'delivery_radius_km' => 10,
        ];

        $response = $this->postJson('/api/stores', $payload);

        $response->assertStatus(401);
    }
}
