<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin - Gestionare Anunțuri') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Quick Sub-Navigation Between Sections -->
            <x-admin-nav :breadcrumbs="['Anunțuri' => null]" />

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tl-lg">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Titlu</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Categorie</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Status</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Promovat</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tr-lg">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-100">
                        @forelse($listings as $listing)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $listing->id }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-900">
                                    <a href="{{ route('listings.details', $listing->id) }}" target="_blank" class="hover:text-indigo-600 transition inline-flex items-center gap-1">
                                        {{ $listing->title }}
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600">{{ $listing->category->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">
                                    @if($listing->status === 'activ')
                                        <span class="bg-emerald-100 text-emerald-800 py-1 px-2.5 rounded-full font-bold text-xs">Activ</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-700 py-1 px-2.5 rounded-full font-medium text-xs">{{ ucfirst($listing->status ?? 'Inactiv') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($listing->is_featured)
                                        <span class="bg-amber-100 text-amber-800 py-1 px-2.5 rounded-full font-bold text-xs">★ Promovat</span>
                                    @else
                                        <span class="text-xs text-gray-400">Nu</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.listings.reviews', $listing->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-2.5 rounded text-xs font-semibold transition">
                                            Recenzii
                                        </a>
                                        <a href="{{ route('admin.listings.edit', $listing->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white py-1 px-2.5 rounded text-xs font-semibold transition">
                                            Editare
                                        </a>
                                        <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Sigur doriți să ștergeți acest anunț?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white py-1 px-2.5 rounded text-xs font-semibold transition">
                                                Șterge
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 px-4 text-center text-gray-500">Nu există anunțuri înregistrate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
