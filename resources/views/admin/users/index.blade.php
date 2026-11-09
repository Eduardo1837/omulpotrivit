<x-app-layout>
    <!-- This goes into the $header variable in app.blade.php -->
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Dashboard - Manage Users') }}
            </h2>
            <a href="{{ url('/') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                Back to Main Site
            </a>
        </div>
    </x-slot>

    <!-- This goes into the $slot variable in app.blade.php -->
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    
                    <table class="min-w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="py-3 px-4 uppercase font-semibold text-sm rounded-tl-lg">ID</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">Avatar</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">Name</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">City</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">Role</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm">Listings</th>
                                <th class="py-3 px-4 uppercase font-semibold text-sm rounded-tr-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 border-b border-gray-200">
                                    <td class="py-3 px-4">{{ $user->id }}</td>
                                    <td class="py-3 px-4">
                                        @if($user->avatarUrl)
                                            <img src="{{ $user->avatarUrl }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-bold">{{ $user->name }}</td>
                                    <td class="py-3 px-4">{{ $user->email }}</td>
                                    <td class="py-3 px-4">{{ $user->city ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        @if($user->role === 'admin')
                                            <span class="bg-yellow-200 text-yellow-800 py-1 px-2 rounded font-bold text-xs">Admin</span>
                                        @else
                                            <span class="bg-indigo-100 text-indigo-800 py-1 px-2 rounded font-bold text-xs">{{ ucfirst($user->role ?? 'User') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">{{ $user->listings->count() }}</td>
                                   <td class="py-3 px-4 flex gap-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white py-1 px-3 rounded text-sm transition">Edit</a>
                                        
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded text-sm transition">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-4 px-4 text-center text-gray-500">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>