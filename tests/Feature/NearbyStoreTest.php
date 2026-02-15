<?php

namespace Tests\Feature;

use App\Models\Postcode;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NearbyStoreTest extends TestCase
{
    use RefreshDatabase; // Wipes the DB for a clean test every time

    public function test_it_returns_stores_within_delivery_radius(): void
    {
        Postcode::factory()->create([
            'postcode' => 'AB101XG',
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);

        Store::factory()->create([
            'name' => 'Close Store',
            'delivery_radius_km' => 5,
            'location' => DB::raw("POINT(-2.1035, 57.1465)")
        ]);

        Store::factory()->create([
            'name' => 'Far Away Store',
            'delivery_radius_km' => 5,
            'location' => DB::raw("POINT(-4.2247, 57.4778)")
        ]);

        $response = $this->getJson('/api/stores/nearby?postcode=AB101XG');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Close Store');
    }

    public function test_it_returns_404_when_no_stores_in_range(): void
    {
        Postcode::factory()->create(['postcode' => 'AB101XG']);
        
        Store::factory()->create([
            'delivery_radius_km' => 1,
            'location' => DB::raw("POINT(0, 0)")
        ]);

        $response = $this->getJson('/api/stores/nearby?postcode=AB101XG');

        $response->assertStatus(404);
    }

    public function test_it_returns_only_stores_within_their_delivery_radius(): void
    {
        Postcode::factory()->create([
            'postcode' => 'AB101XG',
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);

        Store::factory()->create([
            'name' => 'Store A (In Range)',
            'delivery_radius_km' => 5,
            'location' => DB::raw("POINT(-2.1040, 57.1470)")
        ]);

        Store::factory()->create([
            'name' => 'Store B (Too Far)',
            'delivery_radius_km' => 5,
            'location' => DB::raw("POINT(-2.3000, 57.2000)")
        ]);

        $response = $this->getJson('/api/stores/nearby?postcode=AB101XG');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Store A (In Range)');
    }

    public function test_it_returns_stores_ordered_by_distance(): void
    {
        Postcode::factory()->create([
            'postcode' => 'AB101XG',
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);

        // Further store (4km away)
        Store::factory()->create([
            'name' => 'Further Store',
            'delivery_radius_km' => 10,
            'location' => DB::raw("POINT(-2.1500, 57.1700)")
        ]);

        // Closer store (1km away)
        Store::factory()->create([
            'name' => 'Closer Store',
            'delivery_radius_km' => 10,
            'location' => DB::raw("POINT(-2.1100, 57.1480)")
        ]);

        $response = $this->getJson('/api/stores/nearby?postcode=AB101XG');

        $response->assertStatus(200);
        $this->assertEquals('Closer Store', $response->json('data.0.name'));
        $this->assertEquals('Further Store', $response->json('data.1.name'));
    }

    public function test_it_handles_postcode_normalization(): void
    {
        Postcode::factory()->create([
            'postcode' => 'AB101XG',
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);
        
        Store::factory()->create([
            'delivery_radius_km' => 50,
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);

        $response = $this->getJson('/api/stores/nearby?postcode=ab10 1xg');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_it_returns_404_if_postcode_is_not_found(): void
    {
        $response = $this->getJson('/api/stores/nearby?postcode=NONEXISTENT');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Postcode not found.',
            ]);
    }

    public function test_it_returns_422_if_postcode_is_missing(): void
    {
        $response = $this->getJson('/api/stores/nearby');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['postcode']);
    }
}
