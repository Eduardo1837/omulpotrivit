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

            <a href="{{ route('listings.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Publică Anunț Nou
            </a>
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

            <!-- Dynamic API Feedback Banner -->
            <div id="api-feedback" class="hidden px-4 py-3 rounded-xl shadow-sm text-sm border"></div>

            <!-- Quotas & Current Plan Summary -->
            @php
                $currentPlan = $quotas['current_plan'] ?? null;
                $isPaidCurrentPlan = $currentPlan && (float) $currentPlan->price > 0;
                $currentPlanName = $currentPlan?->name ?? 'Gratuit';
                $freeLeft = $quotas['free_listings_left'] ?? 0;
                $freeLimit = $quotas['free_listings_limit'] ?? 1;
                $promotionsLeft = $quotas['promotions_left'] ?? 0;
                $promotionsLimit = $quotas['promotions_limit'] ?? 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Planul tău curent</p>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $isPaidCurrentPlan ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                {{ $isPaidCurrentPlan ? '★ ' : '' }}{{ $currentPlanName }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('plans.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition shrink-0">
                        Schimbă planul
                    </a>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Anunțuri gratuite disponibile</p>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span id="free-listings-left" class="text-2xl font-black {{ $freeLeft > 0 ? 'text-emerald-600' : 'text-gray-800' }}">{{ $freeLeft }}</span>
                        <span id="free-listings-limit" class="text-xs text-gray-500">din {{ $freeLimit }} gratuit activ</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Promovări rămase</p>
                    <div class="mt-1 flex items-baseline gap-2">
                        @if($promotionsLimit === null)
                            <span id="promotions-left" class="text-2xl font-black text-amber-600">Nelimitat</span>
                            <span id="promotions-limit-label" class="text-xs text-gray-500">conform planului {{ $currentPlanName }}</span>
                        @else
                            <span id="promotions-left" class="text-2xl font-black {{ $promotionsLeft > 0 ? 'text-amber-600' : 'text-gray-800' }}">{{ $promotionsLeft }}</span>
                            <span id="promotions-limit-label" class="text-xs text-gray-500">din {{ $promotionsLimit }} disponibile</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- List of Listings -->
            <div class="space-y-6">
                @forelse($listings as $listing)
                    <div id="listing-card-{{ $listing->id }}" class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
                        
                        <!-- Header Card Anunț -->
                        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                        {{ $listing->category->name ?? 'Fără categorie' }}
                                    </span>

                                    <span id="featured-badge-{{ $listing->id }}"
                                          class="text-xs px-2.5 py-0.5 rounded-full {{ $listing->is_featured ? 'font-bold bg-amber-100 text-amber-800 flex items-center gap-1' : 'font-semibold bg-gray-100 text-gray-600' }}">
                                        {{ $listing->is_featured ? '★ Promovat (Plătit)' : 'Nepromovat (Plan Gratuit)' }}
                                    </span>

                                    <span id="status-badge-{{ $listing->id }}" class="text-xs font-medium px-2 py-0.5 rounded-full {{ $listing->status === 'activ' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($listing->status ?? 'activ') }}
                                    </span>
                                </div>

                                <h3 class="text-xl font-bold text-gray-900">
                                    <a href="{{ ($isAdminPreview ?? false) ? route('listings.preview.details', $listing->id) : route('listings.details', $listing->id) }}" class="hover:text-indigo-600 transition">
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
                                <a href="{{ ($isAdminPreview ?? false) ? route('listings.preview.details', $listing->id) : route('listings.details', $listing->id) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                    Vezi Anunț
                                </a>
                                <a href="{{ ($isAdminPreview ?? false) ? route('listings.preview.edit', $listing->id) : route('listings.edit', $listing->id) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition">
                                    Editează
                                </a>
                                @if(!($isAdminPreview ?? false))
                                    <button
                                        type="button"
                                        id="toggle-btn-{{ $listing->id }}"
                                        onclick="toggleListingStatus({{ $listing->id }})"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition {{ $listing->status === 'activ' ? 'bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                                        {{ $listing->status === 'activ' ? 'Dezactivează' : 'Reactivează' }}
                                    </button>
                                    <button
                                        type="button"
                                        title="Șterge permanent"
                                        aria-label="Șterge permanent"
                                        onclick="openDeleteModal({{ $listing->id }}, '{{ addslashes($listing->title) }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Secțiune Statistici: Detaliate dacă e promovat vs Blocate dacă e nepromovat -->
                        <div class="p-6 bg-gray-50/60">
                            <!-- STATISTICI DETALIATE (UTILIZATOR PROMOVAT) -->
                            <div id="stats-unlocked-{{ $listing->id }}" class="{{ $listing->is_featured ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Statistici Detaliate de Performanță (Anunț Promovat)</h4>
                                    </div>
                                    @if(!($isAdminPreview ?? false))
                                        <button
                                            type="button"
                                            onclick="demoteListing({{ $listing->id }})"
                                            class="px-3 py-1 rounded-lg text-xs font-semibold bg-white hover:bg-rose-50 text-gray-600 hover:text-rose-700 border border-gray-300 hover:border-rose-200 transition">
                                            Anulează Promovarea
                                        </button>
                                    @endif
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

                            <!-- STATISTICI BLOCATE (UTILIZATOR NEPROMOVAT / NEPLĂTIT) -->
                            <div id="stats-locked-{{ $listing->id }}" class="{{ $listing->is_featured ? 'hidden ' : '' }}bg-white rounded-xl border border-dashed border-gray-300 p-5 flex flex-col md:flex-row items-center justify-between gap-4">
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

                                @if($isAdminPreview ?? false)
                                    <a href="{{ route('listings.preview.edit', $listing->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-xs shadow-sm transition shrink-0">
                                        ★ Promovează Anunțul
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        onclick="promoteListing({{ $listing->id }})"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-xs shadow-sm transition shrink-0">
                                        ★ Promovează Anunțul
                                    </button>
                                @endif
                            </div>
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

    <!-- Upgrade / Promotion Limit Reached Modal -->
    <div id="upgradeLimitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Limită de Promovare Atinsă</h3>
            </div>
            <p id="upgradeLimitMessage" class="text-sm text-gray-600 mb-5"></p>
            <div class="flex justify-end gap-2">
                <button type="button"
                        onclick="closeUpgradeModal()"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    Închide
                </button>
                <button type="button"
                        onclick="upgradeToNextPlan()"
                        class="px-4 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition">
                    Fă Upgrade la un Plan Superior
                </button>
            </div>
        </div>
    </div>

    <!-- Jira-style Permanent Delete Confirmation Modal -->
    <div id="deleteListingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Permanent Deletion</h3>
            <p class="text-sm text-gray-600 mb-4">
                You are about to permanently delete <span id="deleteListingTitle" class="font-semibold text-gray-900"></span>.
                This action cannot be undone. Type <code class="px-1.5 py-0.5 bg-gray-100 text-rose-600 rounded font-bold">delete</code> to confirm.
            </p>
            <form id="deleteListingForm" onsubmit="submitDeleteListing(event)">
                <input type="text"
                       id="confirmDeleteInput"
                       name="confirm_delete"
                       autocomplete="off"
                       placeholder="Type delete"
                       oninput="document.getElementById('confirmDeleteSubmit').disabled = this.value.trim() !== 'delete';"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm mb-4">
                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="closeDeleteModal()"
                            class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                            id="confirmDeleteSubmit"
                            disabled
                            class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedPlanId = @json($activePlanId ?? null);
        let loadedPlans = @json($promotionPlans ?? []);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function showApiFeedback(message, isError = false) {
            const banner = document.getElementById('api-feedback');
            if (!banner) return;
            banner.textContent = message;
            banner.className = isError
                ? 'px-4 py-3 rounded-xl shadow-sm text-sm border bg-rose-50 border-rose-200 text-rose-800'
                : 'px-4 py-3 rounded-xl shadow-sm text-sm border bg-emerald-50 border-emerald-200 text-emerald-800';
            banner.classList.remove('hidden');
        }

        function updateQuotasUI(quotas) {
            if (!quotas) return;

            const freeEl = document.getElementById('free-listings-left');
            const freeLimitEl = document.getElementById('free-listings-limit');
            if (freeEl) {
                const freeLeft = Number(quotas.free_listings_left ?? 0);
                freeEl.textContent = freeLeft;
                freeEl.className = `text-2xl font-black ${freeLeft > 0 ? 'text-emerald-600' : 'text-gray-800'}`;
            }
            if (freeLimitEl && quotas.free_listings_limit !== undefined) {
                freeLimitEl.textContent = `din ${quotas.free_listings_limit} gratuit activ`;
            }

            const promoEl = document.getElementById('promotions-left');
            const promoLimitEl = document.getElementById('promotions-limit-label');
            if (promoEl) {
                if (quotas.promotions_limit === null) {
                    promoEl.textContent = 'Nelimitat';
                    promoEl.className = 'text-2xl font-black text-amber-600';
                    if (promoLimitEl) {
                        const planName = quotas.current_plan?.name ?? 'Selectat';
                        promoLimitEl.textContent = `conform planului ${planName}`;
                    }
                } else {
                    const promoLeft = Number(quotas.promotions_left ?? 0);
                    promoEl.textContent = promoLeft;
                    promoEl.className = `text-2xl font-black ${promoLeft > 0 ? 'text-amber-600' : 'text-gray-800'}`;
                    if (promoLimitEl) {
                        promoLimitEl.textContent = `din ${quotas.promotions_limit} disponibile`;
                    }
                }
            }
        }

        async function refreshUserQuotas() {
            try {
                const response = await fetch('/api/my-listings', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;
                const data = await response.json();
                if (data.quotas) {
                    updateQuotasUI(data.quotas);
                }
            } catch (error) {
                // Silent fallback if background quota refresh fails
            }
        }

        function syncListingPromotionUI(listingId, isFeatured, status) {
            const featuredBadge = document.getElementById(`featured-badge-${listingId}`);
            if (featuredBadge) {
                featuredBadge.textContent = isFeatured ? '★ Promovat (Plătit)' : 'Nepromovat (Plan Gratuit)';
                featuredBadge.className = isFeatured
                    ? 'text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 flex items-center gap-1'
                    : 'text-xs px-2.5 py-0.5 rounded-full font-semibold bg-gray-100 text-gray-600';
            }

            const unlocked = document.getElementById(`stats-unlocked-${listingId}`);
            const locked = document.getElementById(`stats-locked-${listingId}`);
            if (unlocked) unlocked.classList.toggle('hidden', !isFeatured);
            if (locked) locked.classList.toggle('hidden', isFeatured);

            if (status) {
                const normalized = status.toLowerCase();
                const statusBadge = document.getElementById(`status-badge-${listingId}`);
                const toggleBtn = document.getElementById(`toggle-btn-${listingId}`);
                if (statusBadge) {
                    statusBadge.textContent = normalized.charAt(0).toUpperCase() + normalized.slice(1);
                    statusBadge.className = normalized === 'activ'
                        ? 'text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700'
                        : 'text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600';
                }
                if (toggleBtn) {
                    toggleBtn.textContent = normalized === 'activ' ? 'Dezactivează' : 'Reactivează';
                    toggleBtn.className = normalized === 'activ'
                        ? 'px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200'
                        : 'px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200';
                }
            }
        }

        async function promoteListing(listingId) {
            const selectedPlan = loadedPlans.find(p => p.id === Number(selectedPlanId));
            if (selectedPlan && Number(selectedPlan.price) <= 0) {
                openUpgradeModal({
                    message: `Planul ${selectedPlan.name} nu include anunțuri promovate. Pentru a promova acest anunț, trebuie să faci upgrade la un plan superior.`,
                });
                return;
            }

            try {
                const response = await fetch(`/api/listings/${listingId}/promote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ plan_id: selectedPlanId }),
                });

                const data = await response.json();
                if (!response.ok) {
                    if (data.limit_reached) {
                        openUpgradeModal(data);
                        return;
                    }
                    showApiFeedback(data.message || 'Promovarea a eșuat.', true);
                    return;
                }

                syncListingPromotionUI(listingId, Boolean(data.listing?.este_promovat), data.listing?.status);
                await refreshUserQuotas();
                window.dispatchEvent(new CustomEvent('notifications-updated'));
                showApiFeedback(data.message || 'Anunțul a fost promovat!');
            } catch (error) {
                showApiFeedback('Eroare de rețea la promovarea anunțului.', true);
            }
        }

        async function demoteListing(listingId) {
            const freePlan = loadedPlans.find(p => Number(p.price) <= 0);
            if (!freePlan) {
                showApiFeedback('Planul gratuit nu este disponibil.', true);
                return;
            }

            try {
                const response = await fetch(`/api/listings/${listingId}/promote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ plan_id: freePlan.id }),
                });

                const data = await response.json();
                if (!response.ok) {
                    showApiFeedback(data.message || 'Anularea promovării a eșuat.', true);
                    return;
                }

                syncListingPromotionUI(listingId, Boolean(data.listing?.este_promovat), data.listing?.status);
                await refreshUserQuotas();
                showApiFeedback(data.message || 'Promovarea a fost anulată.');
            } catch (error) {
                showApiFeedback('Eroare de rețea la anularea promovării.', true);
            }
        }

        async function toggleListingStatus(listingId) {
            try {
                const response = await fetch(`/api/listings/${listingId}/toggle-status`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();
                if (!response.ok) {
                    showApiFeedback(data.message || 'Actualizarea statusului a eșuat.', true);
                    return;
                }

                const newStatus = (data.listing?.status || 'activ').toLowerCase();
                const badge = document.getElementById(`status-badge-${listingId}`);
                const btn = document.getElementById(`toggle-btn-${listingId}`);

                if (badge) {
                    badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    badge.className = newStatus === 'activ'
                        ? 'text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700'
                        : 'text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600';
                }

                if (btn) {
                    btn.textContent = newStatus === 'activ' ? 'Dezactivează' : 'Reactivează';
                    btn.className = newStatus === 'activ'
                        ? 'px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition bg-rose-50 hover:bg-rose-100 text-rose-700 border-rose-200'
                        : 'px-3.5 py-1.5 rounded-lg text-xs font-semibold border transition bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200';
                }

                await refreshUserQuotas();
                showApiFeedback(data.message || 'Statusul a fost actualizat.');
            } catch (error) {
                showApiFeedback('Eroare de rețea la actualizarea statusului.', true);
            }
        }

        function openUpgradeModal(data) {
            const msgEl = document.getElementById('upgradeLimitMessage');
            if (msgEl) {
                msgEl.textContent = data.message || 'Ai atins limita de anunțuri promovate pentru planul curent. Fă upgrade la un plan superior pentru a promova mai multe anunțuri.';
            }
            document.getElementById('upgradeLimitModal').classList.remove('hidden');
        }

        function closeUpgradeModal() {
            document.getElementById('upgradeLimitModal').classList.add('hidden');
        }

        function upgradeToNextPlan() {
            closeUpgradeModal();
            window.location.href = "{{ route('plans.index') }}";
        }

        let deleteListingId = null;

        function openDeleteModal(listingId, title) {
            deleteListingId = listingId;
            document.getElementById('deleteListingTitle').textContent = title;
            const input = document.getElementById('confirmDeleteInput');
            input.value = '';
            document.getElementById('confirmDeleteSubmit').disabled = true;
            document.getElementById('deleteListingModal').classList.remove('hidden');
            input.focus();
        }

        function closeDeleteModal() {
            deleteListingId = null;
            document.getElementById('deleteListingModal').classList.add('hidden');
        }

        async function submitDeleteListing(event) {
            event.preventDefault();
            if (!deleteListingId) return;

            const confirmDelete = document.getElementById('confirmDeleteInput').value.trim();
            try {
                const response = await fetch(`/api/listings/${deleteListingId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ confirm_delete: confirmDelete }),
                });

                const data = await response.json();
                if (!response.ok) {
                    showApiFeedback(data.message || 'Ștergerea anunțului a eșuat.', true);
                    return;
                }

                const card = document.getElementById(`listing-card-${deleteListingId}`);
                if (card) card.remove();
                closeDeleteModal();
                await refreshUserQuotas();
                showApiFeedback(data.message || 'Anunțul a fost șters permanent.');
            } catch (error) {
                showApiFeedback('Eroare de rețea la ștergerea anunțului.', true);
            }
        }
    </script>
</x-app-layout>
