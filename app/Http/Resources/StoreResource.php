<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'delivery_radius_km' => $this->delivery_radius_km,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'distance_km' => isset($this->distance_m) ? round($this->distance_m / 1000, 2) : null,
        ];
    }
}
