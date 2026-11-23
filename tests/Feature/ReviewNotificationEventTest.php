<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewNotificationEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_review_via_api_notifies_listing_provider(): void
    {
        $provider = User::factory()->create(['name' => 'Maria Ionescu', 'role' => 'user']);
        $reviewer = User::factory()->create(['name' => 'Andrei Popescu', 'role' => 'user']);
        $category = Category::create(['name' => 'Gastronomie', 'description' => 'Servicii culinare']);
        $listing = Listing::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'title' => 'Torturi Personalizate',
            'description' => 'Torturi artizanale pentru evenimente.',
            'price' => 180.00,
            'priceType' => 'fix',
            'city' => 'Botoșani',
            'status' => 'activ',
            'is_featured' => false,
        ]);

        Sanctum::actingAs($reviewer);

        $response = $this->postJson("/api/listings/{$listing->id}/reviews", [
            'rating' => 5,
            'comment' => 'Serviciu excelent!',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $provider->id,
            'kind' => 'review_received',
            'isRead' => false,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $reviewer->id,
            'kind' => 'review_received',
        ]);
    }
}
