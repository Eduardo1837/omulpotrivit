<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ListingService;
use App\Services\ReviewService;
use App\Services\UserService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        protected ListingService $listingService,
        protected ReviewService $reviewService,
        protected UserService $userService,
        protected CategoryService $categoryService
    ) {
    }

    public function dashboard()
    {
        $stats = [
            'users' => $this->userService->countUsers(),
            'listings' => $this->listingService->countAllListings(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // --- USER MANAGEMENT ---

    public function index()
    {
        $users = $this->userService->getAllUsersWithListings();

        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
            'city' => 'nullable|string|max:255',
        ]);

        $this->userService->createUser($validated, auth()->id());

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            abort(404);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:admin,user',
            'city' => 'nullable|string|max:255',
        ]);

        $this->userService->updateUser($id, $validated, auth()->id());

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroyUser($id)
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            abort(404);
        }

        if ((int) $user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $this->userService->deleteUser($id, auth()->id());

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    // --- LISTING MANAGEMENT ---

    public function listingsIndex()
    {
        $listings = $this->listingService->getAllListingsForAdmin();

        return view('admin.listings.index', compact('listings'));
    }

    public function createListing()
    {
        $categories = $this->categoryService->getAllCategories();
        $users = $this->userService->getAllUsers();

        return view('admin.listings.create', compact('categories', 'users'));
    }

    public function storeListing(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'priceType' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'provider_id' => 'required|exists:users,id',
            'status' => 'required|in:activ,inactiv',
            'is_featured' => 'boolean',
        ]);

        $providerId = (int) $validated['provider_id'];
        unset($validated['provider_id']);
        $validated['is_featured'] = $request->boolean('is_featured');

        $this->listingService->createListing($validated, $providerId);

        return redirect()->route('admin.listings.index')->with('success', 'Listing created successfully.');
    }

    public function editListing($id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        $categories = $this->categoryService->getAllCategories();
        $users = $this->userService->getAllUsers();

        return view('admin.listings.edit', compact('listing', 'categories', 'users'));
    }

    public function updateListing(Request $request, $id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'provider_id' => 'required|exists:users,id',
            'status' => 'required|in:activ,inactiv',
            'is_featured' => 'boolean',
        ]);

        $this->listingService->updateListing($id, [
            'title' => $validated['title'],
            'category_id' => (int) $validated['category_id'],
            'provider_id' => (int) $validated['provider_id'],
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
        ], auth()->id());

        return redirect()->route('admin.listings.index')->with('success', 'Listing updated successfully.');
    }

    public function toggleListingStatus($id)
    {
        $listing = $this->listingService->toggleListingStatus($id, auth()->id());
        if (!$listing) {
            abort(404);
        }

        $label = $listing->status === 'activ' ? 'activated' : 'deactivated';

        return redirect()->route('admin.listings.index')->with('success', "Listing {$label} successfully.");
    }

    public function destroyListing(Request $request, $id)
    {
        if (trim((string) $request->input('confirm_delete')) !== 'delete') {
            return redirect()->route('admin.listings.index')
                ->with('error', 'Type "delete" to confirm permanent deletion.');
        }

        $deleted = $this->listingService->forceDeleteListing($id, auth()->id());
        if (!$deleted) {
            abort(404);
        }

        return redirect()->route('admin.listings.index')->with('success', 'Listing permanently deleted.');
    }

    public function listingReviews($id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        $reviews = $this->reviewService->getReviewsForListing($id, visibleOnly: false);

        return view('admin.listings.reviews', compact('listing', 'reviews'));
    }

    public function toggleReviewVisibility($id)
    {
        $review = $this->reviewService->toggleVisibility($id, auth()->id());
        if (!$review) {
            abort(404);
        }

        $status = $review->is_hidden ? 'hidden' : 'visible';

        return back()->with('success', "Review is now {$status}.");
    }

    public function destroyReview($id)
    {
        $hidden = $this->reviewService->hideReview($id, auth()->id());
        if (!$hidden) {
            abort(404);
        }

        return back()->with('success', 'Review hidden successfully.');
    }
}
