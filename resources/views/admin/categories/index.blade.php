<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin - Gestionare Categorii') }}
            </h2>
            <div class="flex items-center gap-4">
                <div class="text-xs text-gray-500 font-medium">
                    Total categorii: <span class="font-bold text-gray-800">{{ $categories->count() }}</span>
                </div>
                <a href="{{ route('admin.categories.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs shadow-sm transition">
                    + Adaugă Categorie
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-admin-nav :breadcrumbs="['Categorii' => null]" />

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tl-lg">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Nume</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Descriere</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Categorie Părinte</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Anunțuri</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tr-lg">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-100">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $category->id }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-900">{{ $category->name }}</td>
                                <td class="py-3 px-4 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($category->description ?? '-', 60) }}</td>
                                <td class="py-3 px-4 text-sm text-gray-600">{{ $category->parent?->name ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ $category->listings->count() }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white py-1 px-3 rounded text-xs font-semibold transition">
                                            Editare
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Sigur dorești să ștergi această categorie?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-xs font-semibold transition">
                                                Șterge
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-500 text-sm">Nu există categorii definite.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
