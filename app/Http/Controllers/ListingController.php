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

    // --- ACȚIUNILE INDEX & DETAILS (din Exercițiul 1) ---
    public function index()
    {
         $listings = $this->listingAccessor->getAll();
        // ListingResource::collection realizează maparea automată a listei
        return ListingResource::collection($listings);
    }

    public function details(int $id)
    {
        $listing = $this->listingAccessor->getById($id);
        if (!$listing) {
            return response()->json(['message' => 'Entitatea nu a fost găsită.'], 404);
        }
        // new ListingResource mappează automat o singură entitate
        return new ListingResource($listing);
    }

    // --- OPERAȚIA CREATE ---

    /**
     * 1. CREATE (GET): Afișează formularul inițial gol
     */
    public function create()
    {
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
        // Validarea datelor (text, select, checkbox)
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'priceType'   => 'required|string',
            'city'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Tratarea controlului CheckBox (dacă nu e bifat, browserul nu trimite valoarea)
        $validated['is_featured'] = $request->boolean('is_featured');
        
        // Atribuim provider-ul logat sau primul utilizator (ID 1) pentru teste
        $validated['provider_id'] = auth()->id() ?? 1;
        $validated['status'] = 'activ';

        // Apel prin Accessor (AddEntity)
        $listing = $this->listingAccessor->create($validated);

        return redirect()->route('listings.details', $listing->id)->with('success', 'Anuntul a fost creat cu succes!');
    }

    // --- OPERAȚIA EDIT ---

    /**
     * 3. EDIT (GET): Afișează formularul populat cu datele entității existente
     */
    public function edit(int $id)
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
    public function update(Request $request, int $id)
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

        // Apel prin Accessor (UpdateEntity)
        $this->listingAccessor->update($id, $validated);

        return redirect()->route('listings.details', $id)->with('success', 'Anunțul a fost actualizat cu succes!');
    }
}