<?php

namespace Tests\Feature;

use App\Models\Postcode;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CanDeliverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_true_when_delivery_is_feasible(): void
    {
        Postcode::factory()->create([
            'postcode' => 'AB101XG',
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);

        $store = Store::factory()->create([
            'delivery_radius_km' => 5,
            'location' => DB::raw("POINT(-2.1035, 57.1465)")
        ]);

        $response = $this->getJson("/api/stores/can-deliver?postcode=AB101XG&store_id={$store->id}");

        $response->assertStatus(200)
            ->assertJson([
                'can_deliver' => true,
                'store_name' => $store->name
            ]);
    }

    public function test_it_returns_false_when_outside_radius(): void
    {
        Postcode::factory()->create([
            'postcode' => 'AB101XG',
            'location' => DB::raw("POINT(-2.1030, 57.1460)")
        ]);

        $store = Store::factory()->create([
            'delivery_radius_km' => 10,
            'location' => DB::raw("POINT(-3.1030, 58.1460)")
        ]);

        $response = $this->getJson("/api/stores/can-deliver?postcode=AB101XG&store_id={$store->id}");

        $response->assertStatus(200)
            ->assertJson(['can_deliver' => false]);
    }

    public function test_it_returns_404_for_invalid_store(): void
    {
        Postcode::factory()->create(['postcode' => 'AB101XG']);

        // Use a store_id that definitely doesn't exist (999)
        $response = $this->getJson("/api/stores/can-deliver?postcode=AB101XG&store_id=999");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['store_id']);
    }

    public function test_it_returns_404_for_invalid_postcode(): void
    {
        $store = Store::factory()->create();

        $response = $this->getJson("/api/stores/can-deliver?postcode=WRONG&store_id={$store->id}");

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Postcode not found.'
            ]);
    }
}
