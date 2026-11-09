<?php

namespace App\Http\Controllers;

use App\Accessors\ListingAccessor;
use App\Accessors\CategoryAccessor;
use App\ViewModels\ListingViewModel;
use Illuminate\Http\Request;
use App\Http\Resources\ListingResource;

class ListingController extends Controller
{
    protected ListingAccessor $listingAccessor;
    protected CategoryAccessor $categoryAccessor;

    public function __construct(ListingAccessor $listingAccessor, CategoryAccessor $categoryAccessor)
    {
        $this->listingAccessor = $listingAccessor;
        $this->categoryAccessor = $categoryAccessor;
    }

    // --- ACȚIUNILE INDEX & DETAILS ---
    public function index()
    {
        $listings = $this->listingAccessor->getAll();

        if (request()->wantsJson()) {
            return ListingResource::collection($listings);
        }

        $viewModels = $listings->map(fn($l) => new ListingViewModel($l));
        return view('listings.index', ['listings' => $viewModels]);
    }

    public function details(int|string $id)
    {
        $listing = $this->listingAccessor->getById($id);
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
        $listings = auth()->user()->listings()->with(['category', 'reviews'])->latest()->get();
        $canCreateMore = $this->canUserCreateListing();

        return view('listings.my-listings', compact('listings', 'canCreateMore'));
    }

    /**
     * Verifică dacă utilizatorul poate crea un nou anunț:
     * Utilizatorul cu anunț nepromovat (neprelungit/neplătit) are voie la MAXIMUM 1 anunț.
     */
    protected function canUserCreateListing(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        $count = $user->listings()->count();
        if ($count === 0) {
            return true;
        }

        // Dacă are cel puțin un anunț, poate crea mai multe DOAR dacă cel puțin unul este promovat (is_featured = true)
        return $user->listings()->where('is_featured', true)->exists();
    }

    // --- OPERAȚIA CREATE ---

    /**
     * 1. CREATE (GET): Afișează formularul inițial gol
     */
    public function create()
    {
        if (!$this->canUserCreateListing()) {
            return redirect()->route('listings.my')->with('error', 'Ai atins limita de 1 anunț gratuit. Promovează anunțul curent pentru a putea publica mai multe anunțuri.');
        }

        // Obținem categoriile pentru lista de selecție (dropdown)
        $categories = $this->categoryAccessor->getAll();

        return view('listings.create', [
            'categories' => $categories
        ]);
    }

    /**
     * 2. CREATE (POST): Salvează datele trimise din formular
     */
    public function store(Request $request)
    {
        if (!$this->canUserCreateListing()) {
            return redirect()->route('listings.my')->with('error', 'Ai atins limita de 1 anunț gratuit. Promovează anunțul existent pentru a publica altele.');
        }

        // Validarea datelor (text, select, checkbox)
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'priceType'   => 'required|string',
            'city'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Tratarea controlului CheckBox
        $validated['is_featured'] = $request->boolean('is_featured');
        
        $validated['provider_id'] = auth()->id() ?? 1;
        $validated['status'] = 'activ';

        // Apel prin Accessor
        $listing = $this->listingAccessor->create($validated);

        return redirect()->route('listings.details', $listing->id)->with('success', 'Anunțul a fost creat cu succes!');
    }

    // --- OPERAȚIA EDIT ---

    /**
     * 3. EDIT (GET): Afișează formularul populat cu datele entității existente
     */
    public function edit(int|string $id)
    {
        $listing = $this->listingAccessor->getById($id);
        if (!$listing) abort(404, 'Anunțul nu există.');

        $categories = $this->categoryAccessor->getAll();

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
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'priceType'   => 'required|string',
            'city'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        // Apel prin Accessor
        $this->listingAccessor->update($id, $validated);

        return redirect()->route('listings.details', $id)->with('success', 'Anunțul a fost actualizat cu succes!');
    }
}
