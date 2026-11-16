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

        $isFeatured = $request->boolean('is_featured');
        if (!$this->listingService->canUserCreateListing($userId, $isFeatured)) {
            return response()->json([
                'message' => 'Ai atins limita de 1 anunț gratuit. Poți publica anunțuri suplimentare doar dacă sunt promovate.',
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

    /**
     * GET /api/plans
     * Returnează planurile de promovare disponibile în format JSON
     */
    public function plans(): JsonResponse
    {
        $plans = $this->listingService->getPromotionPlans();

        return response()->json($plans, 200);
    }

    /**
     * POST /api/listings/{id}/promote
     * Promovează un anunț al utilizatorului autentificat
     */
    public function promote(Request $request, int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            return response()->json(['message' => 'Anunțul nu a fost găsit.'], 404);
        }

        if ((int) $listing->provider_id !== $userId) {
            return response()->json(['message' => 'Nu ai permisiunea să promovezi acest anunț.'], 403);
        }

        $validated = $request->validate([
            'plan_id' => 'nullable|integer|exists:promotion_plans,id',
        ]);

        $promoted = $this->listingService->promoteListing($id, $userId, $validated['plan_id'] ?? null);
        if (!$promoted) {
            return response()->json(['message' => 'Anunțul nu poate fi promovat cu planul selectat.'], 422);
        }

        $updatedListing = $this->listingService->getListingById($id);

        return response()->json([
            'message' => 'Anunțul a fost promovat cu succes.',
            'listing' => new ListingResource($updatedListing),
        ], 200);
    }

    /**
     * PATCH /api/listings/{id}/toggle-status
     * Comută statusul unui anunț între 'activ' și 'inactiv'
     */
    public function toggleStatus(int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            return response()->json(['message' => 'Anunțul nu a fost găsit.'], 404);
        }

        if ((int) $listing->provider_id !== $userId) {
            return response()->json(['message' => 'Nu ai permisiunea să modifici acest anunț.'], 403);
        }

        $updatedListing = $this->listingService->toggleListingStatus($id, $userId);

        return response()->json([
            'message' => 'Statusul anunțului a fost actualizat.',
            'listing' => new ListingResource($updatedListing),
        ], 200);
    }

    /**
     * DELETE /api/listings/{id}
     * Ștergere logică a unui anunț (status = 'inactiv')
     */
    public function destroy(int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            return response()->json(['message' => 'Anunțul nu a fost găsit.'], 404);
        }

        if ((int) $listing->provider_id !== $userId) {
            return response()->json(['message' => 'Nu ai permisiunea să ștergi acest anunț.'], 403);
        }

        $this->listingService->deleteListing($id, $userId);

        return response()->json([
            'message' => 'Anunțul a fost dezactivat (ștergere logică).',
        ], 200);
    }
}
