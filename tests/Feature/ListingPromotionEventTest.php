<?php

namespace Tests\Feature;

use App\Events\ListingPromoted;
use App\Models\Category;
use App\Models\Listing;
use App\Models\PromotionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListingPromotionEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_promoting_listing_via_api_creates_notification_and_stat_event(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT & Programare', 'description' => 'Servicii IT']);
        $listing = Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Dezvoltare Web Laravel',
            'description' => 'Servicii complete de dezvoltare web.',
            'price' => 250.00,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);
        $plan = PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29.00,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/listings/{$listing->id}/promote", [
            'plan_id' => $plan->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('listing.este_promovat', true);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'kind' => 'listing_promoted',
            'isRead' => false,
        ]);

        $this->assertDatabaseHas('stat_events', [
            'viewer_id' => $user->id,
            'listing_id' => $listing->id,
            'eventType' => 'promovare',
        ]);

        $this->assertDatabaseHas('promotions', [
            'user_id' => $user->id,
            'listing_id' => $listing->id,
            'plan_id' => $plan->id,
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 29.00,
            'currency' => 'RON',
            'status' => 'COMPLETED',
        ]);
    }

    public function test_switching_to_free_plan_does_not_dispatch_listing_promoted_event(): void
    {
        Event::fake([ListingPromoted::class]);

        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'Design', 'description' => 'Servicii design']);
        $listing = Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Logo Design',
            'description' => 'Creare logo profesional.',
            'price' => 150.00,
            'priceType' => 'fix',
            'city' => 'Iași',
            'status' => 'activ',
            'is_featured' => true,
        ]);
        $freePlan = PromotionPlan::create([
            'name' => 'Free',
            'price' => 0.00,
            'currency' => 'RON',
            'durationDays' => 0,
            'maxItems' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/listings/{$listing->id}/promote", [
            'plan_id' => $freePlan->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('listing.este_promovat', false);

        Event::assertNotDispatched(ListingPromoted::class);
    }
}
