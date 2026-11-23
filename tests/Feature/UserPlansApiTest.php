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

        $freePlan = PromotionPlan::create([
            'name' => 'Free',
            'price' => 0,
            'currency' => 'RON',
            'durationDays' => 0,
            'maxItems' => 1,
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

        // Owner can switch back to Free plan
        $this->actingAs($owner)
            ->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $freePlan->id])
            ->assertStatus(200)
            ->assertJsonPath('listing.este_promovat', false);

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'is_featured' => 0,
        ]);

        // Promoting a non-featured listing with Free plan is blocked (requires upgrade)
        $this->actingAs($owner)
            ->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $freePlan->id])
            ->assertStatus(422)
            ->assertJson([
                'limit_reached' => true,
                'plan_name' => 'Free',
                'max_items' => 0,
            ]);

        // Permanent delete requires typing "delete"
        $this->actingAs($owner)
            ->deleteJson("/api/listings/{$listing->id}", ['confirm_delete' => 'wrong'])
            ->assertStatus(422);

        $this->actingAs($owner)
            ->deleteJson("/api/listings/{$listing->id}", ['confirm_delete' => 'delete'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }

    public function test_my_listings_page_renders_api_controls_and_quotas_without_plans_grid(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/my-listings');

        $response->assertStatus(200)
            ->assertSee('Anunțuri gratuite disponibile')
            ->assertSee('Promovări rămase')
            ->assertSee('/api/listings/')
            ->assertDontSee('Planuri de Promovare Disponibile');
    }

    public function test_dedicated_plans_page_renders_and_user_can_select_plan_via_api(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $freePlan = PromotionPlan::create([
            'name' => 'Free',
            'price' => 0,
            'currency' => 'RON',
            'durationDays' => 0,
            'maxItems' => 1,
        ]);
        $paidPlan = PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        // Dedicated plans page renders plans container, payment modal, and current plan badge
        $this->actingAs($user)
            ->get('/my-plan')
            ->assertStatus(200)
            ->assertSee('Planuri de Promovare Disponibile')
            ->assertSee('paymentPortalModal')
            ->assertSee('Google Play')
            ->assertSee('/api/plans')
            ->assertSee($freePlan->name);

        // User can select a plan via API with a chosen payment method (e.g., google_play)
        $this->actingAs($user)
            ->postJson('/api/plans/select', [
                'plan_id' => $paidPlan->id,
                'payment_method' => 'google_play',
            ])
            ->assertStatus(200)
            ->assertJsonPath('plan.id', $paidPlan->id)
            ->assertJsonPath('quotas.promotions_left', 5);

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'method' => 'google_play',
            'status' => 'COMPLETED',
        ]);

        // Navigation badge reflects selected plan
        $this->actingAs($user)
            ->get('/my-listings')
            ->assertStatus(200)
            ->assertSee('★ Pro 7 Days')
            ->assertSee('5');
    }

    public function test_promotion_plans_support_description_and_logical_is_deleted(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $activePlan = PromotionPlan::create([
            'name' => 'Starter',
            'description' => 'Descriere plan Starter',
            'price' => 15,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 2,
        ]);

        $deletedPlan = PromotionPlan::create([
            'name' => 'Archived Plan',
            'description' => 'Plan arhivat',
            'price' => 99,
            'currency' => 'RON',
            'durationDays' => 30,
            'maxItems' => null,
            'is_deleted' => true,
        ]);

        $this->getJson('/api/plans')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'name' => 'Starter',
                'description' => 'Descriere plan Starter',
            ])
            ->assertJsonMissing(['name' => 'Archived Plan']);

        $this->actingAs($user)
            ->postJson('/api/plans/select', ['plan_id' => $deletedPlan->id])
            ->assertStatus(404);

        $this->actingAs($user)
            ->postJson('/api/plans/select', ['plan_id' => $activePlan->id])
            ->assertStatus(200);
    }

    public function test_promote_and_demote_return_updated_quotas_via_api(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT', 'description' => 'IT services']);

        $listing = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Servicii Test',
            'description' => 'Descriere test',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        $freePlan = PromotionPlan::create([
            'name' => 'Free',
            'price' => 0,
            'currency' => 'RON',
            'durationDays' => 0,
            'maxItems' => 1,
        ]);

        $paidPlan = PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        // Select paid plan first
        $this->actingAs($owner)
            ->postJson('/api/plans/select', ['plan_id' => $paidPlan->id])
            ->assertStatus(200)
            ->assertJsonPath('quotas.free_listings_left', 0)
            ->assertJsonPath('quotas.promotions_left', 5);

        // Promoting listing frees up the free listing slot and consumes 1 promotion
        $this->actingAs($owner)
            ->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $paidPlan->id])
            ->assertStatus(200)
            ->assertJsonPath('quotas.free_listings_left', 1)
            ->assertJsonPath('quotas.promotions_left', 4);

        // GET /api/my-listings also returns the fresh quotas
        $this->actingAs($owner)
            ->getJson('/api/my-listings')
            ->assertStatus(200)
            ->assertJsonPath('quotas.free_listings_left', 1)
            ->assertJsonPath('quotas.promotions_left', 4);

        // Demoting listing back to free restores the promotion slot and uses the free listing slot
        $this->actingAs($owner)
            ->postJson("/api/listings/{$listing->id}/promote", ['plan_id' => $freePlan->id])
            ->assertStatus(200)
            ->assertJsonPath('quotas.free_listings_left', 0)
            ->assertJsonPath('quotas.promotions_left', 5);
    }

    public function test_promotion_is_blocked_when_plan_max_items_limit_is_reached(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT', 'description' => 'IT services']);

        $alreadyPromoted = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Anunț Promovat 1',
            'description' => 'Descriere',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        $secondListing = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Anunț Nepromovat 2',
            'description' => 'Descriere',
            'price' => 120,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        $limitedPlan = PromotionPlan::create([
            'name' => 'Starter 1',
            'price' => 15,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 1,
        ]);

        $unlimitedPlan = PromotionPlan::create([
            'name' => 'Business 30 Days',
            'price' => 79,
            'currency' => 'RON',
            'durationDays' => 30,
            'maxItems' => null,
        ]);

        // Blocked because user already has 1 promoted listing and Starter 1 allows maxItems = 1
        $this->actingAs($owner)
            ->postJson("/api/listings/{$secondListing->id}/promote", ['plan_id' => $limitedPlan->id])
            ->assertStatus(422)
            ->assertJson([
                'limit_reached' => true,
                'plan_name' => 'Starter 1',
                'max_items' => 1,
                'current_count' => 1,
            ]);

        // Succeeds after upgrading to unlimited plan
        $this->actingAs($owner)
            ->postJson("/api/listings/{$secondListing->id}/promote", ['plan_id' => $unlimitedPlan->id])
            ->assertStatus(200)
            ->assertJsonPath('listing.este_promovat', true);

        // Reloading /my-listings preserves the upgraded plan as active
        $this->actingAs($owner)
            ->get('/my-listings')
            ->assertStatus(200)
            ->assertViewHas('activePlanId', $unlimitedPlan->id);
    }

    public function test_edit_listing_renders_tailwind_layout_and_updates_redirect_to_my_listings(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT', 'description' => 'IT services']);

        $listing = Listing::create([
            'provider_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Titlu Vechi',
            'description' => 'Descriere',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        $this->actingAs($owner)
            ->get("/listings/{$listing->id}/edit")
            ->assertStatus(200)
            ->assertSee('Editează Anunțul')
            ->assertSee(route('listings.my'));

        $this->actingAs($owner)
            ->post("/listings/{$listing->id}/edit", [
                'title' => 'Titlu Nou',
                'description' => 'Descriere nouă',
                'price' => 150,
                'priceType' => 'fix',
                'city' => 'Suceava',
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('listings.my'));
    }

    public function test_creating_a_listing_redirects_to_my_listings(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT', 'description' => 'IT services']);

        $this->actingAs($owner)
            ->post('/listings', [
                'title' => 'Anunț Nou',
                'description' => 'Descriere test',
                'price' => 200,
                'priceType' => 'fix',
                'city' => 'Suceava',
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('listings.my'));
    }

    public function test_api_home_and_categories_return_active_data(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'IT & Web', 'description' => 'Dezvoltare web']);

        Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Site Web Rapid',
            'description' => 'Descriere activă',
            'price' => 500,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Anunț Inactiv',
            'description' => 'Ascuns',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'inactiv',
            'is_featured' => false,
        ]);

        $this->getJson('/api/home')
            ->assertStatus(200)
            ->assertJsonCount(1, 'listings')
            ->assertJsonPath('listings.0.titlu', 'Site Web Rapid')
            ->assertJsonFragment(['name' => 'IT & Web']);

        $this->getJson('/api/categories')
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'IT & Web']);
    }

    public function test_api_create_update_and_show_own_listing_with_ownership_checks(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'Design', 'description' => 'Grafică']);

        // Unauthenticated create is rejected
        $this->postJson('/api/listings', [
            'title' => 'Test',
            'description' => 'Desc',
            'price' => 100,
            'city' => 'Iași',
            'category_id' => $category->id,
        ])->assertStatus(401);

        // Owner creates listing via API
        $createRes = $this->actingAs($owner)->postJson('/api/listings', [
            'title' => 'Logo Design',
            'description' => 'Creare logo profesional',
            'price' => 250,
            'priceType' => 'fix',
            'city' => 'Iași',
            'category_id' => $category->id,
            'is_featured' => false,
        ])->assertStatus(201)
            ->assertJsonPath('titlu', 'Logo Design');

        $listingId = $createRes->json('id');

        // Owner can fetch my-listings and own listing
        $this->actingAs($owner)
            ->getJson('/api/my-listings')
            ->assertStatus(200)
            ->assertJsonCount(1, 'listings')
            ->assertJsonPath('listings.0.id', $listingId);

        $this->actingAs($owner)
            ->getJson("/api/listings/{$listingId}/own")
            ->assertStatus(200)
            ->assertJsonPath('titlu', 'Logo Design');

        // Non-owner cannot view own edit payload or update
        $this->actingAs($otherUser)
            ->getJson("/api/listings/{$listingId}/own")
            ->assertStatus(403);

        $this->actingAs($otherUser)
            ->putJson("/api/listings/{$listingId}", ['title' => 'Hack'])
            ->assertStatus(403);

        // Owner can update via API
        $this->actingAs($owner)
            ->putJson("/api/listings/{$listingId}", [
                'title' => 'Logo Design Premium',
                'price' => 300,
            ])
            ->assertStatus(200)
            ->assertJsonPath('titlu', 'Logo Design Premium')
            ->assertJsonPath('pret', 300);
    }

    public function test_api_reviews_crud_for_listing(): void
    {
        $provider = User::factory()->create(['role' => 'user']);
        $reviewer = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'Instalații', 'description' => 'Servicii casă']);

        $listing = Listing::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Reparații Sanitare',
            'description' => 'Intervenții rapide',
            'price' => 150,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
        ]);

        // Unauthenticated review post rejected
        $this->postJson("/api/listings/{$listing->id}/reviews", [
            'rating' => 5,
            'comment' => 'Super',
        ])->assertStatus(401);

        // Reviewer creates review
        $createRes = $this->actingAs($reviewer)
            ->postJson("/api/listings/{$listing->id}/reviews", [
                'rating' => 5,
                'comment' => 'Serviciu excelent!',
            ])
            ->assertStatus(201)
            ->assertJsonPath('review.comment', 'Serviciu excelent!');

        $reviewId = $createRes->json('review.id');

        // Duplicate review by same user is blocked
        $this->actingAs($reviewer)
            ->postJson("/api/listings/{$listing->id}/reviews", [
                'rating' => 4,
                'comment' => 'Încă o recenzie',
            ])
            ->assertStatus(422);

        // Show listing API includes the review
        $this->actingAs($reviewer)
            ->getJson("/api/listings/{$listing->id}")
            ->assertStatus(200)
            ->assertJsonCount(1, 'reviews')
            ->assertJsonPath('user_has_review', true);

        // Other user cannot edit or delete someone else's review
        $this->actingAs($otherUser)
            ->putJson("/api/reviews/{$reviewId}", ['rating' => 1, 'comment' => 'Spam'])
            ->assertStatus(403);

        $this->actingAs($otherUser)
            ->deleteJson("/api/reviews/{$reviewId}")
            ->assertStatus(403);

        // Author can update and delete their own review
        $this->actingAs($reviewer)
            ->putJson("/api/reviews/{$reviewId}", [
                'rating' => 4,
                'comment' => 'Foarte bun!',
            ])
            ->assertStatus(200)
            ->assertJsonPath('review.comment', 'Foarte bun!');

        $this->actingAs($reviewer)
            ->deleteJson("/api/reviews/{$reviewId}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('reviews', ['id' => $reviewId]);
    }

    public function test_api_auth_register_login_and_logout(): void
    {
        $registerRes = $this->postJson('/api/register', [
            'name' => 'Client API',
            'email' => 'client.api@example.com',
            'password' => 'parola1234',
            'password_confirmation' => 'parola1234',
            'city' => 'Cluj-Napoca',
        ])->assertStatus(201)
            ->assertJsonStructure(['token', 'user']);

        $token = $registerRes->json('token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJsonPath('email', 'client.api@example.com');

        $loginRes = $this->postJson('/api/login', [
            'email' => 'client.api@example.com',
            'password' => 'parola1234',
        ])->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $loginToken = $loginRes->json('token');

        $this->withToken($loginToken)
            ->postJson('/api/logout')
            ->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
