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

            <!-- Promotion Plans Section (loaded via GET /api/plans) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Planuri de Promovare Disponibile</h3>
                        <p class="text-xs text-gray-500">Selectează un plan (gratuit sau plătit), apoi apasă „Aplică Planul” pe oricare dintre anunțurile tale.</p>
                    </div>
                    <span id="selected-plan-label" class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 hidden"></span>
                </div>

                <div id="plans-grid" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="col-span-full text-xs text-gray-400 py-4 text-center">Se încarcă planurile de promovare...</div>
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

                                    @if($listing->is_featured)
                                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 flex items-center gap-1">
                                            ★ Promovat (Plătit)
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                            Nepromovat (Plan Gratuit)
                                        </span>
                                    @endif

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
                            @if($listing->is_featured)
                                <!-- STATISTICI DETALIATE (UTILIZATOR PROMOVAT) -->
                                <div>
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
        let loadedPlans = [];
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

        function selectPlan(planId) {
            selectedPlanId = planId;
            const plan = loadedPlans.find(p => p.id === planId);
            const label = document.getElementById('selected-plan-label');
            if (label && plan) {
                label.textContent = `Plan selectat: ${plan.name} (${Number(plan.price).toFixed(2)} ${plan.currency})`;
                label.classList.remove('hidden');
            }
            renderPlans();
        }

        function renderPlans() {
            const grid = document.getElementById('plans-grid');
            if (!grid) return;

            if (!loadedPlans.length) {
                grid.innerHTML = '<div class="col-span-full text-xs text-gray-500 py-4 text-center">Nu există planuri configurate.</div>';
                return;
            }

            grid.innerHTML = loadedPlans.map(plan => {
                const isPaid = Number(plan.price) > 0;
                const isSelected = selectedPlanId === plan.id;
                const borderClass = isSelected
                    ? 'border-amber-500 ring-2 ring-amber-200 bg-amber-50/30'
                    : 'border-gray-200 bg-gray-50/40';
                const maxItemsText = plan.maxItems ? `Max ${plan.maxItems} anunț(uri)` : 'Anunțuri nelimitate';
                const durationText = plan.durationDays > 0 ? `${plan.durationDays} zile promovare` : 'Fără promovare';

                return `
                    <div class="rounded-xl border p-4 flex flex-col justify-between transition ${borderClass}">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-bold text-gray-900 text-sm">${plan.name}</h4>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full ${isPaid ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-700'}">
                                    ${Number(plan.price).toFixed(2)} ${plan.currency}
                                </span>
                            </div>
                            <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                <li>• ${durationText}</li>
                                <li>• ${maxItemsText}</li>
                            </ul>
                        </div>
                        <div class="mt-3">
                            <button type="button" onclick="selectPlan(${plan.id})" class="w-full py-1.5 px-3 rounded-lg text-xs font-semibold transition ${isSelected ? 'bg-amber-500 text-white' : 'bg-white border border-amber-300 text-amber-700 hover:bg-amber-50'}">
                                ${isSelected ? '✓ Plan Selectat' : 'Selectează Planul'}
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function loadPlans() {
            try {
                const response = await fetch('/api/plans', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Eroare la încărcarea planurilor.');
                loadedPlans = await response.json();
                if (selectedPlanId && loadedPlans.some(p => p.id === Number(selectedPlanId))) {
                    selectPlan(Number(selectedPlanId));
                } else {
                    const defaultPaid = loadedPlans.find(p => Number(p.price) > 0);
                    if (defaultPaid) {
                        selectPlan(defaultPaid.id);
                    } else {
                        renderPlans();
                    }
                }
            } catch (error) {
                const grid = document.getElementById('plans-grid');
                if (grid) {
                    grid.innerHTML = `<div class="col-span-full text-xs text-rose-600 py-4 text-center">${error.message}</div>`;
                }
            }
        }

        async function promoteListing(listingId) {
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

                showApiFeedback(data.message || 'Anunțul a fost promovat!');
                setTimeout(() => window.location.reload(), 600);
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

                showApiFeedback(data.message || 'Promovarea a fost anulată.');
                setTimeout(() => window.location.reload(), 600);
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
            const currentPlan = loadedPlans.find(p => p.id === selectedPlanId);
            const currentMax = currentPlan?.maxItems ?? 0;
            const higherPlan = loadedPlans.find(p =>
                Number(p.price) > 0 &&
                p.id !== selectedPlanId &&
                (p.maxItems === null || Number(p.maxItems) > Number(currentMax))
            );

            if (higherPlan) {
                selectPlan(higherPlan.id);
                showApiFeedback(`Planul „${higherPlan.name}” a fost selectat pentru upgrade. Apasă din nou „★ Promovează Anunțul” pe anunțul dorit.`);
            }

            const plansGrid = document.getElementById('plans-grid');
            if (plansGrid) {
                plansGrid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
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
                showApiFeedback(data.message || 'Anunțul a fost șters permanent.');
            } catch (error) {
                showApiFeedback('Eroare de rețea la ștergerea anunțului.', true);
            }
        }

        document.addEventListener('DOMContentLoaded', loadPlans);
    </script>
</x-app-layout>
