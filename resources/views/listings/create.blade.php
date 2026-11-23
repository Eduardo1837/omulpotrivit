<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Publică un Anunț Nou') }}
            </h2>
            <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                &larr; Înapoi la toate anunțurile
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

            <div id="create-listing-feedback" class="hidden mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium"></div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 md:p-8">
                <form id="create-listing-form" action="{{ route('listings.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-bold text-gray-700 mb-1">Titlu Anunț *</label>
                        <input type="text" id="title" name="title" required value="{{ old('title') }}"
                               placeholder="Ex: Zugrav profesionist cu experiență"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-1">Descriere detaliată *</label>
                        <textarea id="description" name="description" rows="4" required
                                  placeholder="Detaliază serviciul pe care îl oferi, disponibilitatea și detaliile relevante..."
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="price" class="block text-sm font-bold text-gray-700 mb-1">Preț (RON) *</label>
                            <input type="number" step="0.01" id="price" name="price" required value="{{ old('price') }}"
                                   placeholder="150"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="priceType" class="block text-sm font-bold text-gray-700 mb-1">Tip Preț *</label>
                            <select id="priceType" name="priceType" required
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="fix" {{ old('priceType') === 'fix' ? 'selected' : '' }}>Fix</option>
                                <option value="pe ora" {{ old('priceType') === 'pe ora' ? 'selected' : '' }}>Pe oră</option>
                                <option value="negociabil" {{ old('priceType') === 'negociabil' ? 'selected' : '' }}>Negociabil</option>
                            </select>
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-bold text-gray-700 mb-1">Oraș / Locație *</label>
                            <input type="text" id="city" name="city" required value="{{ old('city', auth()->user()->city ?? '') }}"
                                   placeholder="București"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-bold text-gray-700 mb-1">Categorie *</label>
                        <select id="category_id" name="category_id" required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Alege o categorie potrivită --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="images" class="block text-sm font-bold text-gray-700 mb-1">Imagini Serviciu (opțional, max 8)</label>
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
                            <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">Doresc să promovez acest anunț (Featured)</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                        <a href="{{ route('listings.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            Anulează
                        </a>
                        @if(auth()->user()?->role !== 'admin')
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm text-sm transition">
                                Publică Anunțul
                            </button>
                        @else
                            <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-200">
                                Administratorii pot doar previzualiza acest formular.
                            </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(auth()->user()?->role !== 'admin')
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const form = document.getElementById('create-listing-form');
                const categorySelect = document.getElementById('category_id');
                const feedback = document.getElementById('create-listing-feedback');
                const csrfToken = '{{ csrf_token() }}';

                const showError = (message) => {
                    if (!feedback) return;
                    feedback.textContent = message;
                    feedback.classList.remove('hidden');
                };

                try {
                    const catRes = await fetch('/api/categories', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    if (catRes.ok && categorySelect) {
                        const categories = await catRes.json();
                        const selectedValue = categorySelect.value;
                        categorySelect.innerHTML = '<option value="">-- Alege o categorie potrivită --</option>';
                        categories.forEach((cat) => {
                            const opt = document.createElement('option');
                            opt.value = cat.id;
                            opt.textContent = cat.name;
                            if (String(cat.id) === String(selectedValue)) {
                                opt.selected = true;
                            }
                            categorySelect.appendChild(opt);
                        });
                    }
                } catch (e) {
                    // Păstrează categoriile redate de server
                }

                const imagesInput = document.getElementById('images');
                const imagesPreview = document.getElementById('images-preview');

                if (imagesInput && imagesPreview) {
                    imagesInput.addEventListener('change', () => {
                        const files = Array.from(imagesInput.files || []);
                        imagesPreview.innerHTML = '';
                        imagesPreview.classList.toggle('hidden', files.length === 0);
                        files.forEach((file) => {
                            const url = URL.createObjectURL(file);
                            const img = document.createElement('img');
                            img.src = url;
                            img.alt = file.name;
                            img.className = 'w-full h-24 object-cover rounded-lg border border-gray-200';
                            imagesPreview.appendChild(img);
                        });
                    });
                }

                if (form) {
                    form.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        if (feedback) feedback.classList.add('hidden');

                        const formData = new FormData();
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
                            const response = await fetch('/api/listings', {
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
                                showError(errorsList || data.message || 'Eroare la publicarea anunțului.');
                                return;
                            }

                            window.location.href = '{{ route('listings.my') }}';
                        } catch (err) {
                            showError('Eroare de rețea la publicarea anunțului.');
                        }
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
