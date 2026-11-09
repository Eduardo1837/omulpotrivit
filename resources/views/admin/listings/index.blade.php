<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Listings</h2>
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
                            <th class="py-3 px-4 uppercase font-semibold text-sm">ID</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Title</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Category</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Status</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Featured</th>
                            <th class="py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        @foreach($listings as $listing)
                            <tr class="border-b">
                                <td class="py-3 px-4">{{ $listing->id }}</td>
                                <td class="py-3 px-4 font-bold">{{ $listing->title }}</td>
                                <td class="py-3 px-4">{{ $listing->category->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ ucfirst($listing->status ?? 'Active') }}</td>
                                <td class="py-3 px-4">{{ $listing->is_featured ? 'Yes' : 'No' }}</td>
                                <td class="py-3 px-4 flex gap-2">
                                    <a href="{{ route('admin.listings.reviews', $listing->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-sm transition">Reviews</a>
                                    <a href="{{ route('admin.listings.edit', $listing->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded text-sm">Edit</a>
                                    <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Delete this listing?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>