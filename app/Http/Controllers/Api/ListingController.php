<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService
    ) {
    }

    /**
     * GET /api/listings
     * Returnează toate entitățile active în format JSON
     */
    public function index(): JsonResponse
    {
        $listings = $this->listingService->getAllActiveListings();
        return response()->json(ListingResource::collection($listings), 200);
    }

    /**
     * GET /api/listings/{id}
     * Returnează detaliile unei singure entități active
     */
    public function show(int|string $id): JsonResponse
    {
        $listing = $this->listingService->getActiveListingById($id);

        if (!$listing) {
            return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
        }

        return response()->json(new ListingResource($listing), 200);
    }

    /**
     * POST /api/listings
     * Adaugă o entitate nouă primită în Body-ul request-ului (JSON)
     */
    public function store(Request $request): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        if (!$this->listingService->canUserCreateListing($userId)) {
            return response()->json([
                'message' => 'Ai atins limita de 1 anunț gratuit. Promovează anunțul existent pentru a publica altele.',
            ], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'priceType'   => 'nullable|string',
            'city'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'is_featured' => 'nullable|boolean',
        ]);

        $validated['priceType'] = $validated['priceType'] ?? 'fix';
        $validated['is_featured'] = $request->boolean('is_featured');

        $listing = $this->listingService->createListing($validated, $userId);

        return response()->json(new ListingResource($listing), 201);
    }

    /**
     * PUT /api/listings/{id}
     * Actualizează o entitate existentă pe baza datelor din Body (JSON)
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $listing = $this->listingService->getListingById($id);

        if (!$listing) {
            return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price'       => 'sometimes|required|numeric|min:0',
            'priceType'   => 'sometimes|required|string',
            'city'        => 'sometimes|required|string|max:100',
            'category_id' => 'sometimes|required|exists:categories,id',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($request->has('is_featured')) {
            $validated['is_featured'] = $request->boolean('is_featured');
        }

        $this->listingService->updateListing($id, $validated, auth()->id());

        $updatedListing = $this->listingService->getListingById($id);

        return response()->json(new ListingResource($updatedListing), 200);
    }
}
