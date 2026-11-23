<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                        {{ __('Planul meu de Promovare') }}
                    </h2>
                    @php
                        $currentPlan = $quotas['current_plan'] ?? null;
                        $isPaidCurrentPlan = $currentPlan && (float) $currentPlan->price > 0;
                        $currentPlanName = $currentPlan?->name ?? 'Gratuit';
                    @endphp
                    <span id="page-plan-badge" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $isPaidCurrentPlan ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                        {{ $isPaidCurrentPlan ? '★ ' : '' }}{{ $currentPlanName }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Alege planul potrivit pentru serviciile tale și gestionează limita de anunțuri promovate.
                </p>
            </div>

            <a href="{{ route('listings.my') }}" class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold px-4 py-2 rounded-lg text-sm transition">
                ← Înapoi la Anunțurile Mele
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div id="plan-feedback" class="hidden px-4 py-3 rounded-xl shadow-sm text-sm border"></div>

            <!-- Promotion Plans Section (loaded via GET /api/plans, activated via POST /api/plans/select) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Planuri de Promovare Disponibile</h3>
                        <p class="text-xs text-gray-500">Selectează un plan (gratuit sau plătit) pentru contul tău. Promovările se aplică din pagina „Anunțurile mele”.</p>
                    </div>
                    <span id="selected-plan-label" class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-200 hidden"></span>
                </div>

                <div id="plans-grid" class="space-y-4">
                    <div class="text-xs text-gray-400 py-4 text-center">Se încarcă planurile de promovare...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mock Payment Portal Modal -->
    <div id="paymentPortalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-gray-200">
            <!-- Modal Header -->
            <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-amber-400 font-bold">Portal de Plată Securizat (Simulare)</p>
                    <h3 id="paymentPlanTitle" class="text-lg font-bold mt-0.5">Activare Plan</h3>
                </div>
                <button type="button" onclick="closePaymentModal()" class="text-gray-400 hover:text-white transition text-xl leading-none">&times;</button>
            </div>

            <div class="p-6">
                <!-- Payment Form State -->
                <div id="paymentFormState" class="space-y-5">
                    <!-- Order Summary -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Total de plată</p>
                            <p id="paymentPlanMeta" class="text-xs font-medium text-gray-700 mt-0.5"></p>
                        </div>
                        <span id="paymentPlanAmount" class="text-xl font-black text-gray-900">0.00 RON</span>
                    </div>

                    <!-- Payment Method Selector -->
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Alege metoda de plată</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    id="methodBtn-card"
                                    onclick="selectPaymentMethod('card')"
                                    class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-amber-500 bg-amber-50 text-amber-900 text-xs font-bold transition">
                                💳 Card Bancar
                            </button>
                            <button type="button"
                                    id="methodBtn-google_play"
                                    onclick="selectPaymentMethod('google_play')"
                                    class="flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 text-xs font-bold transition">
                                ▶ Google Play
                            </button>
                        </div>
                    </div>

                    <!-- Card Mock Form -->
                    <div id="paymentSection-card" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Număr card (demo)</label>
                            <input type="text" value="4242 •••• •••• 4242" class="w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Expirare</label>
                                <input type="text" value="12/28" class="w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">CVV</label>
                                <input type="text" value="123" class="w-full rounded-lg border-gray-300 text-sm focus:border-amber-500 focus:ring-amber-500" />
                            </div>
                        </div>
                    </div>

                    <!-- Google Play Mock Section -->
                    <div id="paymentSection-google_play" class="hidden rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center space-y-1.5">
                        <p class="text-sm font-bold text-emerald-900">▶ Google Play Billing</p>
                        <p class="text-xs text-emerald-700">Plata va fi confirmată instant prin contul tău Google Play (simulare).</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button"
                                id="cancelPaymentBtn"
                                onclick="closePaymentModal()"
                                class="px-4 py-2 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                            Anulează
                        </button>
                        <button type="button"
                                id="confirmPaymentBtn"
                                onclick="processMockPayment()"
                                class="px-5 py-2 rounded-lg text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition">
                            Plătește acum
                        </button>
                    </div>
                </div>

                <!-- Animated Payment Success State -->
                <div id="paymentSuccessState" class="hidden py-6 text-center space-y-4">
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center animate-[popIn_0.4s_ease-out_forwards]">
                            <svg class="w-9 h-9 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path class="checkmark-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-4xl font-black text-amber-500 inline-block animate-[smileBounce_0.8s_ease-in-out_infinite]">:)</span>
                    </div>
                    <div>
                        <h4 class="text-lg font-extrabold text-gray-900">Plată finalizată cu succes!</h4>
                        <p id="paymentSuccessText" class="text-xs text-gray-500 mt-1">Noul tău plan este acum activ.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes drawCheck {
            from { stroke-dashoffset: 36; }
            to { stroke-dashoffset: 0; }
        }
        @keyframes popIn {
            0% { transform: scale(0.4); opacity: 0; }
            70% { transform: scale(1.12); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes smileBounce {
            0%, 100% { transform: translateY(0) rotate(-4deg) scale(1); }
            50% { transform: translateY(-6px) rotate(6deg) scale(1.15); }
        }
        .checkmark-path {
            stroke-dasharray: 36;
            stroke-dashoffset: 36;
            animation: drawCheck 0.45s 0.15s ease-out forwards;
        }
    </style>

    <script>
        let selectedPlanId = @json($activePlanId ?? null);
        let loadedPlans = [];
        let pendingPaymentPlan = null;
        let selectedPaymentMethod = 'card';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function showPlanFeedback(message, isError = false) {
            const banner = document.getElementById('plan-feedback');
            if (!banner) return;
            banner.textContent = message;
            banner.className = isError
                ? 'px-4 py-3 rounded-xl shadow-sm text-sm border bg-rose-50 border-rose-200 text-rose-800'
                : 'px-4 py-3 rounded-xl shadow-sm text-sm border bg-emerald-50 border-emerald-200 text-emerald-800';
            banner.classList.remove('hidden');
        }

        function updatePlanBadges(plan) {
            if (!plan) return;
            const isPaid = Number(plan.price) > 0;
            const label = isPaid ? `★ ${plan.name}` : plan.name;

            const pageBadge = document.getElementById('page-plan-badge');
            if (pageBadge) {
                pageBadge.textContent = label;
                pageBadge.className = isPaid
                    ? 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200'
                    : 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200';
            }

            const navBadge = document.getElementById('nav-plan-badge');
            if (navBadge) {
                navBadge.textContent = label;
                navBadge.className = isPaid
                    ? 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200'
                    : 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600 border border-gray-200';
            }

            const dropdownBadge = document.getElementById('dropdown-plan-badge');
            if (dropdownBadge) {
                dropdownBadge.textContent = plan.name;
                dropdownBadge.className = isPaid
                    ? 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800'
                    : 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600';
            }
        }

        function openPaymentModal(plan) {
            pendingPaymentPlan = plan;
            selectedPaymentMethod = 'card';
            selectPaymentMethod('card');

            document.getElementById('paymentFormState').classList.remove('hidden');
            document.getElementById('paymentSuccessState').classList.add('hidden');
            document.getElementById('paymentPlanTitle').textContent = `Cumpără planul ${plan.name}`;
            document.getElementById('paymentPlanAmount').textContent = `${Number(plan.price).toFixed(2)} ${plan.currency}`;
            document.getElementById('paymentPlanMeta').textContent = plan.durationDays > 0
                ? `Valabil ${plan.durationDays} zile`
                : 'Activare imediată';

            const btn = document.getElementById('confirmPaymentBtn');
            btn.disabled = false;
            btn.textContent = 'Plătește acum';
            document.getElementById('paymentPortalModal').classList.remove('hidden');
        }

        function closePaymentModal() {
            pendingPaymentPlan = null;
            document.getElementById('paymentPortalModal').classList.add('hidden');
        }

        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            const methods = ['card', 'google_play'];
            methods.forEach(m => {
                const btn = document.getElementById(`methodBtn-${m}`);
                const sec = document.getElementById(`paymentSection-${m}`);
                const isActive = m === method;
                if (btn) {
                    btn.className = isActive
                        ? 'flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border-2 border-amber-500 bg-amber-50 text-amber-900 text-xs font-bold transition'
                        : 'flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 text-xs font-bold transition';
                }
                if (sec) {
                    sec.classList.toggle('hidden', !isActive);
                }
            });
        }

        async function processMockPayment() {
            if (!pendingPaymentPlan) return;
            const btn = document.getElementById('confirmPaymentBtn');
            btn.disabled = true;
            btn.textContent = 'Se procesează plata...';

            await new Promise(resolve => setTimeout(resolve, 650));
            const planId = pendingPaymentPlan.id;
            const planName = pendingPaymentPlan.name;
            const method = selectedPaymentMethod;

            const success = await commitPlanSelection(planId, method);
            if (success) {
                document.getElementById('paymentFormState').classList.add('hidden');
                document.getElementById('paymentSuccessText').textContent = `Planul „${planName}” este acum activ pe contul tău.`;
                document.getElementById('paymentSuccessState').classList.remove('hidden');

                await new Promise(resolve => setTimeout(resolve, 1400));
                closePaymentModal();
            } else {
                btn.disabled = false;
                btn.textContent = 'Plătește acum';
            }
        }

        function activatePlan(planId) {
            const plan = loadedPlans.find(p => Number(p.id) === Number(planId));
            if (plan && Number(plan.price) > 0) {
                openPaymentModal(plan);
                return;
            }
            commitPlanSelection(planId, 'card');
        }

        async function commitPlanSelection(planId, paymentMethod = 'card') {
            try {
                const response = await fetch('/api/plans/select', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ plan_id: planId, payment_method: paymentMethod }),
                });

                const data = await response.json();
                if (!response.ok) {
                    showPlanFeedback(data.message || 'Nu s-a putut activa planul.', true);
                    return false;
                }

                selectedPlanId = planId;
                const plan = loadedPlans.find(p => p.id === planId) || data.plan;
                if (plan) {
                    updatePlanBadges(plan);
                    const label = document.getElementById('selected-plan-label');
                    if (label) {
                        label.textContent = `Plan activ: ${plan.name} (${Number(plan.price).toFixed(2)} ${plan.currency})`;
                        label.classList.remove('hidden');
                    }
                }
                renderPlans();
                showPlanFeedback(data.message || 'Planul a fost actualizat!');
                return true;
            } catch (error) {
                showPlanFeedback('Eroare de rețea la activarea planului.', true);
                return false;
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function renderPlans() {
            const grid = document.getElementById('plans-grid');
            if (!grid) return;

            if (!loadedPlans.length) {
                grid.innerHTML = '<div class="text-xs text-gray-500 py-4 text-center">Nu există planuri configurate.</div>';
                return;
            }

            // Sort descending by price so the highest-priced (most noticeable) plan appears first
            const sortedPlans = [...loadedPlans].sort((a, b) => Number(b.price) - Number(a.price));
            const maxPaidPrice = Math.max(0, ...sortedPlans.map(p => Number(p.price)));

            grid.innerHTML = sortedPlans.map(plan => {
                const price = Number(plan.price);
                const isPaid = price > 0;
                const isTopTier = isPaid && price === maxPaidPrice;
                const isMidTier = isPaid && price < maxPaidPrice;
                const isSelected = Number(selectedPlanId) === Number(plan.id);

                let rowClass = 'bg-white border border-gray-200';
                let titleClass = 'text-base font-semibold text-gray-800';
                let priceBadgeClass = 'bg-gray-100 text-gray-700';
                let tierBadgeHtml = '';
                let buttonClass = isSelected
                    ? 'bg-gray-800 text-white'
                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50';

                if (isTopTier) {
                    rowClass = 'bg-gradient-to-r from-amber-50 via-yellow-50/70 to-white border-2 border-amber-400 shadow-md';
                    titleClass = 'text-lg font-extrabold text-gray-900';
                    priceBadgeClass = 'bg-amber-100 text-amber-900 border border-amber-300';
                    tierBadgeHtml = '<span class="text-[11px] font-extrabold uppercase tracking-wide px-2.5 py-0.5 rounded-full bg-amber-500 text-white shadow-xs">★ Recomandat</span>';
                    buttonClass = isSelected
                        ? 'bg-amber-600 text-white shadow-sm'
                        : 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm';
                } else if (isMidTier) {
                    rowClass = 'bg-indigo-50/40 border border-indigo-200 shadow-sm';
                    titleClass = 'text-base font-bold text-gray-900';
                    priceBadgeClass = 'bg-indigo-100 text-indigo-800 border border-indigo-200';
                    tierBadgeHtml = '<span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700">Popular</span>';
                    buttonClass = isSelected
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white border border-indigo-300 text-indigo-700 hover:bg-indigo-50';
                }

                if (isSelected) {
                    rowClass += ' ring-2 ring-amber-300';
                }

                const maxItemsText = plan.maxItems ? `Max ${plan.maxItems} anunț(uri) promovate` : 'Anunțuri promovate nelimitate';
                const durationText = plan.durationDays > 0 ? `${plan.durationDays} zile promovare` : 'Fără promovare';
                const descriptionHtml = plan.description
                    ? `<p class="text-sm text-gray-600 mt-1">${escapeHtml(plan.description)}</p>`
                    : '';

                return `
                    <div class="rounded-xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 transition ${rowClass}">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h4 class="${titleClass}">${escapeHtml(plan.name)}</h4>
                                ${tierBadgeHtml}
                                ${isSelected ? '<span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">✓ Plan Activ</span>' : ''}
                            </div>
                            ${descriptionHtml}
                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 pt-1">
                                <span>• ${durationText}</span>
                                <span>• ${isPaid ? maxItemsText : '1 anunț gratuit activ'}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-4 shrink-0">
                            <span class="text-sm font-extrabold px-3 py-1.5 rounded-full ${priceBadgeClass}">
                                ${price.toFixed(2)} ${escapeHtml(plan.currency)}
                            </span>
                            <button type="button" onclick="activatePlan(${plan.id})" class="py-2 px-4 rounded-lg text-xs font-bold transition ${buttonClass}">
                                ${isSelected ? '✓ Plan Activ' : 'Alege Planul'}
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

                if (!selectedPlanId) {
                    const freePlan = loadedPlans.find(p => Number(p.price) <= 0);
                    if (freePlan) selectedPlanId = freePlan.id;
                }

                const active = loadedPlans.find(p => Number(p.id) === Number(selectedPlanId));
                const label = document.getElementById('selected-plan-label');
                if (label && active) {
                    label.textContent = `Plan activ: ${active.name} (${Number(active.price).toFixed(2)} ${active.currency})`;
                    label.classList.remove('hidden');
                }

                renderPlans();
            } catch (error) {
                const grid = document.getElementById('plans-grid');
                if (grid) {
                    grid.innerHTML = `<div class="col-span-full text-xs text-rose-600 py-4 text-center">${error.message}</div>`;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', loadPlans);
    </script>
</x-app-layout>
