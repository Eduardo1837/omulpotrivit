<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.listings.create'))->assertForbidden();
    }

    public function test_admin_can_crud_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Test Provider',
                'email' => 'provider@example.com',
                'password' => 'password123',
                'role' => 'user',
                'city' => 'Suceava',
            ])
            ->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'provider@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('Test Provider', $createdUser->name);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $createdUser->id), [
                'name' => 'Updated Provider',
                'email' => 'provider@example.com',
                'role' => 'admin',
                'city' => 'Iași',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'name' => 'Updated Provider',
            'role' => 'admin',
            'city' => 'Iași',
        ]);

        // Admin cannot delete themselves
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin->id))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Admin can delete other users
        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $createdUser->id))
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $createdUser->id]);
    }

    public function test_admin_can_crud_listings_with_foreign_keys(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $providerA = User::factory()->create(['role' => 'user', 'name' => 'Provider A']);
        $providerB = User::factory()->create(['role' => 'user', 'name' => 'Provider B']);
        $catA = Category::create(['name' => 'IT & Programare', 'description' => 'Servicii IT']);
        $catB = Category::create(['name' => 'Curățenie', 'description' => 'Servicii curățenie']);

        $this->actingAs($admin)
            ->get(route('admin.listings.create'))
            ->assertOk()
            ->assertSee($catA->name)
            ->assertSee($providerA->name);

        $this->actingAs($admin)
            ->post(route('admin.listings.store'), [
                'title' => 'Admin Created Listing',
                'description' => 'Full stack development service',
                'price' => 250,
                'priceType' => 'fix',
                'city' => 'Suceava',
                'category_id' => $catA->id,
                'provider_id' => $providerA->id,
                'status' => 'activ',
                'is_featured' => '1',
            ])
            ->assertRedirect(route('admin.listings.index'));

        $listing = Listing::where('title', 'Admin Created Listing')->first();
        $this->assertNotNull($listing);
        $this->assertSame($catA->id, $listing->category_id);
        $this->assertSame($providerA->id, $listing->provider_id);
        $this->assertTrue((bool) $listing->is_featured);

        $this->actingAs($admin)
            ->get(route('admin.listings.edit', $listing->id))
            ->assertOk()
            ->assertSee($catB->name)
            ->assertSee($providerB->name);

        $this->actingAs($admin)
            ->put(route('admin.listings.update', $listing->id), [
                'title' => 'Admin Updated Listing',
                'category_id' => $catB->id,
                'provider_id' => $providerB->id,
                'status' => 'inactiv',
            ])
            ->assertRedirect(route('admin.listings.index'));

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'title' => 'Admin Updated Listing',
            'category_id' => $catB->id,
            'provider_id' => $providerB->id,
            'status' => 'inactiv',
            'is_featured' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.listings.destroy', $listing->id), [
                'confirm_delete' => 'delete',
            ])
            ->assertRedirect(route('admin.listings.index'));

        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }
}
