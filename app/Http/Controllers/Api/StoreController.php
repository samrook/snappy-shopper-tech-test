<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PostcodeNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CanDeliverRequest;
use App\Http\Requests\NearbyStoreRequest;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function __construct(private StoreService $storeService)
    {
    }

    public function canDeliver(CanDeliverRequest $request)
    {
        try {
            $result = $this->storeService->checkFeasibility(
                $request->postcode, 
                $request->store_id
            );

            return response()->json($result);   
        } catch (PostcodeNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Postcode not found.'
            ], 404);
        }
    }

    public function nearby(NearbyStoreRequest $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $stores = $this->storeService->findStoresNearPostcode($request->postcode);
    
            if ($stores->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No stores found delivering to this area.'
                ], 404);
            }
    
            return StoreResource::collection($stores);
        } catch (PostcodeNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Postcode not found.'
            ], 404);
        }
    }

    public function store(StoreStoreRequest $request): JsonResponse
    {
        $store = $this->storeService->createStore($request->validated());

        return (new StoreResource($store))
            ->response()
            ->setStatusCode(201);
    }
}
