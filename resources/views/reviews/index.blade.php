<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ __('Recenzii') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Părerile clienților despre serviciile publicate în platformă.
                </p>
            </div>

            <a href="{{ route('reviews.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Scrie o Recenzie
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Anunț</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Autor</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Comentariu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Publicat</th>
                            </tr>
                        </thead>
                        <tbody id="reviews-table-body" class="bg-white divide-y divide-gray-100 text-sm">
                            @forelse($reviews as $review)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900">
                                        <a href="{{ route('listings.details', $review->listing_id) }}" class="hover:text-indigo-600 transition">
                                            {{ $review->listing->title ?? 'Listing #' . $review->listing_id }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">{{ $review->author->name ?? 'Unknown Author' }}</td>
                                    <td class="px-6 py-4 font-bold text-amber-600">{{ $review->rating }} / 5 ★</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $review->comment }}</td>
                                    <td class="px-6 py-4 text-gray-400 text-xs">{{ $review->created_at?->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nu există recenzii încă.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const tbody = document.getElementById('reviews-table-body');
            if (!tbody) return;

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            try {
                const response = await fetch('/api/reviews', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;

                const data = await response.json();
                const reviews = Array.isArray(data.reviews) ? data.reviews : [];
                if (reviews.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nu există recenzii încă.</td></tr>';
                    return;
                }

                tbody.innerHTML = reviews.map((r) => `
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            <a href="/listings/${encodeURIComponent(r.listing_id)}" class="hover:text-indigo-600 transition">
                                ${escapeHtml(r.listing_title)}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-700">${escapeHtml(r.author_name)}</td>
                        <td class="px-6 py-4 font-bold text-amber-600">${Number(r.rating)} / 5 ★</td>
                        <td class="px-6 py-4 text-gray-600">${escapeHtml(r.comment)}</td>
                        <td class="px-6 py-4 text-gray-400 text-xs">${escapeHtml(r.created_at_human || '')}</td>
                    </tr>
                `).join('');
            } catch (e) {
                // Păstrează conținutul inițial redat de server
            }
        });
    </script>
</x-app-layout>
