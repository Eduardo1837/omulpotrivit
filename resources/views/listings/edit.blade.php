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

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Galerie Imagini</label>
                        <div id="existing-images-container" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-3 hidden"></div>
                        <input type="file"
                               id="images"
                               name="images[]"
                               multiple
                               accept="image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <div id="images-preview" class="mt-3 grid grid-cols-3 sm:grid-cols-4 gap-3 hidden"></div>
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

                const existingImagesContainer = document.getElementById('existing-images-container');
                const imagesInput = document.getElementById('images');
                const imagesPreview = document.getElementById('images-preview');

                const renderExistingImages = (images) => {
                    if (!existingImagesContainer) return;
                    const list = Array.isArray(images) ? images : [];
                    existingImagesContainer.innerHTML = '';
                    existingImagesContainer.classList.toggle('hidden', list.length === 0);

                    list.forEach((img) => {
                        const wrapper = document.createElement('div');
                        wrapper.id = `listing-image-${img.id}`;
                        wrapper.className = 'relative group rounded-lg overflow-hidden border border-gray-200';
                        wrapper.innerHTML = `
                            <img src="${img.url}" alt="Imagine anunț" class="w-full h-24 object-cover">
                            <button type="button"
                                    data-delete-image="${img.id}"
                                    title="Șterge imaginea"
                                    class="absolute top-1 right-1 w-6 h-6 rounded-full bg-rose-600/90 hover:bg-rose-700 text-white text-xs font-bold flex items-center justify-center shadow">
                                &times;
                            </button>
                        `;
                        existingImagesContainer.appendChild(wrapper);
                    });
                };

                if (existingImagesContainer) {
                    existingImagesContainer.addEventListener('click', async (e) => {
                        const btn = e.target.closest('[data-delete-image]');
                        if (!btn) return;
                        const imageId = btn.getAttribute('data-delete-image');
                        try {
                            const res = await fetch(`/api/listings/${listingId}/images/${imageId}`, {
                                method: 'DELETE',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                credentials: 'same-origin',
                            });
                            if (res.ok) {
                                document.getElementById(`listing-image-${imageId}`)?.remove();
                                if (existingImagesContainer.children.length === 0) {
                                    existingImagesContainer.classList.add('hidden');
                                }
                            }
                        } catch (err) {
                            showError('Nu s-a putut șterge imaginea.');
                        }
                    });
                }

                if (imagesInput && imagesPreview) {
                    imagesInput.addEventListener('change', () => {
                        const files = Array.from(imagesInput.files || []);
                        imagesPreview.innerHTML = '';
                        imagesPreview.classList.toggle('hidden', files.length === 0);
                        files.forEach((file) => {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(file);
                            img.alt = file.name;
                            img.className = 'w-full h-24 object-cover rounded-lg border border-indigo-200';
                            imagesPreview.appendChild(img);
                        });
                    });
                }

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
                        renderExistingImages(own.images);
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

                        const formData = new FormData();
                        formData.append('_method', 'PUT');
                        formData.append('title', document.getElementById('title')?.value ?? '');
                        formData.append('description', document.getElementById('description')?.value ?? '');
                        formData.append('price', document.getElementById('price')?.value ?? '0');
                        formData.append('priceType', document.getElementById('priceType')?.value ?? 'fix');
                        formData.append('city', document.getElementById('city')?.value ?? '');
                        formData.append('category_id', categorySelect?.value ?? '');
                        formData.append('is_featured', document.getElementById('is_featured')?.checked ? '1' : '0');

                        Array.from(imagesInput?.files || []).forEach((file) => {
                            formData.append('images[]', file);
                        });

                        try {
                            const response = await fetch(`/api/listings/${listingId}`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                credentials: 'same-origin',
                                body: formData,
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
