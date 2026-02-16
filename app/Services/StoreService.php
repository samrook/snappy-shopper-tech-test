<?php

namespace App\Services;

use App\Exceptions\PostcodeNotFoundException;
use App\Exceptions\StoreNotFoundException;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function checkFeasibility(string $postcode, int $storeId): array
    {
        $coords = $this->getPostcodeCoordinates($postcode);
        
        if (! $coords) {
            throw new PostcodeNotFoundException;
        }

        $result = DB::selectOne("
            SELECT 
                name,
                ST_Distance_Sphere(location, POINT(?, ?)) / 1000 as distance_km,
                delivery_radius_km
            FROM stores 
            WHERE id = ?", 
            [$coords->lng, $coords->lat, $storeId]
        );

        if (! $result) {
            throw new StoreNotFoundException;
        }

        return [
            'can_deliver' => $result->distance_km <= $result->delivery_radius_km,
            'distance_km' => round($result->distance_km, 2),
            'store_name' => $result->name
        ];
    }

    public function createStore(array $data): Store
    {
        $store = Store::create([
            'name' => $data['name'],
            'delivery_radius_km' => $data['delivery_radius_km'],
            'location' => DB::raw("POINT({$data['longitude']}, {$data['latitude']})"),
        ]);

        return Store::query()->withCoordinates()->find($store->id);
    }

    public function findStoresNearPostcode(string $postcode): Collection
    {
        $location = $this->getPostcodeCoordinates($postcode);

        if (! $location) {
            throw new PostcodeNotFoundException;
        }

        return Store::query()
            ->withCoordinates()
            ->withinDistance($location->lat, $location->lng)
            ->orderByDistance($location->lat, $location->lng)
            ->paginate(15);
    }

    public function getPostcodeCoordinates(string $postcode)
    {
        $cleanPostcode = $this->sanitizePostcode($postcode);

        return Cache::remember(
            "postcode_coords_{$cleanPostcode}",
            now()->addDay(),
            function () use ($cleanPostcode) {
                return DB::table('postcodes')
                    ->selectRaw('ST_Y(location) as lat, ST_X(location) as lng')
                    ->where('postcode', $cleanPostcode)
                    ->first();
            }
        );
    }

    private function sanitizePostcode(string $postcode): string
    {
        return strtoupper(str_replace(' ', '', $postcode));
    }
}
