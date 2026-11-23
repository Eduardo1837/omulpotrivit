<?php

namespace App\Services;

use App\Accessors\ListingAccessor;
use App\Accessors\PromotionPlanAccessor;
use App\Events\ListingPromoted;
use App\Models\Listing;
use App\Models\PromotionPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ListingService
{
    public const CACHE_KEY_HOME_LISTINGS = 'home.listings.active';
    public const CACHE_KEY_ACTIVE_LISTINGS = 'listings.active.all';
    public const CACHE_KEY_PROMOTION_PLANS = 'promotion_plans.all';
    public const CACHE_TTL_SECONDS = 300;

    public function __construct(
        protected ListingAccessor $listingAccessor,
        protected PromotionPlanAccessor $promotionPlanAccessor
    ) {
    }

    /**
     * Returnează listările active pentru pagina principală (prioritizând cele promovate), din cache.
     */
    public function getHomeListings(int $limit = 6): Collection
    {
        $cacheKey = $limit === 6
            ? self::CACHE_KEY_HOME_LISTINGS
            : self::CACHE_KEY_HOME_LISTINGS . '.' . $limit;

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($limit) {
            return $this->listingAccessor->getActiveHomeListings($limit);
        });
    }

    /**
     * Returnează toate listările active, din cache.
     */
    public function getAllActiveListings(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE_LISTINGS, self::CACHE_TTL_SECONDS, function () {
            return $this->listingAccessor->getActiveAll();
        });
    }

    /**
     * Returnează numărul total de listări pentru panoul de administrare.
     */
    public function countAllListings(): int
    {
        return $this->listingAccessor->countAll();
    }

    /**
     * Returnează toate listările (inclusiv inactive) pentru panoul de administrare.
     */
    public function getAllListingsForAdmin(): Collection
    {
        return $this->listingAccessor->getAll();
    }

    /**
     * Găsește o listare activă după ID (pentru vizualizare publică).
     */
    public function getActiveListingById(int|string $id): ?Listing
    {
        return $this->listingAccessor->getActiveById($id);
    }

    /**
     * Găsește o listare după ID indiferent de status (pentru editare de către proprietar/admin).
     */
    public function getListingById(int|string $id): ?Listing
    {
        return $this->listingAccessor->getById($id);
    }

    /**
     * Filtrează listările active după categorie.
     */
    public function getActiveListingsByCategory(int|string $categoryId): Collection
    {
        return $this->listingAccessor->getActiveByCategoryId($categoryId);
    }

    /**
     * Returnează listările unui utilizator (cu recenzii vizibile).
     */
    public function getUserListings(int|string $userId, bool $activeOnly = true): Collection
    {
        return $this->listingAccessor->getByUserId($userId, $activeOnly);
    }

    /**
     * Returnează date mockup (nepersisate) pentru previzualizarea secțiunii „Anunțurile Mele” de către admin.
     */
    public function getAdminPreviewMockupListings(): SupportCollection
    {
        $featured = new Listing([
            'category_id' => 1,
            'title' => 'Exemplu Anunț Promovat — Dezvoltare Web & Aplicații',
            'description' => 'Serviciu complet de dezvoltare aplicații web Laravel, API REST și interfețe moderne Tailwind CSS. Exemplu mockup pentru previzualizarea panoului de utilizator.',
            'city' => 'Suceava',
            'price' => 350,
            'priceType' => 'fix',
            'status' => 'activ',
            'is_featured' => true,
            'views_count' => 148,
        ]);
        $featured->id = 1;
        $featured->created_at = now()->subDays(5);
        $featured->setRelation('category', (object) ['id' => 1, 'name' => 'IT & Programare']);
        $featured->setRelation('reviews', collect([
            (object) ['id' => 1, 'author_id' => 0, 'rating' => 5, 'comment' => 'Serviciu excelent, livrare rapidă!', 'created_at' => now()->subDays(2), 'author' => (object) ['name' => 'Client Demo 1']],
            (object) ['id' => 2, 'author_id' => 0, 'rating' => 4, 'comment' => 'Comunicare foarte bună și cod curat.', 'created_at' => now()->subDay(), 'author' => (object) ['name' => 'Client Demo 2']],
        ]));

        $free = new Listing([
            'category_id' => 2,
            'title' => 'Exemplu Anunț Gratuit — Instalații & Reparații',
            'description' => 'Intervenții rapide pentru instalații sanitare și reparații casnice. Exemplu mockup pentru planul gratuit.',
            'city' => 'Iași',
            'price' => 120,
            'priceType' => 'pe ora',
            'status' => 'activ',
            'is_featured' => false,
            'views_count' => 24,
        ]);
        $free->id = 2;
        $free->created_at = now()->subDays(12);
        $free->setRelation('category', (object) ['id' => 2, 'name' => 'Casă & Grădină']);
        $free->setRelation('reviews', collect());

        return collect([$featured, $free]);
    }

    /**
     * Găsește un anunț mockup după ID pentru previzualizarea de către admin.
     */
    public function getAdminPreviewMockupListingById(int|string $id): ?Listing
    {
        return $this->getAdminPreviewMockupListings()
            ->first(fn (Listing $item) => (int) $item->id === (int) $id);
    }

    /**
     * Returnează planurile de promovare disponibile (din cache pentru cele vizibile).
     */
    public function getPromotionPlans(bool $visibleOnly = true): Collection
    {
        if (!$visibleOnly) {
            return $this->promotionPlanAccessor->getAll(visibleOnly: false);
        }

        return Cache::remember(self::CACHE_KEY_PROMOTION_PLANS, self::CACHE_TTL_SECONDS, function () {
            return $this->promotionPlanAccessor->getAll(visibleOnly: true);
        });
    }

    /**
     * Returnează ID-ul ultimului plan activ al utilizatorului.
     */
    public function getUserActivePlanId(int|string $userId): ?int
    {
        return $this->promotionPlanAccessor->getLatestActivePlanIdForUser($userId);
    }

    /**
     * Returnează planul activ curent al utilizatorului (sau planul gratuit implicit).
     */
    public function getUserCurrentPlan(?int $userId): ?PromotionPlan
    {
        if (!$userId) {
            return null;
        }

        $planId = $this->getUserActivePlanId($userId);
        if ($planId) {
            $plan = $this->promotionPlanAccessor->getById($planId);
            if ($plan) {
                return $plan;
            }
        }

        return $this->promotionPlanAccessor->getFreePlan();
    }

    /**
     * Selectează/activează un plan de promovare pentru utilizator.
     */
    public function selectUserPlan(int $userId, int $planId): ?PromotionPlan
    {
        $plan = $this->promotionPlanAccessor->getById($planId);
        if (!$plan || $plan->is_hidden || $plan->is_deleted) {
            return null;
        }

        $this->promotionPlanAccessor->setUserPlan($userId, $plan);

        Log::info('User selected promotion plan', [
            'user_id' => $userId,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
        ]);

        return $plan;
    }

    /**
     * Returnează cotele rămase pentru utilizator: anunțuri gratuite și promovări disponibile.
     */
    public function getUserQuotas(?int $userId): array
    {
        $currentPlan = $this->getUserCurrentPlan($userId);

        if (!$userId) {
            return [
                'current_plan' => null,
                'free_listings_limit' => 1,
                'free_listings_used' => 0,
                'free_listings_left' => 0,
                'promotions_limit' => 0,
                'promotions_used' => 0,
                'promotions_left' => 0,
            ];
        }

        $freeUsed = $this->listingAccessor->countFreeByUserId($userId, activeOnly: true);
        $freeLimit = 1;
        $freeLeft = max(0, $freeLimit - $freeUsed);

        $promotedUsed = $this->listingAccessor->countFeaturedByUserId($userId, activeOnly: true);
        $isPaidPlan = $currentPlan && (float) $currentPlan->price > 0;

        if (!$isPaidPlan) {
            $promotionsLimit = 0;
            $promotionsLeft = 0;
        } elseif ($currentPlan->maxItems === null) {
            $promotionsLimit = null;
            $promotionsLeft = null;
        } else {
            $promotionsLimit = (int) $currentPlan->maxItems;
            $promotionsLeft = max(0, $promotionsLimit - $promotedUsed);
        }

        return [
            'current_plan' => $currentPlan,
            'free_listings_limit' => $freeLimit,
            'free_listings_used' => $freeUsed,
            'free_listings_left' => $freeLeft,
            'promotions_limit' => $promotionsLimit,
            'promotions_used' => $promotedUsed,
            'promotions_left' => $promotionsLeft,
        ];
    }

    /**
     * Returnează istoricul abonamentelor/promovărilor unui utilizator.
     */
    public function getUserPromotionsHistory(int|string $userId): Collection
    {
        return $this->promotionPlanAccessor->getUserPromotionsHistory($userId);
    }

    /**
     * Verifică dacă utilizatorul poate crea un anunț nou conform regulii planului gratuit:
     * - Un utilizator poate avea maximum 1 anunț gratuit activ (is_featured = false).
     * - Anunțurile promovate (is_featured = true) sunt nelimitate și NU deblochează alte anunțuri gratuite.
     */
    public function canUserCreateListing(?int $userId, bool $isFeatured = false): bool
    {
        if (!$userId) {
            return false;
        }

        if ($isFeatured) {
            return true;
        }

        $activeFreeCount = $this->listingAccessor->countFreeByUserId($userId, activeOnly: true);

        if ($activeFreeCount >= 1) {
            Log::warning('Listing creation blocked by free-plan quota', [
                'user_id' => $userId,
                'active_free_listings_count' => $activeFreeCount,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Creează o listare nouă, invalidează cache-ul și înregistrează în log.
     */
    public function createListing(array $data, int $userId): Listing
    {
        $data['provider_id'] = $userId;
        $data['status'] = $data['status'] ?? 'activ';
        $data['is_featured'] = $data['is_featured'] ?? false;

        $listing = $this->listingAccessor->create($data);
        $this->invalidateListingsCache();

        Log::info('Listing created', [
            'listing_id' => $listing->id,
            'provider_id' => $userId,
            'category_id' => $listing->category_id,
        ]);

        return $listing;
    }

    /**
     * Actualizează o listare existentă, invalidează cache-ul și înregistrează în log.
     */
    public function updateListing(int|string $id, array $data, ?int $actingUserId = null): bool
    {
        $updated = $this->listingAccessor->update($id, $data);

        if ($updated) {
            $this->invalidateListingsCache();

            Log::info('Listing updated', [
                'listing_id' => $id,
                'acting_user_id' => $actingUserId,
                'updated_fields' => array_keys($data),
            ]);

            if (($data['is_featured'] ?? false) === true) {
                Log::info('Listing promoted', [
                    'listing_id' => $id,
                    'acting_user_id' => $actingUserId,
                ]);
            }
        }

        return $updated;
    }

    /**
     * Verifică dacă utilizatorul a atins limita de anunțuri promovate pentru planul selectat.
     * Returnează detalii despre limită dacă este atinsă, sau null dacă promovarea este permisă.
     */
    public function checkPromotionPlanLimit(int $userId, int|string $listingId, ?int $planId = null): ?array
    {
        if (!$planId) {
            return null;
        }

        $plan = $this->promotionPlanAccessor->getById($planId);
        if (!$plan) {
            return null;
        }

        $listing = $this->listingAccessor->getById($listingId);
        if ($listing && $listing->is_featured) {
            return null;
        }

        if ((float) $plan->price <= 0) {
            Log::warning('Listing promotion blocked: free plan does not allow promoted listings', [
                'listing_id' => $listingId,
                'user_id' => $userId,
                'plan_id' => $plan->id,
            ]);

            return [
                'plan_name' => $plan->name,
                'max_items' => 0,
                'current_count' => $this->listingAccessor->countFeaturedByUserId($userId, activeOnly: true),
            ];
        }

        if ($plan->maxItems === null) {
            return null;
        }

        $currentPromotedCount = $this->listingAccessor->countFeaturedByUserId($userId, activeOnly: true);
        if ($currentPromotedCount >= (int) $plan->maxItems) {
            Log::warning('Listing promotion blocked by plan maxItems limit', [
                'listing_id' => $listingId,
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'max_items' => $plan->maxItems,
                'current_promoted_count' => $currentPromotedCount,
            ]);

            return [
                'plan_name' => $plan->name,
                'max_items' => (int) $plan->maxItems,
                'current_count' => $currentPromotedCount,
            ];
        }

        return null;
    }

    /**
     * Promovează o listare a utilizatorului (activează is_featured = true sau false pentru plan gratuit).
     */
    public function promoteListing(int|string $listingId, int $userId, ?int $planId = null): bool
    {
        $listing = $this->listingAccessor->getById($listingId);

        if (!$listing || (int) $listing->provider_id !== $userId) {
            Log::warning('Unauthorized or missing listing promotion attempt', [
                'listing_id' => $listingId,
                'user_id' => $userId,
            ]);
            return false;
        }

        if ($this->checkPromotionPlanLimit($userId, $listingId, $planId) !== null) {
            return false;
        }

        $plan = $planId ? $this->promotionPlanAccessor->getById($planId) : null;
        $isFeatured = $plan ? ((float) $plan->price > 0) : true;

        $updated = $this->listingAccessor->update($listingId, [
            'is_featured' => $isFeatured,
            'status' => 'activ',
        ]);

        if ($updated) {
            if ($plan) {
                $this->promotionPlanAccessor->recordUserPromotion($userId, $listingId, $plan);
            }
            $this->invalidateListingsCache();
            Log::info($isFeatured ? 'Listing promoted via plan' : 'Listing switched to free plan', [
                'listing_id' => $listingId,
                'user_id' => $userId,
                'plan_id' => $plan?->id,
            ]);

            if ($isFeatured) {
                $promotedListing = $this->listingAccessor->getById($listingId) ?? $listing;
                ListingPromoted::dispatch($promotedListing, $userId, $plan);
            }
        }

        return $updated;
    }

    /**
     * Comută statusul unei listări între 'activ' și 'inactiv' (ștergere/reactivare logică).
     */
    public function toggleListingStatus(int|string $id, ?int $actingUserId = null): ?Listing
    {
        $listing = $this->listingAccessor->getById($id);
        if (!$listing) {
            return null;
        }

        if ($actingUserId !== null && (int) $listing->provider_id !== $actingUserId) {
            Log::warning('Unauthorized listing status toggle attempt', [
                'listing_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
            return null;
        }

        $newStatus = $listing->status === 'activ' ? 'inactiv' : 'activ';
        $this->listingAccessor->update($id, ['status' => $newStatus]);
        $this->invalidateListingsCache();

        Log::info('Listing status toggled', [
            'listing_id' => $id,
            'status' => $newStatus,
            'acting_user_id' => $actingUserId,
        ]);

        return $this->listingAccessor->getById($id);
    }

    /**
     * Ștergere logică a unei listări (status = 'inactiv'), invalidare cache și logging.
     */
    public function deleteListing(int|string $id, ?int $actingUserId = null): bool
    {
        $deleted = $this->listingAccessor->softDelete($id);

        if ($deleted) {
            $this->invalidateListingsCache();
            Log::info('Listing logically deleted (status set to inactiv)', [
                'listing_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        } else {
            Log::warning('Listing logical deletion failed: listing not found', [
                'listing_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $deleted;
    }

    /**
     * Marchează o listare ca ștearsă (status = 'deleted') pentru păstrarea istoricului, fără a o elimina din DB.
     */
    public function markListingAsDeleted(int|string $id, ?int $actingUserId = null): bool
    {
        $deleted = $this->listingAccessor->markAsDeleted($id);

        if ($deleted) {
            $this->invalidateListingsCache();
            Log::info('Listing marked as deleted (preserved in DB for history)', [
                'listing_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $deleted;
    }

    /**
     * Ștergere permanentă (fizică) a unei listări din baza de date.
     */
    public function forceDeleteListing(int|string $id, ?int $actingUserId = null): bool
    {
        $deleted = $this->listingAccessor->delete($id);

        if ($deleted) {
            $this->invalidateListingsCache();
            Log::warning('Listing permanently deleted', [
                'listing_id' => $id,
                'acting_user_id' => $actingUserId,
            ]);
        }

        return $deleted;
    }

    /**
     * Invalidează cheile de cache pentru listări.
     */
    public function invalidateListingsCache(): void
    {
        Cache::forget(self::CACHE_KEY_HOME_LISTINGS);
        Cache::forget(self::CACHE_KEY_ACTIVE_LISTINGS);
    }

    /**
     * Găsește un plan de promovare după ID.
     */
    public function getPromotionPlanById(int|string $id): ?PromotionPlan
    {
        return $this->promotionPlanAccessor->getById($id);
    }

    /**
     * Creează un plan de promovare nou, invalidează cache-ul și înregistrează în log.
     */
    public function createPromotionPlan(array $data): PromotionPlan
    {
        $plan = $this->promotionPlanAccessor->create($data);
        $this->invalidatePromotionPlansCache();

        Log::info('Promotion plan created', [
            'plan_id' => $plan->id,
            'name' => $plan->name,
            'price' => $plan->price,
        ]);

        return $plan;
    }

    /**
     * Actualizează un plan de promovare existent, invalidează cache-ul și înregistrează în log.
     */
    public function updatePromotionPlan(int|string $id, array $data): bool
    {
        $updated = $this->promotionPlanAccessor->update($id, $data);

        if ($updated) {
            $this->invalidatePromotionPlansCache();

            Log::info('Promotion plan updated', [
                'plan_id' => $id,
                'updated_fields' => array_keys($data),
            ]);
        }

        return $updated;
    }

    /**
     * Comută vizibilitatea (ascuns/vizibil) unui plan de promovare.
     */
    public function togglePromotionPlanVisibility(int|string $id): ?PromotionPlan
    {
        $plan = $this->promotionPlanAccessor->toggleHidden($id);

        if ($plan) {
            $this->invalidatePromotionPlansCache();

            Log::info('Promotion plan visibility toggled', [
                'plan_id' => $id,
                'is_hidden' => $plan->is_hidden,
            ]);
        }

        return $plan;
    }

    /**
     * Șterge un plan de promovare dacă nu are promovări asociate.
     */
    public function deletePromotionPlan(int|string $id): bool
    {
        if ($this->promotionPlanAccessor->hasPromotions($id)) {
            Log::warning('Promotion plan deletion blocked: plan has associated promotions', [
                'plan_id' => $id,
            ]);
            return false;
        }

        $deleted = $this->promotionPlanAccessor->delete($id);

        if ($deleted) {
            $this->invalidatePromotionPlansCache();

            Log::info('Promotion plan deleted', [
                'plan_id' => $id,
            ]);
        }

        return $deleted;
    }

    /**
     * Invalidează cache-ul planurilor de promovare.
     */
    public function invalidatePromotionPlansCache(): void
    {
        Cache::forget(self::CACHE_KEY_PROMOTION_PLANS);
    }
}
