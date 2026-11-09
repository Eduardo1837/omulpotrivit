<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ __('Anunțurile Mele') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gestionează-ți serviciile publicate și consultă performanța acestora.
                </p>
            </div>

            @if($canCreateMore)
                <a href="{{ route('listings.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Publică Anunț Nou
                </a>
            @else
                <div class="inline-flex items-center gap-2 px-3.5 py-2 bg-amber-50 text-amber-800 rounded-lg border border-amber-200 text-xs font-semibold">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Limită de 1 anunț atinsă (Plan Gratuit)
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Messages -->
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

            <!-- Info Banner despre Anunțuri Nepromovate vs Promovate -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Reguli Plan & Statistici</h3>
                            <p class="text-xs text-gray-600 mt-0.5">
                                Utilizatorii cu anunțuri nepromovate au acces la <strong>maximum 1 anunț gratuit</strong> și nu au acces la statisticile detaliate. Promovează anunțul pentru a debloca anunțuri multiple și statistici complete de vizualizare.
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-xs text-gray-400 block font-medium">Anunțuri publicate de tine</span>
                        <span class="text-xl font-black text-gray-800">{{ $listings->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- List of Listings -->
            <div class="space-y-6">
                @forelse($listings as $listing)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
                        
                        <!-- Header Card Anunț -->
                        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                        {{ $listing->category->name ?? 'Fără categorie' }}
                                    </span>

                                    @if($listing->is_featured)
                                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 flex items-center gap-1">
                                            ★ Promovat (Plătit)
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                            Nepromovat (Plan Gratuit)
                                        </span>
                                    @endif

                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $listing->status === 'activ' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($listing->status ?? 'activ') }}
                                    </span>
                                </div>

                                <h3 class="text-xl font-bold text-gray-900">
                                    <a href="{{ route('listings.details', $listing->id) }}" class="hover:text-indigo-600 transition">
                                        {{ $listing->title }}
                                    </a>
                                </h3>

                                <div class="mt-1 flex items-center gap-4 text-xs text-gray-500">
                                    <span>Oraș: <strong>{{ $listing->city ?? 'Nespecificat' }}</strong></span>
                                    <span>•</span>
                                    <span>Preț: <strong class="text-gray-800">{{ number_format($listing->price, 2) }} RON</strong> ({{ $listing->priceType ?? 'fix' }})</span>
                                    <span>•</span>
                                    <span>Adăugat: {{ $listing->created_at->format('d M Y') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('listings.details', $listing->id) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                    Vezi Anunț
                                </a>
                                <a href="{{ route('listings.edit', $listing->id) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition">
                                    Editează
                                </a>
                            </div>
                        </div>

                        <!-- Secțiune Statistici: Detaliate dacă e promovat vs Blocate dacă e nepromovat -->
                        <div class="p-6 bg-gray-50/60">
                            @if($listing->is_featured)
                                <!-- STATISTICI DETALIATE (UTILIZATOR PROMOVAT) -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Statistici Detaliate de Performanță (Anunț Promovat)</h4>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-xs">
                                            <div class="text-xs text-gray-500 font-medium">Vizualizări totale</div>
                                            <div class="text-2xl font-black text-indigo-600 mt-1">{{ $listing->views_count ?? 0 }}</div>
                                            <div class="text-xs text-emerald-600 mt-1 font-semibold flex items-center gap-0.5">
                                                ↑ +35% săptămâna aceasta
                                            </div>
                                        </div>

                                        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-xs">
                                            <div class="text-xs text-gray-500 font-medium">Recenzii primite</div>
                                            <div class="text-2xl font-black text-gray-800 mt-1">{{ $listing->reviews->count() }}</div>
                                            <div class="text-xs text-gray-400 mt-1">de la clienți verificați</div>
                                        </div>

                                        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-xs">
                                            <div class="text-xs text-gray-500 font-medium">Rating Mediu</div>
                                            <div class="text-2xl font-black text-amber-500 mt-1">
                                                {{ $listing->reviews->avg('rating') ? number_format($listing->reviews->avg('rating'), 1) . ' ★' : '—' }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-1">Scor satisfacție</div>
                                        </div>

                                        <div class="bg-white p-4 rounded-xl border border-gray-200/80 shadow-xs">
                                            <div class="text-xs text-gray-500 font-medium">Poziționare Căutare</div>
                                            <div class="text-2xl font-black text-emerald-600 mt-1">TOP 3</div>
                                            <div class="text-xs text-emerald-600 mt-1 font-semibold">Prioritate maximă</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- STATISTICI BLOCATE (UTILIZATOR NEPROMOVAT / NEPLĂTIT) -->
                                <div class="bg-white rounded-xl border border-dashed border-gray-300 p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-sm font-bold text-gray-800">Statistici Detaliate Blocate</h4>
                                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">Necesită Promovare</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                Statisticile detaliate (număr vizualizări, conversii, rapoarte audiență) nu sunt disponibile pentru anunțurile nepromovate. De asemenea, pe planul gratuit poți deține maximum 1 anunț.
                                            </p>
                                        </div>
                                    </div>

                                    <a href="{{ route('listings.edit', $listing->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-xs shadow-sm transition shrink-0">
                                        ★ Promovează Anunțul
                                    </a>
                                </div>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="bg-white rounded-xl p-12 text-center border border-gray-200">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h4 class="text-lg font-semibold text-gray-800">Nu ai publicat niciun anunț până acum</h4>
                        <p class="text-sm text-gray-500 mt-1">Publică primul tău anunț gratuit pentru a începe!</p>
                        <a href="{{ route('listings.create') }}" class="mt-4 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
                            Publică Primul Anunț
                        </a>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
