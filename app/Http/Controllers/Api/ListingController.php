<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Accessors\ListingAccessor;
use Illuminate\Http\JsonResponse;

class ListingController extends Controller
{
    protected ListingAccessor $listingAccessor;

    public function __construct(ListingAccessor $listingAccessor)
    {
        $this->listingAccessor = $listingAccessor;
    }

    /**
     * GET /api/listings
     * Returnează toate entitățile în format JSON
     */
    public function index(): JsonResponse
    {
        $listings = $this->listingAccessor->getAll();
        return response()->json($listings, 200);
    }

    /**
     * GET /api/listings/{id}
     * Returnează detaliile unei singure entități
     */
    public function show(int $id): JsonResponse
    {
        $listing = $this->listingAccessor->getById($id);

        if (!$listing) {
            return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
        }

        return response()->json($listing, 200);
    }
}