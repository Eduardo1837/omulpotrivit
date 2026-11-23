<?php

namespace Tests\Feature;

use App\Events\ListingPromoted;
use App\Events\NotificationCreated;
use App\Models\Category;
use App\Models\Listing;
use App\Models\PromotionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificationBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_promoting_a_listing_dispatches_notification_created_broadcast_event(): void
    {
        Event::fake([NotificationCreated::class]);

        $user = User::factory()->create();
        $category = Category::create(['name' => 'IT', 'description' => 'IT']);
        $plan = PromotionPlan::create([
            'name' => 'Pro 7 Days',
            'price' => 29.00,
            'currency' => 'RON',
            'durationDays' => 7,
            'maxItems' => 5,
        ]);

        $listing = Listing::create([
            'provider_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Test Service',
            'description' => 'Desc',
            'price' => 100,
            'priceType' => 'fix',
            'city' => 'Suceava',
            'status' => 'activ',
            'is_featured' => true,
        ]);

        ListingPromoted::dispatch($listing, $user->id, $plan);

        Event::assertDispatched(NotificationCreated::class, function (NotificationCreated $event) use ($user) {
            $channels = $event->broadcastOn();
            return (int) $event->notification->user_id === (int) $user->id
                && $channels[0]->name === 'private-App.Models.User.' . $user->id
                && $event->broadcastAs() === 'notification.created';
        });
    }
}
