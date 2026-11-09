<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Listing: {{ $listing->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.listings.update', $listing->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Title</label>
                        <input type="text" name="title" value="{{ old('title', $listing->title) }}" class="w-full border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md">
                            <option value="activ" {{ $listing->status === 'activ' ? 'selected' : '' }}>Activ</option>
                            <option value="inactiv" {{ $listing->status === 'inactiv' ? 'selected' : '' }}>Inactiv</option>
                        </select>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_featured" value="1" {{ $listing->is_featured ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                        <label class="ml-2 text-gray-700 font-bold">Featured Listing</label>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save Changes</button>
                        <a href="{{ route('admin.listings.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>