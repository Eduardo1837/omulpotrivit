<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalii Anunț') }}
            </h2>
            <a href="{{ route('listings.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                &larr; Înapoi la toate anunțurile
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-sm text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="p-6 md:p-8">
                    <!-- Badges -->
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700">
                            {{ $listing->categoryName }}
                        </span>
                        @if($listing->isFeatured)
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-100 text-amber-800">
                                ★ Promovat
                            </span>
                        @endif
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">
                            Status: {{ $listing->status }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-4">
                        {{ $listing->title }}
                    </h1>

                    <!-- Price & Location Callout -->
                    <div class="bg-indigo-50/50 rounded-xl p-4 md:p-6 mb-6 flex flex-wrap items-center justify-between gap-4 border border-indigo-100">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 block">Preț Solicitat</span>
                            <span class="text-2xl md:text-3xl font-black text-indigo-600">{{ $listing->priceFormatted }}</span>
                            <span class="text-xs text-gray-500 font-medium">({{ $listing->priceType }})</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 block">Locație</span>
                            <span class="text-base font-bold text-gray-800 flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $listing->city }}
                            </span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="prose max-w-none mb-8">
                        <h3 class="text-sm font-bold uppercase text-gray-400 tracking-wider mb-2">Descriere Serviciu</h3>
                        <p class="text-gray-700 whitespace-pre-line leading-relaxed text-base">
                            {{ $listing->description }}
                        </p>
                    </div>

                    <!-- Action Bar -->
                    <div class="pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            &larr; Înapoi la lista de anunțuri
                        </a>

                        @auth
                            <a href="#leave-review" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                ★ Lasă o recenzie
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6 md:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Recenzii ({{ $reviews->count() }})
                        </h3>
                        @if($reviews->count() > 0)
                            <p class="text-xs text-gray-500 mt-0.5">
                                Rating mediu: <span class="font-semibold text-amber-600">★ {{ number_format($reviews->avg('rating'), 1) }} / 5</span>
                            </p>
                        @endif
                    </div>
                </div>

                @auth
                    <form id="leave-review" action="{{ route('reviews.store') }}" method="POST" class="bg-gray-50 rounded-xl p-4 md:p-5 border border-gray-200/80 space-y-4">
                        @csrf
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <input type="hidden" name="redirect_to_listing" value="1">

                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-bold text-gray-800">Adaugă o recenzie</span>
                            <div class="flex items-center gap-2">
                                <label for="rating" class="text-xs font-semibold text-gray-600">Rating:</label>
                                <select id="rating" name="rating" required class="border-gray-300 rounded-lg text-sm py-1 px-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>★★★★★ (5)</option>
                                    <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>★★★★☆ (4)</option>
                                    <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>★★★☆☆ (3)</option>
                                    <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>★★☆☆☆ (2)</option>
                                    <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>★☆☆☆☆ (1)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <textarea name="comment" rows="3" required
                                      placeholder="Scrie părerea ta despre acest serviciu..."
                                      class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comment') }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                Trimite Recenzia
                            </button>
                        </div>
                    </form>
                @endauth

                <div class="space-y-4">
                    @forelse($reviews as $review)
                        <div class="border border-gray-100 rounded-xl p-4 bg-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-gray-900">{{ $review->author->name ?? 'Utilizator' }}</span>
                                    <span class="text-xs text-amber-500 font-bold">
                                        {{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $review->created_at?->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $review->comment }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Nu există recenzii pentru acest anunț încă.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
