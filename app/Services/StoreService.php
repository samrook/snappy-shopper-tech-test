<?php

namespace App\Services;

use App\Exceptions\PostcodeNotFoundException;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StoreService
{
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

    private function sanitizePostcode(string $postcode): string
    {
        return strtoupper(str_replace(' ', '', $postcode));
    }
}
