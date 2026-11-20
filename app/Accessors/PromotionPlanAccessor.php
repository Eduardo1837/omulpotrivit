<?php

namespace App\Accessors;

use App\Models\Promotion;
use App\Models\PromotionPlan;
use Illuminate\Database\Eloquent\Collection;

class PromotionPlanAccessor
{
    /**
     * Returnează planurile de promovare ordonate după preț (implicit doar cele vizibile).
     */
    public function getAll(bool $visibleOnly = true): Collection
    {
        $query = PromotionPlan::orderBy('price');

        if ($visibleOnly) {
            $query->where('is_hidden', false);
        }

        return $query->get();
    }

    /**
     * Găsește un plan de promovare după ID.
     */
    public function getById(int|string $id): ?PromotionPlan
    {
        return PromotionPlan::find($id);
    }

    /**
     * Creează un plan de promovare nou.
     */
    public function create(array $data): PromotionPlan
    {
        return PromotionPlan::create($data);
    }

    /**
     * Actualizează un plan de promovare existent.
     */
    public function update(int|string $id, array $data): bool
    {
        $plan = PromotionPlan::find($id);
        return $plan ? $plan->update($data) : false;
    }

    /**
     * Șterge un plan de promovare după ID.
     */
    public function delete(int|string $id): bool
    {
        return (bool) PromotionPlan::destroy($id);
    }

    /**
     * Comută vizibilitatea (is_hidden) a unui plan de promovare.
     */
    public function toggleHidden(int|string $id): ?PromotionPlan
    {
        $plan = PromotionPlan::find($id);
        if (!$plan) {
            return null;
        }

        $plan->update(['is_hidden' => !$plan->is_hidden]);

        return $plan->fresh();
    }

    /**
     * Verifică dacă planul are promovări asociate.
     */
    public function hasPromotions(int|string $id): bool
    {
        $plan = PromotionPlan::find($id);

        return $plan ? $plan->promotions()->exists() : false;
    }

    /**
     * Înregistrează o promovare activă pentru utilizator și anunț.
     */
    public function recordUserPromotion(int $userId, int|string $listingId, PromotionPlan $plan): Promotion
    {
        return Promotion::create([
            'plan_id' => $plan->id,
            'user_id' => $userId,
            'listing_id' => $listingId,
            'startsAt' => now(),
            'endsAt' => $plan->durationDays > 0 ? now()->addDays($plan->durationDays) : null,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Returnează ID-ul ultimului plan activ selectat/folosit de utilizator.
     */
    public function getLatestActivePlanIdForUser(int|string $userId): ?int
    {
        $promotion = Promotion::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->latest('id')
            ->first();

        return $promotion?->plan_id;
    }
}
