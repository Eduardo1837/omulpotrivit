<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Services\CategoryService;
use App\Services\ListingService;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected CategoryService $categoryService,
        protected ReviewService $reviewService
    ) {
    }

    /**
     * GET /api/home
     * Returnează listările recomandate și categoriile active pentru pagina principală
     */
    public function home(): JsonResponse
    {
        $recentListings = $this->listingService->getHomeListings(6);
        $categories = $this->categoryService->getAllCategoriesWithActiveListings();

        return response()->json([
            'listings' => ListingResource::collection($recentListings)->resolve(),
            'categories' => $categories,
        ], 200);
    }

    /**
     * GET /api/categories
     * Returnează toate categoriile disponibile în format JSON
     */
    public function categories(): JsonResponse
    {
        return response()->json($this->categoryService->getAllCategories(), 200);
    }

    /**
     * GET /api/my-listings
     * Returnează anunțurile utilizatorului autentificat și planurile de promovare
     */
    public function myListings(): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listings = $this->listingService->getUserListings($userId, activeOnly: false);

        return response()->json([
            'listings' => ListingResource::collection($listings)->resolve(),
            'can_create_more' => $this->listingService->canUserCreateListing($userId),
            'active_plan_id' => $this->listingService->getUserActivePlanId($userId),
            'promotion_plans' => $this->listingService->getPromotionPlans(),
            'quotas' => $this->listingService->getUserQuotas($userId),
        ], 200);
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
     * Returnează detaliile unei singure entități active și recenziile vizibile
     */
    public function show(int|string $id): JsonResponse
    {
        $listing = $this->listingService->getActiveListingById($id);

        if (!$listing) {
            return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
        }

        $reviews = $this->reviewService->getReviewsForListing($listing->id, visibleOnly: true);
        $currentUserId = auth()->id() ?? auth('sanctum')->id();

        $payload = (new ListingResource($listing))->resolve();
        $payload['reviews'] = $reviews->map(fn ($r) => [
            'id' => $r->id,
            'listing_id' => $r->listing_id,
            'author_id' => $r->author_id,
            'author_name' => $r->author->name ?? 'Utilizator',
            'rating' => (int) $r->rating,
            'comment' => $r->comment,
            'created_at_human' => $r->created_at?->diffForHumans(),
        ])->values();
        $payload['user_has_review'] = $currentUserId !== null && $reviews->contains('author_id', $currentUserId);

        return response()->json($payload, 200);
    }

    /**
     * GET /api/listings/{id}/own
     * Returnează detaliile propriei listări (inclusiv inactive) pentru formularul de editare
     */
    public function showOwn(int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            return response()->json(['message' => 'Anunțul nu a fost găsit.'], 404);
        }

        if ((int) $listing->provider_id !== $userId && auth()->user()?->role !== 'admin') {
            return response()->json(['message' => 'Nu ai permisiunea să accesezi acest anunț.'], 403);
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

        if (auth()->user()?->role === 'admin') {
            return response()->json(['message' => 'Administratorii nu pot crea anunțuri.'], 403);
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
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getListingById($id);

        if (!$listing) {
            return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
        }

        if ((int) $listing->provider_id !== $userId && auth()->user()?->role !== 'admin') {
            return response()->json(['message' => 'Nu ai permisiunea să actualizezi acest anunț.'], 403);
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

        $this->listingService->updateListing($id, $validated, $userId);

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
     * POST /api/plans/select
     * Selectează/activează un plan de promovare pentru utilizatorul autentificat
     */
    public function selectPlan(Request $request): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $validated = $request->validate([
            'plan_id' => 'required|integer|exists:promotion_plans,id',
        ]);

        $plan = $this->listingService->selectUserPlan($userId, (int) $validated['plan_id']);
        if (!$plan) {
            return response()->json(['message' => 'Planul selectat nu este disponibil.'], 404);
        }

        $quotas = $this->listingService->getUserQuotas($userId);

        return response()->json([
            'message' => "Planul „{$plan->name}” a fost activat pentru contul tău.",
            'plan' => $plan,
            'quotas' => $quotas,
        ], 200);
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

        $limitInfo = $this->listingService->checkPromotionPlanLimit($userId, $id, $validated['plan_id'] ?? null);
        if ($limitInfo !== null) {
            $message = $limitInfo['max_items'] === 0
                ? "Planul {$limitInfo['plan_name']} nu include anunțuri promovate. Pentru a promova acest anunț, trebuie să faci upgrade la un plan superior."
                : "Ai atins limita de {$limitInfo['max_items']} anunț(uri) promovate pentru planul {$limitInfo['plan_name']}. Pentru a promova mai multe anunțuri, trebuie să faci upgrade la un plan superior.";

            return response()->json([
                'limit_reached' => true,
                'plan_name' => $limitInfo['plan_name'],
                'max_items' => $limitInfo['max_items'],
                'current_count' => $limitInfo['current_count'],
                'message' => $message,
            ], 422);
        }

        $promoted = $this->listingService->promoteListing($id, $userId, $validated['plan_id'] ?? null);
        if (!$promoted) {
            return response()->json(['message' => 'Anunțul nu poate fi promovat cu planul selectat.'], 422);
        }

        $updatedListing = $this->listingService->getListingById($id);
        $message = $updatedListing?->is_featured
            ? 'Anunțul a fost promovat cu succes.'
            : 'Anunțul a fost trecut pe planul gratuit.';

        return response()->json([
            'message' => $message,
            'listing' => new ListingResource($updatedListing),
            'quotas' => $this->listingService->getUserQuotas($userId),
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
     * Ștergere permanentă (cu confirm_delete === 'delete') sau logică a unui anunț
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Neautentificat.'], 401);
        }

        $listing = $this->listingService->getListingById($id);
        if (!$listing || $listing->status === 'deleted') {
            return response()->json(['message' => 'Anunțul nu a fost găsit.'], 404);
        }

        if ((int) $listing->provider_id !== $userId) {
            return response()->json(['message' => 'Nu ai permisiunea să ștergi acest anunț.'], 403);
        }

        if ($request->has('confirm_delete')) {
            if (trim((string) $request->input('confirm_delete')) !== 'delete') {
                return response()->json([
                    'message' => 'Tastează "delete" pentru a confirma ștergerea.',
                ], 422);
            }

            $this->listingService->markListingAsDeleted($id, $userId);

            return response()->json([
                'message' => 'Anunțul a fost șters.',
            ], 200);
        }

        $this->listingService->deleteListing($id, $userId);

        return response()->json([
            'message' => 'Anunțul a fost dezactivat (ștergere logică).',
        ], 200);
    }
}
