<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reviews for: {{ $listing->title }}
            </h2>
            <a href="{{ route('admin.listings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                Back to Listings
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Author</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Rating</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Comment</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Date</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Status</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @forelse($reviews as $review)
                            <tr class="border-b hover:bg-gray-50 {{ $review->is_hidden ? 'bg-gray-100 opacity-75' : '' }}">
                                <td class="py-3 px-4 font-bold">{{ $review->author->name ?? 'Unknown' }}</td>
                                <td class="py-3 px-4">
                                    <span class="bg-yellow-100 text-yellow-800 font-bold px-2 py-1 rounded text-xs">
                                        {{ $review->rating }} / 5 ★
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="max-w-md truncate">{{ $review->comment }}</div>
                                </td>
                                <td class="py-3 px-4 text-sm">{{ $review->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    @if($review->is_hidden)
                                        <span class="bg-red-100 text-red-800 py-1 px-2 rounded font-bold text-xs">Hidden</span>
                                    @else
                                        <span class="bg-green-100 text-green-800 py-1 px-2 rounded font-bold text-xs">Visible</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 flex gap-2">
                                    
                                    <!-- Toggle Visibility Button -->
                                    <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white py-1 px-3 rounded text-sm transition">
                                            {{ $review->is_hidden ? 'Unhide' : 'Hide' }}
                                        </button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this review?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm transition">Delete</button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">No reviews found for this listing.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>