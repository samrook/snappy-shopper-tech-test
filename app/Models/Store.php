<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    /** @use HasFactory<\Database\Factories\StoreFactory> */
    use HasFactory;
    
    protected $casts = [
        'location' => 'string', // Prevents Eloquent from mangling the binary data
    ];

    protected $fillable = ['name', 'location', 'delivery_radius_km'];

    public function scopeWithinDistance($query, $latitude, $longitude)
    {
        // Multiply radius by 1000 as ST_Distance_Sphere uses meters
        return $query->whereRaw(
            "ST_Distance_Sphere(location, POINT(?, ?)) <= (delivery_radius_km * 1000)",
            [$longitude, $latitude]
        );
    }

    public function scopeWithCoordinates($query)
    {
        return $query->select('*')
            ->selectRaw('ST_Y(location) as latitude')
            ->selectRaw('ST_X(location) as longitude');
    }

    public function scopeOrderByDistance($query, $lat, $lng)
    {
        return $query->orderByRaw(
            "ST_Distance_Sphere(location, POINT(?, ?)) ASC",
            [$lng, $lat]
        );
    }

    public function loadCoordinates()
    {
        $coords = DB::table('stores')
            ->selectRaw('ST_Y(location) as lat, ST_X(location) as lng')
            ->where('id', $this->id)
            ->first();

        $this->setAttribute('latitude', $coords->lat);
        $this->setAttribute('longitude', $coords->lng);

        return $this;
    }
}
