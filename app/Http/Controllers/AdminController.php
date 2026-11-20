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
            'categories' => $this->categoryService->getAllCategories()->count(),
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

    public function editListing($id)
    {
        $listing = $this->listingService->getListingById($id);
        if (!$listing) {
            abort(404);
        }

        $categories = $this->categoryService->getAllCategories();
        $users = $this->userService->getAllProviders();

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

    // --- CATEGORY MANAGEMENT ---

    public function categoriesIndex()
    {
        $categories = $this->categoryService->getAllCategories();

        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        $categories = $this->categoryService->getAllCategories();

        return view('admin.categories.create', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $this->categoryService->createCategory($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function editCategory($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        if (!$category) {
            abort(404);
        }

        $categories = $this->categoryService->getAllCategories()
            ->reject(fn ($item) => (int) $item->id === (int) $id);

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = $this->categoryService->getCategoryById($id);
        if (!$category) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id,
        ]);

        $this->categoryService->updateCategory($id, $validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroyCategory($id)
    {
        $category = $this->categoryService->getCategoryById($id);
        if (!$category) {
            abort(404);
        }

        if ($category->listings->isNotEmpty()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete a category that still has listings.');
        }

        $this->categoryService->deleteCategory($id);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }

    // --- PROMOTION PLANS ---

    public function plansIndex()
    {
        $plans = $this->listingService->getPromotionPlans(visibleOnly: false);

        return view('admin.plans.index', compact('plans'));
    }

    public function createPlan()
    {
        return view('admin.plans.create');
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'durationDays' => 'required|integer|min:0',
            'maxItems' => 'nullable|integer|min:1',
        ]);

        $this->listingService->createPromotionPlan($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Planul de promovare a fost creat cu succes.');
    }

    public function editPlan($id)
    {
        $plan = $this->listingService->getPromotionPlanById($id);
        if (!$plan) {
            abort(404);
        }

        return view('admin.plans.edit', compact('plan'));
    }

    public function updatePlan(Request $request, $id)
    {
        $plan = $this->listingService->getPromotionPlanById($id);
        if (!$plan) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'durationDays' => 'required|integer|min:0',
            'maxItems' => 'nullable|integer|min:1',
        ]);

        $this->listingService->updatePromotionPlan($id, $validated);

        return redirect()->route('admin.plans.index')->with('success', 'Planul de promovare a fost actualizat cu succes.');
    }

    public function togglePlanVisibility($id)
    {
        $plan = $this->listingService->togglePromotionPlanVisibility($id);
        if (!$plan) {
            abort(404);
        }

        $statusLabel = $plan->is_hidden ? 'ascuns' : 'vizibil';

        return redirect()->route('admin.plans.index')->with('success', "Planul \"{$plan->name}\" este acum {$statusLabel}.");
    }

    public function destroyPlan($id)
    {
        $plan = $this->listingService->getPromotionPlanById($id);
        if (!$plan) {
            abort(404);
        }

        if (!$this->listingService->deletePromotionPlan($id)) {
            return redirect()->route('admin.plans.index')
                ->with('error', 'Planul nu poate fi șters deoarece are promovări asociate. Îl poți ascunde în schimb.');
        }

        return redirect()->route('admin.plans.index')->with('success', 'Planul de promovare a fost șters cu succes.');
    }
}
