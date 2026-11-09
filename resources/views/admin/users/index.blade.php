<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin - Gestionare Utilizatori') }}
            </h2>
            <div class="text-xs text-gray-500 font-medium">
                Total utilizatori înregistrați: <span class="font-bold text-gray-800">{{ $users->count() }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Quick Sub-Navigation Between Sections -->
            <x-admin-nav :breadcrumbs="['Utilizatori' => null]" />

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200/80">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    
                    <table class="min-w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tl-lg">ID</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs">Avatar</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs">Nume</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs">Email</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs">Oraș</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs">Rol</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs">Anunțuri</th>
                                <th class="py-3 px-4 uppercase font-semibold text-xs rounded-tr-lg">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 px-4 font-mono text-xs text-gray-500">{{ $user->id }}</td>
                                    <td class="py-3 px-4">
                                        @if($user->avatarUrl)
                                            <img src="{{ $user->avatarUrl }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-900">{{ $user->name }}</td>
                                    <td class="py-3 px-4 text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $user->city ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        @if($user->role === 'admin')
                                            <span class="bg-amber-100 text-amber-800 py-1 px-2.5 rounded-full font-bold text-xs">Admin</span>
                                        @else
                                            <span class="bg-indigo-50 text-indigo-700 py-1 px-2.5 rounded-full font-medium text-xs">{{ ucfirst($user->role ?? 'User') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                            {{ $user->listings->count() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white py-1 px-3 rounded text-xs font-semibold transition">
                                                Editare
                                            </a>
                                            
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Sigur doriți să ștergeți acest utilizator?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white py-1 px-3 rounded text-xs font-semibold transition">
                                                        Șterge
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 px-4 text-center text-gray-500">Nu au fost găsiți utilizatori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
