<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingResource;
use App\Services\CategoryService;
use App\Services\ListingService;
use App\ViewModels\ListingViewModel;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected CategoryService $categoryService
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

        return view('listings.details', ['listing' => new ListingViewModel($listing)]);
    }

    /**
     * Secțiunea "Anunțurile Mele" pentru utilizatorul autentificat
     */
    public function myListings()
    {
        $userId = auth()->id();
        $listings = $this->listingService->getUserListings($userId);
        $canCreateMore = $this->listingService->canUserCreateListing($userId);
        $promotionPlans = $this->listingService->getPromotionPlans();

        return view('listings.my-listings', compact('listings', 'canCreateMore', 'promotionPlans'));
    }

    // --- OPERAȚIA CREATE ---

    /**
     * 1. CREATE (GET): Afișează formularul inițial gol
     */
    public function create()
    {
        if (!$this->listingService->canUserCreateListing(auth()->id())) {
            return redirect()->route('listings.my')->with('error', 'Ai atins limita de 1 anunț gratuit. Promovează anunțul curent pentru a putea publica mai multe anunțuri.');
        }

        $categories = $this->categoryService->getAllCategories();

        return view('listings.create', [
            'categories' => $categories
        ]);
    }

    /**
     * 2. CREATE (POST): Salvează datele trimise din formular
     */
    public function store(Request $request)
    {
        $userId = auth()->id();
        if (!$this->listingService->canUserCreateListing($userId)) {
            return redirect()->route('listings.my')->with('error', 'Ai atins limita de 1 anunț gratuit. Promovează anunțul existent pentru a publica altele.');
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
