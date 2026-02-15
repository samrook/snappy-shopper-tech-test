<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class PostcodeCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_caches_postcode_coordinates_after_first_lookup(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('postcode_coords_AB101XG', Mockery::any(), Mockery::any())
            ->andReturn((object)['lat' => 57.146, 'lng' => -2.103]);

        $this->getJson('/api/stores/nearby?postcode=AB101XG');
    }
}
