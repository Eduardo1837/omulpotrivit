<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\PromotionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPlansApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_plans_endpoint_returns_seeded_promotion_plans(): void
    {
        PromotionPlan::create([
            'name' => 'Free',
            'price' => 0,
            'currency' => 'RON',
            'durationDays' => 0,
            'maxItems' => 1,
        ]);
        PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        $response = $this->getJson('/api/plans');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Free'])
            ->assertJsonFragment(['name' => 'Pro 7 Days']);
    }

    public function test_owner_can_promote_and_toggle_listing_status_via_api(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT', 'description' => 'IT services']);

        $listing = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Servicii Web',
            'description' => 'Descriere test',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        $paidPlan = PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        // Unauthenticated requests are rejected
        $this->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $paidPlan->id])
            ->assertStatus(401);

        // Non-owner cannot promote or toggle status
        $this->actingAs($otherUser)
            ->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $paidPlan->id])
            ->assertStatus(403);

        $this->actingAs($otherUser)
            ->patchJson("/api/listings/{$listing->id}/toggle-status")
            ->assertStatus(403);

        // Owner can promote with paid plan
        $this->actingAs($owner)
            ->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $paidPlan->id])
            ->assertStatus(200)
            ->assertJsonPath('listing.este_promovat', true);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'is_featured' => 1,
            'status' => 'activ',
        ]);

        // Owner can toggle status to inactiv and back to activ
        $this->actingAs($owner)
            ->patchJson("/api/listings/{$listing->id}/toggle-status")
            ->assertStatus(200)
            ->assertJsonPath('listing.status', 'Inactiv');

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'inactiv',
        ]);

        $this->actingAs($owner)
            ->patchJson("/api/listings/{$listing->id}/toggle-status")
            ->assertStatus(200)
            ->assertJsonPath('listing.status', 'Activ');
    }

    public function test_my_listings_page_renders_api_controls_and_plans_container(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/my-listings');

        $response->assertStatus(200)
            ->assertSee('Planuri de Promovare Disponibile')
            ->assertSee('/api/plans');
    }
}
