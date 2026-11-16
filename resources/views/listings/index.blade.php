<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ __('Toate Anunțurile') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Descoperă meșteri și servicii verificate din comunitatea noastră.
                </p>
            </div>
            @auth
                <a href="{{ route('listings.create') }}" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Publică Anunț
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition">
                    Autentificare pentru a posta
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Grid de Anunțuri -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($listings as $item)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                        <div class="p-6">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700">
                                    {{ $item->categoryName }}
                                </span>
                                @if($item->isFeatured)
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 flex items-center gap-1">
                                        ★ Promovat
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 line-clamp-1 hover:text-indigo-600 transition">
                                <a href="{{ route('listings.details', $item->id) }}">
                                    {{ $item->title }}
                                </a>
                            </h3>

                            <p class="text-sm text-gray-600 mt-2 line-clamp-3 leading-relaxed">
                                {{ $item->description }}
                            </p>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $item->city }}
                                </span>
                                <span class="font-semibold text-gray-700 capitalize">
                                    {{ $item->priceType }}
                                </span>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-400 block font-medium">Preț estimat</span>
                                <span class="text-lg font-black text-indigo-600">{{ $item->priceFormatted }}</span>
                            </div>
                            <a href="{{ route('listings.details', $item->id) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 shadow-sm transition">
                                Detalii &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-xl p-12 text-center border border-gray-200">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                        </svg>
                        <h4 class="text-lg font-semibold text-gray-800">Nu a fost găsit niciun anunț</h4>
                        <p class="text-sm text-gray-500 mt-1">Fii primul care adaugă un anunț în această platformă!</p>
                        @auth
                            <a href="{{ route('listings.create') }}" class="mt-4 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
                                Adaugă Primul Anunț
                            </a>
                        @endauth
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
