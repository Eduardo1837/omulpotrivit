<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingResource;
use App\Services\CategoryService;
use App\Services\ListingService;
use App\Services\ReviewService;
use App\ViewModels\ListingViewModel;
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
     * Pagina principală: afișează categoriile și cele mai recente/promovate listări active.
     */
    public function home()
    {
        $rawListings = $this->listingService->getHomeListings(6);
        $recentListings = $rawListings->map(fn ($listing) => new ListingViewModel($listing));
        $categories = $this->categoryService->getAllCategoriesWithActiveListings();

        return view('welcome', compact('recentListings', 'categories'));
    }

    // --- ACȚIUNILE INDEX & DETAILS ---
    public function index()
    {
        $listings = $this->listingService->getAllActiveListings();

        if (request()->wantsJson()) {
            return ListingResource::collection($listings);
        }

        $viewModels = $listings->map(fn ($l) => new ListingViewModel($l));
        return view('listings.index', ['listings' => $viewModels]);
    }

    public function details(int|string $id)
    {
        $listing = $this->listingService->getActiveListingById($id);
        if (!$listing) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
            }
            abort(404, 'Anunțul nu există.');
        }

        if (request()->wantsJson()) {
            return new ListingResource($listing);
        }

        $reviews = $this->reviewService->getReviewsForListing($listing->id, visibleOnly: true);

        return view('listings.details', [
            'listing' => new ListingViewModel($listing),
            'reviews' => $reviews,
        ]);
    }

    /**
     * Secțiunea "Anunțurile Mele" pentru utilizatorul autentificat
     */
    public function myListings()
    {
        $isAdminPreview = auth()->user()?->role === 'admin';

        if ($isAdminPreview) {
            $listings = $this->listingService->getAdminPreviewMockupListings();
            $canCreateMore = false;
        } else {
            $userId = auth()->id();
            $listings = $this->listingService->getUserListings($userId, activeOnly: false);
            $canCreateMore = $this->listingService->canUserCreateListing($userId);
        }

        $promotionPlans = $this->listingService->getPromotionPlans();

        return view('listings.my-listings', compact('listings', 'canCreateMore', 'promotionPlans', 'isAdminPreview'));
    }

    /**
     * Previzualizare mockup pentru pagina de detalii a unui anunț din „Anunțurile Mele” (doar admin).
     */
    public function previewMockupDetails(int|string $id)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $listing = $this->listingService->getAdminPreviewMockupListingById($id);
        if (!$listing) {
            abort(404);
        }

        return view('listings.details', [
            'listing' => new ListingViewModel($listing),
            'reviews' => $listing->reviews,
            'userHasReview' => true,
            'isAdminPreview' => true,
        ]);
    }

    /**
     * Previzualizare mockup pentru formularul de editare a unui anunț (doar admin).
     */
    public function previewMockupEdit(int|string $id)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $listing = $this->listingService->getAdminPreviewMockupListingById($id);
        if (!$listing) {
            abort(404);
        }

        return view('listings.edit', [
            'listing' => $listing,
            'categories' => $this->categoryService->getAllCategories(),
            'isAdminPreview' => true,
        ]);
    }

    // --- OPERAȚIA CREATE ---

    /**
     * 1. CREATE (GET): Afișează formularul inițial gol
     */
    public function create()
    {
        $canCreateFree = $this->listingService->canUserCreateListing(auth()->id(), false);
        $categories = $this->categoryService->getAllCategories();

        return view('listings.create', [
            'categories' => $categories,
            'canCreateFree' => $canCreateFree,
        ]);
    }

    /**
     * 2. CREATE (POST): Salvează datele trimise din formular
     */
    public function store(Request $request)
    {
        if (auth()->user()?->role === 'admin') {
            abort(403, 'Administratorii nu pot crea anunțuri.');
        }

        $userId = auth()->id();
        $isFeatured = $request->boolean('is_featured');

        if (!$this->listingService->canUserCreateListing($userId, $isFeatured)) {
            return redirect()->route('listings.create')
                ->withInput()
                ->with('error', 'Ai atins limita de 1 anunț gratuit. Bifează opțiunea de promovare (Featured) pentru a publica un anunț suplimentar.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'priceType'   => 'required|string',
            'city'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['is_featured'] = $isFeatured;

        $listing = $this->listingService->createListing($validated, $userId);

        return redirect()->route('listings.details', $listing->id)->with('success', 'Anunțul a fost creat cu succes!');
    }

    // --- OPERAȚIA EDIT ---

    /**
     * 3. EDIT (GET): Afișează formularul populat cu datele entității existente
     */
    public function edit(int|string $id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404, 'Anunțul nu există.');
        }

        if ((int) $listing->provider_id !== auth()->id() && auth()->user()?->role !== 'admin') {
            abort(403, 'Nu ai permisiunea să editezi acest anunț.');
        }

        $categories = $this->categoryService->getAllCategories();

        return view('listings.edit', [
            'listing'    => $listing,
            'categories' => $categories
        ]);
    }

    /**
     * 4. EDIT (POST): Salvează modificările aduse entității
     */
    public function update(Request $request, int|string $id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404, 'Anunțul nu există.');
        }

        if ((int) $listing->provider_id !== auth()->id() && auth()->user()?->role !== 'admin') {
            abort(403, 'Nu ai permisiunea să actualizezi acest anunț.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'priceType'   => 'required|string',
            'city'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $this->listingService->updateListing($id, $validated, auth()->id());

        return redirect()->route('listings.details', $id)->with('success', 'Anunțul a fost actualizat cu succes!');
    }
}
