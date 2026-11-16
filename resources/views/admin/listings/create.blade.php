<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adaugă Anunț Nou') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Sub-Navigation Between Sections -->
            <x-admin-nav :breadcrumbs="['Anunțuri' => route('admin.listings.index'), 'Adaugă' => null]" />

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6">
                <form action="{{ route('admin.listings.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Titlu</label>
                        <input type="text" name="title" value="{{ old('title') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Categorie</label>
                            <select name="category_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Selectează o categorie --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Furnizor (Utilizator)</label>
                            <select name="provider_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Selectează un furnizor --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('provider_id', auth()->id()) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Descriere</label>
                        <textarea name="description" rows="4" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Preț (RON)</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Tip Preț</label>
                            <select name="priceType" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="fix" {{ old('priceType', 'fix') === 'fix' ? 'selected' : '' }}>Fix</option>
                                <option value="pe ora" {{ old('priceType') === 'pe ora' ? 'selected' : '' }}>Pe oră</option>
                                <option value="negociabil" {{ old('priceType') === 'negociabil' ? 'selected' : '' }}>Negociabil</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Oraș</label>
                            <input type="text" name="city" value="{{ old('city') }}" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="activ" {{ old('status', 'activ') === 'activ' ? 'selected' : '' }}>Activ</option>
                            <option value="inactiv" {{ old('status') === 'inactiv' ? 'selected' : '' }}>Inactiv</option>
                        </select>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label class="ml-2 text-gray-700 font-semibold text-sm">Anunț Promovat (Featured)</label>
                    </div>

                    <div class="flex items-center gap-4 mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-5 rounded-lg text-sm shadow-sm transition">
                            Creează Anunț
                        </button>
                        <a href="{{ route('admin.listings.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            &larr; Înapoi la Anunțuri
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
