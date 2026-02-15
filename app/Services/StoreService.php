<?php

namespace App\Services;

use App\Exceptions\PostcodeNotFoundException;
use App\Exceptions\StoreNotFoundException;
use App\Models\Store;
use Illuminate\Support\Collection;
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

    public function findStoresNearPostcode(string $postcode): Collection
    {
        $cleanPostcode = $this->sanitizePostcode($postcode);
        
        $location = DB::table('postcodes')
            ->selectRaw('ST_Y(location) as lat, ST_X(location) as lng')
            ->where('postcode', $cleanPostcode)
            ->first();

        if (! $location) {
            throw new PostcodeNotFoundException;
        }

        return Store::query()
            ->withCoordinates()
            ->withinDistance($location->lat, $location->lng)
            ->orderByDistance($location->lat, $location->lng)
            ->get();
    }

    public function getPostcodeCoordinates(string $postcode)
    {
        $cleanPostcode = $this->sanitizePostcode($postcode);

        return DB::table('postcodes')
            ->selectRaw('ST_Y(location) as lat, ST_X(location) as lng')
            ->where('postcode', $cleanPostcode)
            ->first();
    }

    private function sanitizePostcode(string $postcode): string
    {
        return strtoupper(str_replace(' ', '', $postcode));
    }
}
