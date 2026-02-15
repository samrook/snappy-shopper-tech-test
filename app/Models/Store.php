<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    /** @use HasFactory<\Database\Factories\StoreFactory> */
    use HasFactory;
    
    protected $casts = [
        'location' => 'string', // Prevents Eloquent from mangling the binary data
    ];

    protected $fillable = ['name', 'location', 'delivery_radius_km'];

    public function scopeWithinDistance($query, $latitude, $longitude, $radiusKm)
    {
        // Multiply radius by 1000 as ST_Distance_Sphere uses meters
        return $query->whereRaw(
            "ST_Distance_Sphere(location, POINT(?, ?)) <= ?",
            [$longitude, $latitude, $radiusKm * 1000]
        );
    }
}
