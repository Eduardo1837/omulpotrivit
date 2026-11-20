<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Scrie o Recenzie') }}
            </h2>
            <a href="{{ route('reviews.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                &larr; Înapoi la recenzii
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div id="create-review-feedback" class="hidden mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium"></div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 md:p-8">
                <form id="create-review-form" action="{{ route('reviews.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="listing_id" class="block text-sm font-bold text-gray-700 mb-1">Anunț *</label>
                        <select name="listing_id" id="listing_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Alege un anunț --</option>
                            @foreach($listings as $listing)
                                <option value="{{ $listing->id }}" {{ old('listing_id') == $listing->id ? 'selected' : '' }}>
                                    {{ $listing->title ?? 'Listing #' . $listing->id }}
                                </option>
                            @endforeach
                        </select>
                        @error('listing_id')
                            <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="rating" class="block text-sm font-bold text-gray-700 mb-1">Rating (1 - 5) *</label>
                        <select name="rating" id="rating" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Selectează nota --</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ old('rating', 5) == $i ? 'selected' : '' }}>
                                    {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})
                                </option>
                            @endfor
                        </select>
                        @error('rating')
                            <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-bold text-gray-700 mb-1">Comentariu *</label>
                        <textarea name="comment" id="comment" rows="4" required
                                  placeholder="Scrie părerea ta despre serviciul prestat..."
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('comment') }}</textarea>
                        @error('comment')
                            <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <a href="{{ route('reviews.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            Anulează
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm text-sm transition">
                            Trimite Recenzia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const form = document.getElementById('create-review-form');
            const listingSelect = document.getElementById('listing_id');
            const feedback = document.getElementById('create-review-feedback');
            const csrfToken = '{{ csrf_token() }}';

            const showError = (message) => {
                if (!feedback) return;
                feedback.textContent = message;
                feedback.classList.remove('hidden');
            };

            try {
                const res = await fetch('/api/listings', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (res.ok && listingSelect) {
                    const listings = await res.json();
                    const selected = listingSelect.value;
                    listingSelect.innerHTML = '<option value="">-- Alege un anunț --</option>';
                    listings.forEach((item) => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.titlu;
                        if (String(item.id) === String(selected)) {
                            opt.selected = true;
                        }
                        listingSelect.appendChild(opt);
                    });
                }
            } catch (e) {
                // Păstrează opțiunile redate de server
            }

            if (form) {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    if (feedback) feedback.classList.add('hidden');

                    const listingId = listingSelect?.value;
                    const rating = document.getElementById('rating')?.value;
                    const comment = document.getElementById('comment')?.value;

                    if (!listingId) {
                        showError('Te rugăm să selectezi un anunț.');
                        return;
                    }

                    try {
                        const response = await fetch(`/api/listings/${encodeURIComponent(listingId)}/reviews`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                rating: Number(rating),
                                comment,
                            }),
                        });

                        const data = await response.json();
                        if (!response.ok) {
                            const errorsList = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                            showError(errorsList || data.message || 'Eroare la salvarea recenziei.');
                            return;
                        }

                        window.location.href = '{{ route('reviews.index') }}';
                    } catch (err) {
                        showError('Eroare de rețea la trimiterea recenziei.');
                    }
                });
            }
        });
    </script>
</x-app-layout>
