<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Union Street - City Center
        Store::create([
            'name' => 'Aberdeen Central (Union St)',
            'location' => DB::raw("POINT(-2.1030, 57.1460)"),
            'delivery_radius_km' => 5,
        ]);

        // Dyce - Near the Airport
        Store::create([
            'name' => 'Dyce Outpost',
            'location' => DB::raw("POINT(-2.2040, 57.2110)"),
            'delivery_radius_km' => 7,
        ]);

        // Cove Bay - Southern Aberdeen
        Store::create([
            'name' => 'Cove Bay Express',
            'location' => DB::raw("POINT(-2.0950, 57.1090)"),
            'delivery_radius_km' => 10,
        ]);
    }
}
