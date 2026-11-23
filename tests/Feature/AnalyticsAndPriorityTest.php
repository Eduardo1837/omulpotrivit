<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Promotion;
use App\Models\PromotionPlan;
use App\Models\StatEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnalyticsAndPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_orders_promoted_listings_by_most_expensive_plan_first(): void
    {
        Cache::flush();

        $category = Category::create(['name' => 'IT & Tech', 'description' => 'Tech']);

        $proPlan = PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29.00,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        $businessPlan = PromotionPlan::create([
            'name' => 'Business 30 Days',
            'price' => 79.00,
            'currency' => 'RON',
            'durationDays' => 30,
            'maxItems' => null,
        ]);

        $proUser = User::factory()->create();
        $businessUser = User::factory()->create();
        $freeUser = User::factory()->create();

        $businessListing = Listing::create([
            'provider_id' => $businessUser->id,
            'category_id' => $category->id,
            'title' => 'Business Listing',
            'description' => 'Desc',
            'price' => 200,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        Promotion::create([
            'plan_id' => $businessPlan->id,
            'user_id' => $businessUser->id,
            'listing_id' => $businessListing->id,
            'startsAt' => now(),
            'endsAt' => now()->addDays(30),
            'status' => 'ACTIVE',
        ]);

        $proListing = Listing::create([
            'provider_id' => $proUser->id,
            'category_id' => $category->id,
            'title' => 'Pro Listing',
            'description' => 'Desc',
            'price' => 150,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        Promotion::create([
            'plan_id' => $proPlan->id,
            'user_id' => $proUser->id,
            'listing_id' => $proListing->id,
            'startsAt' => now(),
            'endsAt' => now()->addDays(7),
            'status' => 'ACTIVE',
        ]);

        $freeListing = Listing::create([
            'provider_id' => $freeUser->id,
            'category_id' => $category->id,
            'title' => 'Free Listing',
            'description' => 'Desc',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        $response = $this->getJson('/api/home');
        $response->assertStatus(200);

        $ids = collect($response->json('listings'))->pluck('id')->all();
        $this->assertSame([$businessListing->id, $proListing->id, $freeListing->id], $ids);
    }

    public function test_home_and_show_record_impressions_and_clicks_in_stat_events(): void
    {
        Cache::flush();

        $category = Category::create(['name' => 'Design', 'description' => 'Design']);
        $owner = User::factory()->create();

        $listing = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Logo Design',
            'description' => 'Desc',
            'price' => 300,
            'priceType' => 'fix',
            'city' => 'Iași',
            'status' => 'activ',
            'is_featured' => true,
            'views_count' => 0,
        ]);

        $this->getJson('/api/home')->assertStatus(200);

        $this->assertDatabaseHas('stat_events', [
            'listing_id' => $listing->id,
            'eventType' => 'home_impression',
            'resultPosition' => 1,
        ]);

        $this->getJson("/api/listings/{$listing->id}")->assertStatus(200);

        $this->assertDatabaseHas('stat_events', [
            'listing_id' => $listing->id,
            'eventType' => 'click',
        ]);
        $this->assertSame(1, $listing->fresh()->views_count);
    }

    public function test_analytics_endpoint_enforces_ownership_and_returns_7_day_series(): void
    {
        $category = Category::create(['name' => 'IT', 'description' => 'IT']);
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $listing = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Web App',
            'description' => 'Desc',
            'price' => 500,
            'priceType' => 'fix',
            'city' => 'Cluj',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        StatEvent::create([
            'listing_id' => $listing->id,
            'eventType' => 'home_impression',
            'resultPosition' => 2,
        ]);
        StatEvent::create([
            'listing_id' => $listing->id,
            'eventType' => 'click',
        ]);

        $this->actingAs($otherUser)
            ->getJson("/api/listings/{$listing->id}/analytics")
            ->assertStatus(403);

        $response = $this->actingAs($owner)
            ->getJson("/api/listings/{$listing->id}/analytics");

        $response->assertStatus(200)
            ->assertJsonPath('total_home_impressions', 1)
            ->assertJsonPath('total_clicks', 1)
            ->assertJsonPath('ctr_percent', 100)
            ->assertJsonPath('avg_home_position', 2)
            ->assertJsonCount(7, 'daily_series');
    }
}
