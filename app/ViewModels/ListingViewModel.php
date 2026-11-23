<?php

namespace App\ViewModels;

use App\Models\Listing;

class ListingViewModel
{
    public int $id;
    public int $providerId;
    public string $providerName;
    public ?string $providerPhone;
    public ?string $providerEmail;
    public string $title;
    public string $description;
    public string $priceFormatted;
    public string $priceType;
    public string $city;
    public string $categoryName;
    public bool $isFeatured;
    public string $status;

    public function __construct(Listing $listing)
    {
        $this->id = $listing->id;
        $this->providerId = (int) $listing->provider_id;
        $this->providerName = $listing->provider?->name ?? 'Furnizor';
        $this->providerPhone = $listing->provider?->phone ?: null;
        $this->providerEmail = $listing->provider?->email ?: null;
        $this->title = $listing->title;
        $this->description = $listing->description;
        $this->priceFormatted = number_format($listing->price, 2) . ' RON';
        $this->priceType = ucfirst($listing->priceType ?? 'fix');
        $this->city = $listing->city ?? 'Nespecificat';
        $this->categoryName = $listing->category ? $listing->category->name : 'Fără categorie';
        $this->isFeatured = (bool) $listing->is_featured;
        $this->status = ucfirst($listing->status ?? 'activ');
    }
}