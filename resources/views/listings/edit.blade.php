<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Editează Anunțul') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $listing->title }}</p>
            </div>
            <a href="{{ route('listings.my') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                &larr; Înapoi la Anunțurile Mele
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                    <div class="font-semibold text-sm mb-1">Te rugăm să corectezi erorile de mai jos:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="edit-listing-feedback" class="hidden mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium"></div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 md:p-8">
                <form id="edit-listing-form" action="{{ route('listings.update', $listing->id) }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-bold text-gray-700 mb-1">Titlu Anunț *</label>
                        <input type="text" id="title" name="title" required value="{{ old('title', $listing->title) }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-1">Descriere detaliată *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $listing->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-700 mb-1">Preț (RON) *</label>
                            <input type="number" step="0.01" id="price" name="price" required value="{{ old('price', $listing->price) }}"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="priceType" class="block text-sm font-bold text-gray-700 mb-1">Tip Preț *</label>
                            <select id="priceType" name="priceType" required
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="fix" {{ old('priceType', $listing->priceType) === 'fix' ? 'selected' : '' }}>Fix</option>
                                <option value="pe ora" {{ old('priceType', $listing->priceType) === 'pe ora' ? 'selected' : '' }}>Pe oră</option>
                                <option value="negociabil" {{ old('priceType', $listing->priceType) === 'negociabil' ? 'selected' : '' }}>Negociabil</option>
                            </select>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-bold text-gray-700 mb-1">Oraș / Locație *</label>
                            <input type="text" id="city" name="city" required value="{{ old('city', $listing->city) }}"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-bold text-gray-700 mb-1">Categorie *</label>
                        <select id="category_id" name="category_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $listing->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $listing->is_featured) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Marchează ca Anunț Promovat (Featured)</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                        <a href="{{ route('listings.my') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            Anulează
                        </a>

                        @if(!($isAdminPreview ?? false))
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm text-sm transition">
                                Salvează Modificările
                            </button>
                        @else
                            <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-200">
                                Mod Previzualizare Utilizator (administratorii nu pot salva modificări aici).
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!($isAdminPreview ?? false))
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const listingId = {{ (int) $listing->id }};
                const form = document.getElementById('edit-listing-form');
                const categorySelect = document.getElementById('category_id');
                const feedback = document.getElementById('edit-listing-feedback');
                const csrfToken = '{{ csrf_token() }}';

                const showError = (message) => {
                    if (!feedback) return;
                    feedback.textContent = message;
                    feedback.classList.remove('hidden');
                };

                try {
                    const [catRes, ownRes] = await Promise.all([
                        fetch('/api/categories', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }),
                        fetch(`/api/listings/${listingId}/own`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }),
                    ]);

                    let currentCategoryId = categorySelect?.value;

                    if (ownRes.ok) {
                        const own = await ownRes.json();
                        if (document.getElementById('title')) document.getElementById('title').value = own.titlu ?? '';
                        if (document.getElementById('description')) document.getElementById('description').value = own.descriere ?? '';
                        if (document.getElementById('price')) document.getElementById('price').value = own.pret ?? '';
                        if (document.getElementById('priceType') && own.tip_pret_raw) document.getElementById('priceType').value = own.tip_pret_raw;
                        if (document.getElementById('city')) document.getElementById('city').value = own.oras ?? '';
                        if (document.getElementById('is_featured')) document.getElementById('is_featured').checked = Boolean(own.este_promovat);
                        if (own.category_id) currentCategoryId = own.category_id;
                    }

                    if (catRes.ok && categorySelect) {
                        const categories = await catRes.json();
                        categorySelect.innerHTML = '';
                        categories.forEach((cat) => {
                            const opt = document.createElement('option');
                            opt.value = cat.id;
                            opt.textContent = cat.name;
                            if (String(cat.id) === String(currentCategoryId)) {
                                opt.selected = true;
                            }
                            categorySelect.appendChild(opt);
                        });
                    }
                } catch (e) {
                    // Păstrează datele redate de server
                }

                if (form) {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        if (feedback) feedback.classList.add('hidden');

                        const payload = {
                            title: document.getElementById('title')?.value ?? '',
                            description: document.getElementById('description')?.value ?? '',
                            price: Number(document.getElementById('price')?.value ?? 0),
                            priceType: document.getElementById('priceType')?.value ?? 'fix',
                            city: document.getElementById('city')?.value ?? '',
                            category_id: Number(categorySelect?.value ?? 0),
                            is_featured: Boolean(document.getElementById('is_featured')?.checked),
                        };

                        try {
                            const response = await fetch(`/api/listings/${listingId}`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify(payload),
                            });

                            const data = await response.json();
                            if (!response.ok) {
                                const errorsList = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                                showError(errorsList || data.message || 'Eroare la actualizarea anunțului.');
                                return;
                            }

                            window.location.href = '{{ route('listings.my') }}';
                        } catch (err) {
                            showError('Eroare de rețea la actualizarea anunțului.');
                        }
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
