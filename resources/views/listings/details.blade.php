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

                    <!-- Title & Author -->
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2">
                        {{ $listing->title }}
                    </h1>
                    <p class="text-sm text-gray-500 mb-4 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Publicat de: <span class="font-semibold text-gray-800">{{ $listing->providerName }}</span>
                    </p>

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

                    <!-- Contact Section -->
                    <div class="mb-8 bg-gray-50 rounded-xl p-5 border border-gray-200/80">
                        <h3 class="text-sm font-bold uppercase text-gray-500 tracking-wider mb-3">Contact Furnizor</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="block text-xs text-gray-400 font-medium">Nume</span>
                                <span class="font-semibold text-gray-900">{{ $listing->providerName }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-400 font-medium">Telefon</span>
                                @if($listing->providerPhone)
                                    <a href="tel:{{ $listing->providerPhone }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                        {{ $listing->providerPhone }}
                                    </a>
                                @else
                                    <span class="text-gray-500">Nespecificat</span>
                                @endif
                            </div>
                            <div>
                                <span class="block text-xs text-gray-400 font-medium">Email</span>
                                @if($listing->providerEmail)
                                    <a href="mailto:{{ $listing->providerEmail }}" class="font-semibold text-indigo-600 hover:text-indigo-800 transition break-all">
                                        {{ $listing->providerEmail }}
                                    </a>
                                @else
                                    <span class="text-gray-500">Nespecificat</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @php
                        $userHasReview = auth()->check() && $reviews->contains('author_id', auth()->id());
                    @endphp

                    <!-- Action Bar -->
                    <div class="pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
                        <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            &larr; Înapoi la lista de anunțuri
                        </a>

                        @auth
                            @if(auth()->id() !== (int) ($listing->providerId ?? 0))
                                <a id="leave-review-btn" href="#leave-review" class="{{ $userHasReview ? 'hidden ' : '' }}inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                    ★ Lasă o recenzie
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 p-6 md:p-8 space-y-6">
                <div id="review-api-feedback" class="hidden px-4 py-3 rounded-xl shadow-sm text-sm border"></div>

                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div>
                        <h3 id="reviews-count-heading" class="text-lg font-bold text-gray-900">
                            Recenzii ({{ $reviews->count() }})
                        </h3>
                        <p id="reviews-avg-container" class="text-xs text-gray-500 mt-0.5 {{ $reviews->count() > 0 ? '' : 'hidden' }}">
                            @if($reviews->count() > 0)
                                Rating mediu: <span class="font-semibold text-amber-600">★ {{ number_format($reviews->avg('rating'), 1) }} / 5</span>
                            @endif
                        </p>
                    </div>
                </div>

                @auth
                    @if(auth()->id() !== (int) ($listing->providerId ?? 0))
                        <form id="leave-review" action="{{ route('reviews.store') }}" method="POST" class="{{ $userHasReview ? 'hidden ' : '' }}bg-gray-50 rounded-xl p-4 md:p-5 border border-gray-200/80 space-y-4">
                        @csrf
                        <input type="hidden" name="listing_id" value="{{ $listing->id }}">
                        <input type="hidden" name="redirect_to_listing" value="1">

                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <span class="text-sm font-bold text-gray-800">Adaugă o recenzie</span>
                            <div class="flex items-center gap-3">
                                <input type="hidden" id="rating" name="rating" value="{{ old('rating', 5) }}" required>
                                <div id="star-rating-picker" class="flex items-center gap-1 shrink-0" role="radiogroup" aria-label="Rating">
                                    @for($s = 1; $s <= 5; $s++)
                                        <button type="button"
                                                data-star-value="{{ $s }}"
                                                aria-label="{{ $s }} stele"
                                                class="js-star-btn p-0.5 text-2xl leading-none transition-transform hover:scale-110 focus:outline-none text-amber-400">
                                            ★
                                        </button>
                                    @endfor
                                </div>
                                <span id="rating-label" class="w-48 shrink-0 whitespace-nowrap text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full text-center">
                                    Serviciu excelent!
                                </span>
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
                    @endif
                @endauth

                <div id="reviews-list-container" class="space-y-4">
                    @forelse($reviews as $review)
                        <div class="border border-gray-100 rounded-xl p-4 bg-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-gray-900">{{ $review->author->name ?? 'Utilizator' }}</span>
                                    <span class="text-xs text-amber-500 font-bold">
                                        {{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">{{ $review->created_at?->diffForHumans() }}</span>
                                    @if(auth()->check() && (int) $review->author_id === auth()->id())
                                        <button type="button"
                                                title="Editează recenzia"
                                                aria-label="Editează recenzia"
                                                data-toggle-edit="{{ $review->id }}"
                                                class="js-toggle-edit-btn inline-flex items-center justify-center w-7 h-7 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <form class="js-delete-review-form" data-review-id="{{ $review->id }}" action="{{ route('reviews.destroy', $review->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Șterge recenzia"
                                                    aria-label="Șterge recenzia"
                                                    class="inline-flex items-center justify-center w-7 h-7 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $review->comment }}</p>

                            @if(auth()->check() && (int) $review->author_id === auth()->id())
                                <form id="edit-review-{{ $review->id }}" data-review-id="{{ $review->id }}" action="{{ route('reviews.update', $review->id) }}" method="POST" class="js-edit-review-form hidden mt-4 pt-4 border-t border-gray-100 space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-semibold text-gray-600">Rating:</label>
                                        <select name="rating" required class="border-gray-300 rounded-lg text-sm py-1 px-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                            @for($r = 5; $r >= 1; $r--)
                                                <option value="{{ $r }}" {{ (int) $review->rating === $r ? 'selected' : '' }}>
                                                    {{ str_repeat('★', $r) }}{{ str_repeat('☆', 5 - $r) }} ({{ $r }})
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <textarea name="comment" rows="2" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $review->comment }}</textarea>
                                    <div class="flex justify-end gap-2">
                                        <button type="button"
                                                data-cancel-edit="{{ $review->id }}"
                                                class="js-cancel-edit-btn px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                            Anulează
                                        </button>
                                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                                            Salvează
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">Nu există recenzii pentru acest anunț încă.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @if(!($isAdminPreview ?? false))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const listingId = {{ (int) $listing->id }};
                const currentUserId = {{ auth()->check() ? (int) auth()->id() : 'null' }};
                const csrfToken = '{{ csrf_token() }}';

                const feedbackBox = document.getElementById('review-api-feedback');
                const reviewsContainer = document.getElementById('reviews-list-container');
                const reviewsHeading = document.getElementById('reviews-count-heading');
                const avgContainer = document.getElementById('reviews-avg-container');
                const leaveReviewForm = document.getElementById('leave-review');
                const leaveReviewBtn = document.getElementById('leave-review-btn');

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');

                const showFeedback = (message, isError = false) => {
                    if (!feedbackBox) return;
                    feedbackBox.textContent = message;
                    feedbackBox.className = isError
                        ? 'px-4 py-3 rounded-xl shadow-sm text-sm border bg-rose-50 border-rose-200 text-rose-800'
                        : 'px-4 py-3 rounded-xl shadow-sm text-sm border bg-emerald-50 border-emerald-200 text-emerald-800';
                    feedbackBox.classList.remove('hidden');
                };

                const stars = (rating) => '★'.repeat(rating) + '☆'.repeat(5 - rating);

                const renderReviews = (data) => {
                    const reviews = Array.isArray(data.reviews) ? data.reviews : [];
                    if (reviewsHeading) {
                        reviewsHeading.textContent = `Recenzii (${reviews.length})`;
                    }

                    if (avgContainer) {
                        if (reviews.length > 0) {
                            const sum = reviews.reduce((acc, r) => acc + Number(r.rating || 0), 0);
                            const avg = (sum / reviews.length).toFixed(1);
                            avgContainer.innerHTML = `Rating mediu: <span class="font-semibold text-amber-600">★ ${avg} / 5</span>`;
                            avgContainer.classList.remove('hidden');
                        } else {
                            avgContainer.classList.add('hidden');
                        }
                    }

                    if (leaveReviewForm) {
                        leaveReviewForm.classList.toggle('hidden', Boolean(data.user_has_review));
                    }
                    if (leaveReviewBtn) {
                        leaveReviewBtn.classList.toggle('hidden', Boolean(data.user_has_review));
                    }

                    if (!reviewsContainer) return;

                    if (reviews.length === 0) {
                        reviewsContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Nu există recenzii pentru acest anunț încă.</p>';
                        return;
                    }

                    reviewsContainer.innerHTML = reviews.map((review) => {
                        const isAuthor = currentUserId !== null && Number(review.author_id) === Number(currentUserId);
                        const ratingOptions = [5, 4, 3, 2, 1].map((r) => `
                            <option value="${r}" ${Number(review.rating) === r ? 'selected' : ''}>${stars(r)} (${r})</option>
                        `).join('');

                        const authorActions = isAuthor ? `
                            <button type="button" title="Editează recenzia" aria-label="Editează recenzia"
                                    data-toggle-edit="${review.id}"
                                    class="js-toggle-edit-btn inline-flex items-center justify-center w-7 h-7 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <form class="js-delete-review-form" data-review-id="${review.id}">
                                <button type="submit" title="Șterge recenzia" aria-label="Șterge recenzia"
                                        class="inline-flex items-center justify-center w-7 h-7 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        ` : '';

                        const editForm = isAuthor ? `
                            <form id="edit-review-${review.id}" data-review-id="${review.id}" class="js-edit-review-form hidden mt-4 pt-4 border-t border-gray-100 space-y-3">
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-semibold text-gray-600">Rating:</label>
                                    <select name="rating" required class="border-gray-300 rounded-lg text-sm py-1 px-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                                        ${ratingOptions}
                                    </select>
                                </div>
                                <textarea name="comment" rows="2" required class="w-full border-gray-300 rounded-lg shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">${escapeHtml(review.comment)}</textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" data-cancel-edit="${review.id}" class="js-cancel-edit-btn px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                                        Anulează
                                    </button>
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                                        Salvează
                                    </button>
                                </div>
                            </form>
                        ` : '';

                        return `
                            <div class="border border-gray-100 rounded-xl p-4 bg-white">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm text-gray-900">${escapeHtml(review.author_name)}</span>
                                        <span class="text-xs text-amber-500 font-bold">${stars(Number(review.rating))}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">${escapeHtml(review.created_at_human || '')}</span>
                                        ${authorActions}
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed">${escapeHtml(review.comment)}</p>
                                ${editForm}
                            </div>
                        `;
                    }).join('');
                };

                const refreshListingDetails = async () => {
                    try {
                        const response = await fetch(`/api/listings/${listingId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'Cache-Control': 'no-cache',
                            },
                            cache: 'no-store',
                            credentials: 'same-origin',
                        });
                        if (!response.ok) return;
                        const data = await response.json();
                        renderReviews(data);
                    } catch (e) {
                        // Păstrează starea existentă
                    }
                };

                const starPicker = document.getElementById('star-rating-picker');
                const ratingInput = document.getElementById('rating');
                const ratingLabel = document.getElementById('rating-label');
                const ratingDescriptions = {
                    1: { text: 'Experiență dezamăgitoare', cls: 'text-rose-700 bg-rose-50 border-rose-200' },
                    2: { text: 'Serviciu sub așteptări', cls: 'text-orange-700 bg-orange-50 border-orange-200' },
                    3: { text: 'Serviciu acceptabil', cls: 'text-amber-700 bg-amber-50 border-amber-200' },
                    4: { text: 'Serviciu foarte bun', cls: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
                    5: { text: 'Serviciu excelent!', cls: 'text-amber-700 bg-amber-50 border-amber-200' },
                };

                const renderStarPicker = (activeValue) => {
                    const val = Math.min(5, Math.max(1, Number(activeValue) || 5));
                    if (starPicker) {
                        starPicker.querySelectorAll('.js-star-btn').forEach((btn) => {
                            const starVal = Number(btn.getAttribute('data-star-value'));
                            btn.classList.toggle('text-amber-400', starVal <= val);
                            btn.classList.toggle('text-gray-300', starVal > val);
                        });
                    }
                    if (ratingLabel && ratingDescriptions[val]) {
                        const meta = ratingDescriptions[val];
                        ratingLabel.textContent = meta.text;
                        ratingLabel.className = `w-48 shrink-0 whitespace-nowrap text-xs font-semibold border px-2.5 py-1 rounded-full text-center transition ${meta.cls}`;
                    }
                };

                if (starPicker && ratingInput) {
                    renderStarPicker(ratingInput.value);

                    starPicker.addEventListener('click', (e) => {
                        const btn = e.target.closest('.js-star-btn');
                        if (!btn) return;
                        const val = Number(btn.getAttribute('data-star-value'));
                        ratingInput.value = val;
                        renderStarPicker(val);
                    });

                    starPicker.addEventListener('mouseover', (e) => {
                        const btn = e.target.closest('.js-star-btn');
                        if (!btn) return;
                        renderStarPicker(btn.getAttribute('data-star-value'));
                    });

                    starPicker.addEventListener('mouseleave', () => {
                        renderStarPicker(ratingInput.value);
                    });
                }

                if (leaveReviewForm) {
                    leaveReviewForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const rating = leaveReviewForm.querySelector('[name="rating"]').value;
                        const comment = leaveReviewForm.querySelector('[name="comment"]').value;

                        try {
                            const response = await fetch(`/api/listings/${listingId}/reviews`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({ rating: Number(rating), comment }),
                            });

                            const data = await response.json();
                            if (!response.ok) {
                                showFeedback(data.message || 'Eroare la trimiterea recenziei.', true);
                                return;
                            }

                            showFeedback(data.message || 'Recenzia a fost adăugată cu succes!');
                            leaveReviewForm.reset();
                            if (ratingInput) {
                                ratingInput.value = 5;
                                renderStarPicker(5);
                            }
                            await refreshListingDetails();
                        } catch (err) {
                            showFeedback('Eroare de rețea la trimiterea recenziei.', true);
                        }
                    });
                }

                if (reviewsContainer) {
                    reviewsContainer.addEventListener('click', (e) => {
                        const toggleBtn = e.target.closest('.js-toggle-edit-btn');
                        if (toggleBtn) {
                            const id = toggleBtn.getAttribute('data-toggle-edit');
                            document.getElementById(`edit-review-${id}`)?.classList.toggle('hidden');
                            return;
                        }

                        const cancelBtn = e.target.closest('.js-cancel-edit-btn');
                        if (cancelBtn) {
                            const id = cancelBtn.getAttribute('data-cancel-edit');
                            document.getElementById(`edit-review-${id}`)?.classList.add('hidden');
                        }
                    });

                    reviewsContainer.addEventListener('submit', async (e) => {
                        const editForm = e.target.closest('.js-edit-review-form');
                        if (editForm) {
                            e.preventDefault();
                            const reviewId = editForm.getAttribute('data-review-id');
                            const rating = editForm.querySelector('[name="rating"]').value;
                            const comment = editForm.querySelector('[name="comment"]').value;

                            try {
                                const response = await fetch(`/api/reviews/${reviewId}`, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ rating: Number(rating), comment }),
                                });

                                const data = await response.json();
                                if (!response.ok) {
                                    showFeedback(data.message || 'Eroare la actualizarea recenziei.', true);
                                    return;
                                }

                                showFeedback(data.message || 'Recenzia a fost actualizată!');
                                await refreshListingDetails();
                            } catch (err) {
                                showFeedback('Eroare de rețea la actualizarea recenziei.', true);
                            }
                            return;
                        }

                        const deleteForm = e.target.closest('.js-delete-review-form');
                        if (deleteForm) {
                            e.preventDefault();
                            if (!confirm('Sigur doriți să ștergeți această recenzie?')) return;
                            const reviewId = deleteForm.getAttribute('data-review-id');

                            try {
                                const response = await fetch(`/api/reviews/${reviewId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken,
                                    },
                                    credentials: 'same-origin',
                                });

                                const data = await response.json();
                                if (!response.ok) {
                                    showFeedback(data.message || 'Eroare la ștergerea recenziei.', true);
                                    return;
                                }

                                showFeedback(data.message || 'Recenzia a fost ștearsă.');
                                await refreshListingDetails();
                            } catch (err) {
                                showFeedback('Eroare de rețea la ștergerea recenziei.', true);
                            }
                        }
                    });
                }

                refreshListingDetails();
            });
        </script>
    @endif
</x-app-layout>
