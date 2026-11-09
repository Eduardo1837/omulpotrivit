<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    /**
     * Transformă resursa într-un array (Mapper automat Model -> DTO/JSON).
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'titlu'          => $this->title,
            'descriere'      => $this->description,
            // Formatare custom (ex: monedă și tip)
            'pret_afisat'    => number_format($this->price, 2) . ' RON',
            'tip_pret'       => ucfirst($this->priceType ?? 'fix'),
            'oras'           => $this->city ?? 'Nespecificat',
            // Preluare automată din relația cu categoria
            'categorie'      => $this->category ? $this->category->name : 'Fără categorie',
            'este_promovat'  => (bool) $this->is_featured,
            'status'         => ucfirst($this->status ?? 'activ'),
            // Excludem câmpuri interne precum provider_id, updated_at etc.
        ];
    }
}