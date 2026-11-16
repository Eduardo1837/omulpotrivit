<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin - Gestionare Anunțuri') }}
            </h2>
            <a href="{{ route('admin.listings.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-xs shadow-sm transition">
                + Adaugă Anunț
            </a>
        </div>
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
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('admin.listings.reviews', $listing->id) }}"
                                           title="Recenzii"
                                           aria-label="Recenzii"
                                           class="inline-flex items-center justify-center w-8 h-8 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.listings.edit', $listing->id) }}"
                                           title="Editare"
                                           aria-label="Editare"
                                           class="inline-flex items-center justify-center w-8 h-8 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.listings.toggle-status', $listing->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            @if($listing->status === 'activ')
                                                <button type="submit"
                                                        title="Deactivate"
                                                        aria-label="Deactivate"
                                                        class="inline-flex items-center justify-center w-8 h-8 bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200 rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                            @else
                                                <button type="submit"
                                                        title="Activate"
                                                        aria-label="Activate"
                                                        class="inline-flex items-center justify-center w-8 h-8 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 rounded-lg transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </form>
                                        <button type="button"
                                                title="Delete"
                                                aria-label="Delete"
                                                onclick="openDeleteModal('{{ route('admin.listings.destroy', $listing->id) }}', '{{ addslashes($listing->title) }}')"
                                                class="inline-flex items-center justify-center w-8 h-8 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
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

    <!-- Jira-style Permanent Delete Confirmation Modal -->
    <div id="deleteListingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Permanent Deletion</h3>
            <p class="text-sm text-gray-600 mb-4">
                You are about to permanently delete <span id="deleteListingTitle" class="font-semibold text-gray-900"></span>.
                This action cannot be undone. Type <code class="px-1.5 py-0.5 bg-gray-100 text-rose-600 rounded font-bold">delete</code> to confirm.
            </p>
            <form id="deleteListingForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <input type="text"
                       id="confirmDeleteInput"
                       name="confirm_delete"
                       autocomplete="off"
                       placeholder="Type delete"
                       oninput="document.getElementById('confirmDeleteSubmit').disabled = this.value.trim() !== 'delete';"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm mb-4">
                <div class="flex justify-end gap-2">
                    <button type="button"
                            onclick="closeDeleteModal()"
                            class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                            id="confirmDeleteSubmit"
                            disabled
                            class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeleteModal(actionUrl, title) {
            document.getElementById('deleteListingForm').action = actionUrl;
            document.getElementById('deleteListingTitle').textContent = title;
            const input = document.getElementById('confirmDeleteInput');
            input.value = '';
            document.getElementById('confirmDeleteSubmit').disabled = true;
            document.getElementById('deleteListingModal').classList.remove('hidden');
            input.focus();
        }
        function closeDeleteModal() {
            document.getElementById('deleteListingModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
