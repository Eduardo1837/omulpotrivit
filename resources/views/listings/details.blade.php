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
                            <a href="{{ route('reviews.create', ['listing_id' => $listing->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                ★ Lasă o recenzie
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
