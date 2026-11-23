<?php

namespace Tests\Unit;

use App\Services\CategoryService;
use Tests\TestCase;

class ServiceLifetimeTest extends TestCase
{
    public function test_transient_binding_creates_new_instance_every_time(): void
    {
        $this->app->bind(CategoryService::class);

        $first = $this->app->make(CategoryService::class);
        $second = $this->app->make(CategoryService::class);

        $this->assertNotSame(spl_object_id($first), spl_object_id($second));
    }

    public function test_scoped_binding_shares_instance_within_scope_and_resets_across_scopes(): void
    {
        $this->app->scoped(CategoryService::class);

        $firstInScope = $this->app->make(CategoryService::class);
        $secondInScope = $this->app->make(CategoryService::class);

        $this->assertSame(spl_object_id($firstInScope), spl_object_id($secondInScope));

        // Simulează trecerea la o nouă cerere HTTP / un nou scope
        $this->app->forgetScopedInstances();

        $firstInNextScope = $this->app->make(CategoryService::class);

        $this->assertNotSame(spl_object_id($firstInScope), spl_object_id($firstInNextScope));
    }

    public function test_singleton_binding_shares_instance_across_scopes(): void
    {
        $this->app->singleton(CategoryService::class);

        $first = $this->app->make(CategoryService::class);

        // Chiar și după resetarea instanțelor scoped, singleton-ul rămâne același
        $this->app->forgetScopedInstances();

        $second = $this->app->make(CategoryService::class);

        $this->assertSame(spl_object_id($first), spl_object_id($second));
    }
}
