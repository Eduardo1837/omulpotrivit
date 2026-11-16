<?php

namespace App\Accessors;

use App\Models\PromotionPlan;
use Illuminate\Database\Eloquent\Collection;

class PromotionPlanAccessor
{
    /**
     * Returnează toate planurile de promovare ordonate după preț.
     */
    public function getAll(): Collection
    {
        return PromotionPlan::orderBy('price')->get();
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
}
