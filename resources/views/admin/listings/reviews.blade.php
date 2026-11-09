<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Recenzii pentru: <span class="text-indigo-600">{{ $listing->title }}</span>
            </h2>
            <a href="{{ route('admin.listings.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs transition">
                &larr; Înapoi la Anunțuri
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Quick Sub-Navigation Between Sections -->
            <x-admin-nav :breadcrumbs="['Anunțuri' => route('admin.listings.index'), 'Recenzii ('.$listing->title.')' => null]" />

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80 p-6 overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tl-lg">Autor</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Rating</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Comentariu</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Data</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs">Vizibilitate</th>
                            <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tr-lg">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 divide-y divide-gray-100">
                        @forelse($reviews as $review)
                            <tr class="hover:bg-gray-50 transition {{ $review->is_hidden ? 'bg-gray-50 opacity-75' : '' }}">
                                <td class="py-3 px-4 font-semibold text-gray-900">{{ $review->author->name ?? 'Necunoscut' }}</td>
                                <td class="py-3 px-4">
                                    <span class="bg-amber-100 text-amber-800 font-bold px-2 py-1 rounded text-xs">
                                        {{ $review->rating }} / 5 ★
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="max-w-md text-sm text-gray-700">{{ $review->comment }}</div>
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-500">{{ $review->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-3 px-4">
                                    @if($review->is_hidden)
                                        <span class="bg-rose-100 text-rose-800 py-1 px-2.5 rounded-full font-bold text-xs">Ascuns</span>
                                    @else
                                        <span class="bg-emerald-100 text-emerald-800 py-1 px-2.5 rounded-full font-bold text-xs">Vizibil</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-gray-700 hover:bg-gray-900 text-white py-1 px-2.5 rounded text-xs font-semibold transition">
                                                {{ $review->is_hidden ? 'Arată' : 'Ascunde' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Sigur doriți să ștergeți această recenzie?');">
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
                                <td colspan="6" class="py-6 px-4 text-center text-gray-500">Nu există recenzii pentru acest anunț.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
