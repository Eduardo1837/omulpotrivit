<?php

namespace App\Accessors;

use App\Models\Payment;
use App\Models\Promotion;
use App\Models\PromotionPlan;
use Illuminate\Database\Eloquent\Collection;

class PromotionPlanAccessor
{
    /**
     * Returnează planurile de promovare ordonate după preț (excluzând cele șterse logic; implicit doar cele vizibile).
     */
    public function getAll(bool $visibleOnly = true): Collection
    {
        $query = PromotionPlan::where('is_deleted', false)->orderBy('price');

        if ($visibleOnly) {
            $query->where('is_hidden', false);
        }

        return $query->get();
    }

    /**
     * Găsește un plan de promovare după ID (excluzând cele șterse logic).
     */
    public function getById(int|string $id): ?PromotionPlan
    {
        return PromotionPlan::where('is_deleted', false)->find($id);
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
     * Șterge logic un plan de promovare după ID (is_deleted = true).
     */
    public function delete(int|string $id): bool
    {
        $plan = PromotionPlan::where('is_deleted', false)->find($id);
        return $plan ? $plan->update(['is_deleted' => true]) : false;
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
     * Înregistrează o promovare activă pentru utilizator și anunț, închizând promovările anterioare
     * și salvând tranzacția în payments pentru planurile plătite.
     */
    public function recordUserPromotion(int $userId, int|string $listingId, PromotionPlan $plan): Promotion
    {
        Promotion::where('listing_id', $listingId)
            ->where('status', 'ACTIVE')
            ->update([
                'status' => 'REPLACED',
                'endsAt' => now(),
            ]);

        $promotion = Promotion::create([
            'plan_id' => $plan->id,
            'user_id' => $userId,
            'listing_id' => $listingId,
            'startsAt' => now(),
            'endsAt' => $plan->durationDays > 0 ? now()->addDays($plan->durationDays) : null,
            'status' => 'ACTIVE',
        ]);

        if ((float) $plan->price > 0) {
            Payment::create([
                'user_id' => $userId,
                'promotion_id' => $promotion->id,
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'RON',
                'method' => 'card',
                'status' => 'COMPLETED',
                'paidAt' => now(),
            ]);
        }

        return $promotion;
    }

    /**
     * Returnează planul gratuit implicit (primul plan vizibil cu preț 0).
     */
    public function getFreePlan(): ?PromotionPlan
    {
        return PromotionPlan::where('is_deleted', false)
            ->where('is_hidden', false)
            ->where('price', '<=', 0)
            ->orderBy('id')
            ->first();
    }

    /**
     * Setează planul activ la nivel de utilizator (fără a fi legat de un anunț specific).
     */
    public function setUserPlan(int $userId, PromotionPlan $plan, string $paymentMethod = 'card'): Promotion
    {
        Promotion::where('user_id', $userId)
            ->whereNull('listing_id')
            ->where('status', 'ACTIVE')
            ->update([
                'status' => 'REPLACED',
                'endsAt' => now(),
            ]);

        $promotion = Promotion::create([
            'plan_id' => $plan->id,
            'user_id' => $userId,
            'listing_id' => null,
            'startsAt' => now(),
            'endsAt' => $plan->durationDays > 0 ? now()->addDays($plan->durationDays) : null,
            'status' => 'ACTIVE',
        ]);

        if ((float) $plan->price > 0) {
            Payment::create([
                'user_id' => $userId,
                'promotion_id' => $promotion->id,
                'amount' => $plan->price,
                'currency' => $plan->currency ?? 'RON',
                'method' => $paymentMethod,
                'status' => 'COMPLETED',
                'paidAt' => now(),
            ]);
        }

        return $promotion;
    }

    /**
     * Returnează ID-ul ultimului plan activ selectat/folosit de utilizator.
     * Prioritizăm abonamentul la nivel de utilizator (listing_id = null) dacă există.
     */
    public function getLatestActivePlanIdForUser(int|string $userId): ?int
    {
        $userPlan = Promotion::where('user_id', $userId)
            ->whereNull('listing_id')
            ->where('status', 'ACTIVE')
            ->latest('id')
            ->first();

        if ($userPlan) {
            return $userPlan->plan_id;
        }

        $promotion = Promotion::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->latest('id')
            ->first();

        return $promotion?->plan_id;
    }

    /**
     * Returnează istoricul complet al promovărilor/abonamentelor unui utilizator.
     */
    public function getUserPromotionsHistory(int|string $userId): Collection
    {
        return Promotion::with(['plan', 'listing', 'payment'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get();
    }
}
