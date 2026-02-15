<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Postcode extends Model
{
    /** @use HasFactory<\Database\Factories\PostcodeFactory> */
    use HasFactory;

    protected $casts = [
        'location' => 'string', // Prevents Eloquent from mangling the binary data
    ];

    public function scopeWithCoordinates($query)
    {
        return $query->addSelect([
            'latitude' => DB::raw('ST_Y(location)'),
            'longitude' => DB::raw('ST_X(location)'),
        ]);
    }
}
