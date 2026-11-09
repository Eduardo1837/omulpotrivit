<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Welcome Message -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 font-medium text-lg">
                    Welcome back, {{ auth()->user()->name }}! Here is an overview of your platform.
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Users Stat Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex items-center justify-between p-6 border-l-4 border-blue-500">
                    <div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Users</div>
                        <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ $stats['users'] }}</div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <!-- Simple user icon (SVG) -->
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Listings Stat Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex items-center justify-between p-6 border-l-4 border-green-500">
                    <div>
                        <div class="text-sm font-bold text-gray-500 uppercase tracking-wide">Total Listings</div>
                        <div class="text-3xl font-extrabold text-gray-900 mt-1">{{ $stats['listings'] }}</div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <!-- Simple folder/list icon (SVG) -->
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>

            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                    <div class="flex gap-4">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Users
                        </a>
                        <a href="{{ route('admin.listings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            Manage Listings
                        </a>
                        <a href="{{ url('/') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            View Main Site
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>