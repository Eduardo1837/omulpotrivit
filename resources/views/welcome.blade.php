<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Find what you need</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts (Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 text-gray-900 font-sans">

    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="font-bold text-2xl text-blue-600">
                        {{ config('app.name', 'MyPlatform') }}
                    </a>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">Browse Listings</a>
                    
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('listings.create') }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-3 py-1.5 rounded-lg text-sm shadow-sm transition">
                                + Publică Anunț
                            </a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1.5 text-sm">🛡️ Admin</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-900 font-medium px-2 py-1.5 text-sm">Profil</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium px-3 py-2">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md transition">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-blue-600 py-20 px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
            Find the best services & products in your city.
        </h1>
        <p class="text-lg md:text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
            Browse through hundreds of listings, read reviews, and connect directly with providers.
        </p>
        <a href="{{ route('listings.index') }}" class="inline-block bg-white text-blue-600 font-bold text-lg px-8 py-3 rounded-full shadow-lg hover:bg-gray-100 transition">
            Start Exploring
        </a>
    </div>

    <!-- Categories Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Browse by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($categories as $category)
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 text-center hover:shadow-md transition cursor-pointer">
                    <h3 class="font-semibold text-gray-800">{{ $category->name ?? 'Category ' . $category->id }}</h3>
                </div>
            @empty
                <p class="text-gray-500 col-span-4">No categories available yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent Listings Section -->
    <div class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Fresh Listings</h2>
                <a href="{{ route('listings.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">View all &rarr;</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($recentListings as $listing)
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition flex flex-col">
                        <div class="h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                            <!-- Placeholder Image -->
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="p-5 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ $listing->title ?? 'Listing #'.$listing->id }}</h3>
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">{{ $listing->priceFormatted ?? '$0' }}</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4 flex-1">{{ Str::limit($listing->description ?? 'No description provided.', 80) }}</p>
                            <a href="{{ route('listings.details', $listing->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold uppercase tracking-wider">View Details</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 col-span-3">No listings available yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-300 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            &copy; {{ date('Y') }} {{ config('app.name', 'MyPlatform') }}. All rights reserved.
        </div>
    </footer>

</body>
</html>