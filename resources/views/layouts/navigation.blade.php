<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Acasă') }}
                    </x-nav-link>

                    <x-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.index') || request()->routeIs('listings.details')">
                        {{ __('Anunțuri') }}
                    </x-nav-link>

                    <x-nav-link :href="route('listings.my')" :active="request()->routeIs('listings.my')">
                        {{ __('Anunțurile mele') }}
                    </x-nav-link>

                    <x-nav-link :href="route('listings.create')" :active="request()->routeIs('listings.create')" class="text-emerald-600 font-semibold">
                        + {{ __('Publică Anunț') }}
                    </x-nav-link>

                    @if(Auth::user()?->role === 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" class="text-indigo-600 font-semibold">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                {{ __('Admin Panel') }}
                            </span>
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Notifications Bell & Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <div x-data="notificationsBell()" x-init="init()" @click.outside="openBell = false" class="relative">
                    <button type="button"
                            @click="openBell = !openBell; if (openBell) fetchNotifications()"
                            aria-label="Notificări"
                            class="relative inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0"
                              x-text="unreadCount > 9 ? '9+' : unreadCount"
                              x-cloak
                              class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 text-[10px] font-bold leading-none text-white bg-rose-600 rounded-full">
                        </span>
                    </button>

                    <div x-show="openBell"
                         x-transition
                         x-cloak
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50/70">
                            <span class="text-sm font-bold text-gray-900">Notificări</span>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        x-show="unreadCount > 0"
                                        @click="markAllRead()"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                    Marchează toate citite
                                </button>
                                <button type="button"
                                        x-show="notifications.length > 0"
                                        @click="deleteAllNotifications()"
                                        title="Șterge toate notificările"
                                        aria-label="Șterge toate notificările"
                                        class="inline-flex items-center justify-center p-1.5 rounded-md text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                            <template x-if="loading">
                                <div class="px-4 py-6 text-xs text-gray-400 text-center">Se încarcă notificările...</div>
                            </template>

                            <template x-if="!loading && notifications.length === 0">
                                <div class="px-4 py-6 text-xs text-gray-400 text-center">Nu ai notificări momentan.</div>
                            </template>

                            <template x-for="n in notifications" :key="n.id">
                                <div class="px-4 py-3 text-sm flex items-start justify-between gap-3"
                                     :class="n.isRead ? 'bg-white text-gray-600' : 'bg-indigo-50/50 text-gray-900'">
                                    <div class="space-y-0.5 min-w-0">
                                        <p class="text-xs leading-relaxed break-words"
                                           :class="n.isRead ? 'font-normal' : 'font-semibold'"
                                           x-text="n.message"></p>
                                        <p class="text-[11px] text-gray-400" x-text="formatDate(n.created_at)"></p>
                                    </div>
                                    <template x-if="!n.isRead">
                                        <button type="button"
                                                @click="markRead(n.id)"
                                                class="shrink-0 text-[11px] font-semibold px-2 py-1 rounded-md bg-white hover:bg-indigo-50 text-indigo-700 border border-indigo-200 transition">
                                            Citită
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <span>{{ Auth::user()->name }}</span>

                            @if(Auth::user()?->role === 'admin')
                                <span id="nav-plan-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700">
                                    Admin
                                </span>
                            @else
                                @php
                                    $isPaidNavPlan = $currentUserPlan && (float) $currentUserPlan->price > 0;
                                    $navPlanName = $currentUserPlan?->name ?? 'Gratuit';
                                @endphp
                                <span id="nav-plan-badge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $isPaidNavPlan ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ $isPaidNavPlan ? '★ ' : '' }}{{ $navPlanName }}
                                </span>
                            @endif

                            <div class="ms-0.5">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if(Auth::user()?->role === 'admin')
                            <x-dropdown-link :href="route('admin.dashboard')" class="text-indigo-600 font-semibold border-b border-gray-100">
                                🛡️ {{ __('Admin Panel') }}
                            </x-dropdown-link>
                        @endif

                        <x-dropdown-link :href="route('listings.my')">
                            📋 {{ __('Anunțurile mele') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('plans.index')" class="flex items-center justify-between gap-2">
                            <span>⭐ {{ __('Planul meu') }}</span>
                            @if(Auth::user()?->role !== 'admin')
                                <span id="dropdown-plan-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($currentUserPlan && (float) $currentUserPlan->price > 0) ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $currentUserPlan?->name ?? 'Gratuit' }}
                                </span>
                            @endif
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Acasă') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('listings.index')" :active="request()->routeIs('listings.index') || request()->routeIs('listings.details')">
                {{ __('Anunțuri') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('listings.my')" :active="request()->routeIs('listings.my')">
                {{ __('Anunțurile mele') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('listings.create')" :active="request()->routeIs('listings.create')" class="text-emerald-600 font-semibold">
                + {{ __('Publică Anunț') }}
            </x-responsive-nav-link>

            @if(Auth::user()?->role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" class="text-indigo-600 font-semibold bg-indigo-50/50">
                    🛡️ {{ __('Admin Panel') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex items-center justify-between gap-2">
                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                @if(Auth::user()?->role !== 'admin')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($currentUserPlan && (float) $currentUserPlan->price > 0) ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                        {{ ($currentUserPlan && (float) $currentUserPlan->price > 0) ? '★ ' : '' }}{{ $currentUserPlan?->name ?? 'Gratuit' }}
                    </span>
                @endif
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('plans.index')" :active="request()->routeIs('plans.index')">
                    ⭐ {{ __('Planul meu') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    function notificationsBell() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        return {
            openBell: false,
            loading: false,
            hasLoaded: false,
            notifications: [],
            unreadCount: 0,
            pollTimer: null,

            init() {
                this.fetchNotifications();
                window.addEventListener('notifications-updated', () => this.fetchNotifications());
                window.addEventListener('focus', () => this.fetchNotifications());
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') {
                        this.fetchNotifications();
                    }
                });
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                }
                this.pollTimer = setInterval(() => {
                    if (document.visibilityState === 'visible') {
                        this.fetchNotifications();
                    }
                }, 2000);
            },

            async fetchNotifications() {
                if (!this.hasLoaded) {
                    this.loading = true;
                }
                try {
                    const response = await fetch('/api/notifications', {
                        headers: {
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache',
                        },
                        cache: 'no-store',
                        credentials: 'same-origin',
                    });
                    if (!response.ok) return;
                    const data = await response.json();
                    this.notifications = data.notifications || [];
                    this.unreadCount = Number(data.unread_count || 0);
                    this.hasLoaded = true;
                } catch (e) {
                    // ignore transient network errors in header poll
                } finally {
                    this.loading = false;
                }
            },

            async markRead(id) {
                const response = await fetch(`/api/notifications/${id}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                if (response.ok) {
                    await this.fetchNotifications();
                }
            },

            async markAllRead() {
                const response = await fetch('/api/notifications/read-all', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                if (response.ok) {
                    await this.fetchNotifications();
                }
            },

            async deleteAllNotifications() {
                const response = await fetch('/api/notifications', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                if (response.ok) {
                    this.notifications = [];
                    this.unreadCount = 0;
                }
            },

            formatDate(value) {
                if (!value) return '';
                return new Date(value).toLocaleString('ro-RO', { dateStyle: 'short', timeStyle: 'short' });
            },
        };
    }
</script>

@if(Auth::user()?->role === 'admin' && !request()->routeIs('admin.*'))
    <div class="bg-indigo-50 border-b border-indigo-200 text-indigo-800 text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                    Mod Previzualizare Utilizator
                </span>
                <span>Vizualizezi site-ul așa cum îl vede un utilizator normal.</span>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="shrink-0 font-semibold text-indigo-700 hover:text-indigo-900 underline">
                Înapoi la Admin Panel &rarr;
            </a>
        </div>
    </div>
@endif
