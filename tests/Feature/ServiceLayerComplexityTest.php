<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\PromotionPlan;
use App\Models\Review;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\ListingService;
use App\Services\ReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ServiceLayerComplexityTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_listings_prioritize_featured_and_filter_inactive_with_cache(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $category = Category::create(['name' => 'IT & Programare', 'description' => 'Servicii IT']);

        $regular = Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Regular Active Listing',
            'description' => 'Desc',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        $featured = Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Featured Active Listing',
            'description' => 'Desc',
            'price' => 200,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        $inactive = Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Inactive Listing',
            'description' => 'Desc',
            'price' => 300,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'inactiv',
            'is_featured' => true,
        ]);

        $listingService = app(ListingService::class);
        $homeListings = $listingService->getHomeListings();

        $this->assertCount(2, $homeListings);
        $this->assertEquals($featured->id, $homeListings->first()->id);
        $this->assertFalse($homeListings->contains('id', $inactive->id));
        $this->assertTrue(Cache::has(ListingService::CACHE_KEY_HOME_LISTINGS));

        // Logical deletion invalidates cache and hides listing from active queries
        Log::spy();
        $this->assertTrue($listingService->deleteListing($regular->id, $user->id));
        $this->assertFalse(Cache::has(ListingService::CACHE_KEY_HOME_LISTINGS));

        $regular->refresh();
        $this->assertSame('inactiv', $regular->status);
        $this->assertNull($listingService->getActiveListingById($regular->id));

        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context) use ($regular) {
            return str_contains($message, 'logically deleted') && ($context['listing_id'] ?? null) === $regular->id;
        })->once();
    }

    public function test_categories_and_promotion_plans_are_cached(): void
    {
        Cache::flush();

        Category::create(['name' => 'Gastronomie', 'description' => 'Bucatarie']);
        PromotionPlan::create([
            'name' => 'Promovare 7 Zile',
            'price' => 29.99,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 1,
        ]);

        $categoryService = app(CategoryService::class);
        $listingService = app(ListingService::class);

        $categories = $categoryService->getAllCategoriesWithActiveListings();
        $plans = $listingService->getPromotionPlans();

        $this->assertCount(1, $categories);
        $this->assertCount(1, $plans);
        $this->assertTrue(Cache::has(CategoryService::CACHE_KEY_ACTIVE_CATEGORIES));
        $this->assertTrue(Cache::has(ListingService::CACHE_KEY_PROMOTION_PLANS));

        $categoryService->createCategory(['name' => 'Curatenie', 'description' => 'Casa']);
        $this->assertFalse(Cache::has(CategoryService::CACHE_KEY_ACTIVE_CATEGORIES));
    }

    public function test_quota_block_and_promotion_logging(): void
    {
        Log::spy();

        $user = User::factory()->create();
        $category = Category::create(['name' => 'Gaming', 'description' => 'Game Dev']);
        $listingService = app(ListingService::class);

        // 0 free listings -> allowed to create 1st free listing
        $this->assertTrue($listingService->canUserCreateListing($user->id, isFeatured: false));

        $firstListing = $listingService->createListing([
            'category_id' => $category->id,
            'title' => 'First Free Listing',
            'description' => 'Desc',
            'price' => 150,
            'priceType' => 'fix',
            'city' => 'Cluj',
        ], $user->id);

        // 2nd free listing is blocked
        $this->assertFalse($listingService->canUserCreateListing($user->id, isFeatured: false));
        // Featured listings are always allowed
        $this->assertTrue($listingService->canUserCreateListing($user->id, isFeatured: true));

        // Even after creating a featured listing, a 2nd free listing remains blocked
        $featuredListing = $listingService->createListing([
            'category_id' => $category->id,
            'title' => 'Featured Listing',
            'description' => 'Desc',
            'price' => 300,
            'priceType' => 'fix',
            'city' => 'Cluj',
            'is_featured' => true,
        ], $user->id);
        $this->assertFalse($listingService->canUserCreateListing($user->id, isFeatured: false));

        // Promoting the free listing frees up the 1 free slot
        $this->assertTrue($listingService->promoteListing($firstListing->id, $user->id));
        $this->assertTrue($listingService->canUserCreateListing($user->id, isFeatured: false));

        Log::shouldHaveReceived('info')->withArgs(fn (string $msg) => $msg === 'Listing created')->twice();
        Log::shouldHaveReceived('warning')->withArgs(fn (string $msg) => str_contains($msg, 'blocked by free-plan quota'))->twice();
        Log::shouldHaveReceived('info')->withArgs(fn (string $msg) => str_contains($msg, 'promoted'))->once();
    }

    public function test_hidden_reviews_are_filtered_from_public_queries(): void
    {
        $provider = User::factory()->create();
        $author1 = User::factory()->create();
        $author2 = User::factory()->create();
        $category = Category::create(['name' => 'Design', 'description' => 'UI/UX']);

        $listing = Listing::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Active Listing',
            'description' => 'Desc',
            'price' => 250,
            'priceType' => 'fix',
            'city' => 'Iasi',
            'status' => 'activ',
        ]);

        $reviewService = app(ReviewService::class);
        $visible = $reviewService->createReview([
            'listing_id' => $listing->id,
            'rating' => 5,
            'comment' => 'Great service!',
        ], $author1->id);

        $hidden = $reviewService->createReview([
            'listing_id' => $listing->id,
            'rating' => 1,
            'comment' => 'Spam review',
        ], $author2->id);

        $this->assertTrue($reviewService->hideReview($hidden->id, $provider->id));

        $publicReviews = $reviewService->getVisibleReviewsPaginated(10);
        $this->assertCount(1, $publicReviews);
        $this->assertEquals($visible->id, $publicReviews->first()->id);
        $this->assertTrue((bool) Review::find($hidden->id)->is_hidden);
    }
}
