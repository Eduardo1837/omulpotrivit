@props(['title' => null, 'breadcrumbs' => []])

<div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200/80 p-4 transition-all">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        
        <!-- Left: Navigation Tabs with Icons -->
        <nav class="flex items-center flex-wrap gap-2">
            <!-- Dashboard Tab -->
            <a href="{{ route('admin.dashboard') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Users Tab -->
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span>Utilizatori</span>
            </a>

            <!-- Listings Tab -->
            <a href="{{ route('admin.listings.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors duration-150 {{ request()->routeIs('admin.listings.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('admin.listings.*') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span>Anunțuri</span>
            </a>
        </nav>

        <!-- Right: Fast Contextual Shortcuts -->
        <div class="flex items-center gap-2 border-t md:border-t-0 pt-2 md:pt-0 border-gray-100">
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                <span>Vezi Site Public</span>
            </a>
        </div>
    </div>

    <!-- Optional Breadcrumbs bar if provided -->
    @if(!empty($breadcrumbs))
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center text-xs text-gray-500 gap-1.5">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600 transition">Admin</a>
            @foreach($breadcrumbs as $label => $url)
                <span>/</span>
                @if($url)
                    <a href="{{ $url }}" class="hover:text-indigo-600 transition">{{ $label }}</a>
                @else
                    <span class="text-gray-800 font-semibold">{{ $label }}</span>
                @endif
            @endforeach
        </div>
    @endif
</div>
