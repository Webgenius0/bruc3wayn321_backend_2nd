<?php

namespace App\Http\Controllers\API\Destination;

use App\Http\Controllers\Controller;
use App\Services\DestinationService;
use App\Services\UserDestinationService;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function __construct(
        protected DestinationService $destinationService,
        protected UserDestinationService $service

    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->destinationService->list(
                $request->lat,
                $request->lng
            ),
        ]);
    }

    public function show(string $placeId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->destinationService->details($placeId),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'place_id' => ['required', 'string'],
        ]);

        $this->service->save(
            $request->place_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Destination saved successfully.',
        ]);
    }

    public function destroy(string $placeId)
    {
        $this->service->remove($placeId);

        return response()->json([
            'success' => true,
            'message' => 'Destination removed successfully.',
        ]);
    }

    public function mydestination()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->myDestinations(auth()->id()),
        ]);
    }

    public function userDestination($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->myDestinations($id),
        ]);
    }
}
