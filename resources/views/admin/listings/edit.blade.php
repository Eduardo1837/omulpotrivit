<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editare Anunț: ') }} {{ $listing->title }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quick Sub-Navigation Between Sections -->
            <x-admin-nav :breadcrumbs="['Anunțuri' => route('admin.listings.index'), 'Editare' => null]" />

            <div class="max-w-4xl">

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
                <form action="{{ route('admin.listings.update', $listing->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Titlu</label>
                        <input type="text" name="title" value="{{ old('title', $listing->title) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Categorie</label>
                            <select name="category_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $listing->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-bold mb-2 text-sm">Furnizor (Utilizator)</label>
                            <select name="provider_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('provider_id', $listing->provider_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2 text-sm">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="activ" {{ $listing->status === 'activ' ? 'selected' : '' }}>Activ</option>
                            <option value="inactiv" {{ $listing->status === 'inactiv' ? 'selected' : '' }}>Inactiv</option>
                        </select>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_featured" value="1" {{ $listing->is_featured ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label class="ml-2 text-gray-700 font-semibold text-sm">Anunț Promovat (Featured)</label>
                    </div>

                    <div class="flex items-center gap-4 mt-6 pt-4 border-t border-gray-100">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-5 rounded-lg text-sm shadow-sm transition">
                            Salvează Modificările
                        </button>
                        <a href="{{ route('admin.listings.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-gray-900 transition">
                            &larr; Înapoi la Anunțuri
                        </a>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
