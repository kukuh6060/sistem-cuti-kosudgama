<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->role == \App\Enums\UserRole::KEPALA_UNIT)
                    <x-nav-link :href="route('approval.unit.index')" :active="request()->routeIs('approval.unit.*')">
                        {{ __('Approval Unit') }}
                    </x-nav-link>
                    <x-nav-link :href="route('purchase.approval.unit.index')" :active="request()->routeIs('purchase.approval.unit.*')">
                        {{ __('Approval Pembelian') }}
                    </x-nav-link>
                    <x-nav-link :href="route('management.unit.index')" :active="request()->routeIs('management.unit.index')">
                        {{ __('Data Karyawan Unit') }}
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->role == \App\Enums\UserRole::SDM)
                    <x-nav-link :href="route('approval.sdm.index')" :active="request()->routeIs('approval.sdm.*')">
                        {{ __('Approval SDM') }}
                    </x-nav-link>
                    <x-nav-link :href="route('purchase.approval.sdm.index')" :active="request()->routeIs('purchase.approval.sdm.*')">
                        {{ __('Approval Pembelian') }}
                    </x-nav-link>
                    <x-nav-link
                        :href="route('management.managers.index')"
                        :active="request()->routeIs('management.managers.*')">
                        {{ __('Data Pengelola') }}
                    </x-nav-link>
                    <x-nav-link :href="route('management.all.index')" :active="request()->routeIs('management.all.index')">
                        {{ __('Data Karyawan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('absentee.index')" :active="request()->routeIs('absentee.*')">
                        {{ __('Data Absensi') }}
                    </x-nav-link>

                    <x-nav-link :href="route('report.index')" :active="request()->routeIs('report.*')">
                        {{ __('Laporan') }}
                    </x-nav-link>

                    <x-nav-link :href="route('settings.notes.edit')" :active="request()->routeIs('settings.notes.*')">
                        {{ __('Edit Catatan') }}
                    </x-nav-link>
                    @endif

                    @if(Auth::user()->role == \App\Enums\UserRole::PENGURUS)
                    <x-nav-link :href="route('approval.pengurus.index')" :active="request()->routeIs('approval.pengurus.*')">
                        {{ __('Approval Cuti') }}
                    </x-nav-link>
                    <x-nav-link :href="route('purchase.approval.pengurus.index')" :active="request()->routeIs('purchase.approval.pengurus.*')">
                        {{ __('Approval Pembelian') }}
                    </x-nav-link>
                    <x-nav-link :href="route('report.index')" :active="request()->routeIs('report.*')">
                        {{ __('Laporan') }}
                    </x-nav-link>
                    <x-nav-link :href="route('management.all.index')" :active="request()->routeIs('management.all.index')">
                        {{ __('Data Karyawan') }}
                    </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">

                <!-- Notifikasi Dropdown -->
                <x-dropdown align="right" width="96">
                    <x-slot name="trigger">
                        <button @click="markNotificationsAsRead()" class="relative inline-flex items-center p-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if($unreadNotificationsCount > 0)
                            <span id="notification-badge" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full transform translate-x-1/2 -translate-y-1/2">
                                {{ $unreadNotificationsCount }}
                            </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div style="width: 24rem;">
                            <div class="p-2 font-semibold text-sm text-gray-700 border-b">Notifikasi</div>
                            @forelse($unreadNotifications as $notification)
                            <a href="{{ $notification->data['url'] ?? '#' }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                style="white-space: normal;">
                                {{ $notification->data['message'] }}
                                <span class="block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                            </a>
                            @empty
                            <div class="px-4 py-3 text-sm text-gray-500 text-center">
                                Tidak ada notifikasi baru.
                            </div>
                            @endforelse
                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- User Dropdown -->
                <div style="margin-left: 1rem;">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
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
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open; markNotificationsAsRead()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(Auth::user()->role == \App\Enums\UserRole::KEPALA_UNIT)
            <x-responsive-nav-link :href="route('approval.unit.index')" :active="request()->routeIs('approval.unit.*')">
                {{ __('Approval Unit') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('purchase.approval.unit.index')" :active="request()->routeIs('purchase.approval.unit.*')">
                {{ __('Approval Pembelian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link
                :href="route('management.managers.index')"
                :active="request()->routeIs('management.managers.*')">
                {{ __('Data Pengelola') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.unit.index')" :active="request()->routeIs('management.unit.index')">
                {{ __('Data Karyawan Unit') }}
            </x-responsive-nav-link>
            @endif

            @if(Auth::user()->role == \App\Enums\UserRole::SDM)
            <x-responsive-nav-link :href="route('approval.sdm.index')" :active="request()->routeIs('approval.sdm.*')">
                {{ __('Approval SDM') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('purchase.approval.sdm.index')" :active="request()->routeIs('purchase.approval.sdm.*')">
                {{ __('Approval Pembelian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.all.index')" :active="request()->routeIs('management.all.index')">
                {{ __('Data Karyawan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('absentee.index')" :active="request()->routeIs('absentee.*')">
                {{ __('Data Absensi') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('report.index')" :active="request()->routeIs('report.*')">
                {{ __('Laporan') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('settings.notes.edit')" :active="request()->routeIs('settings.notes.*')">
                {{ __('Edit Catatan') }}
            </x-responsive-nav-link>
            @endif

            @if(Auth::user()->role == \App\Enums\UserRole::PENGURUS)
            <x-responsive-nav-link :href="route('approval.pengurus.index')" :active="request()->routeIs('approval.pengurus.*')">
                {{ __('Approval Cuti') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('purchase.approval.pengurus.index')" :active="request()->routeIs('purchase.approval.pengurus.*')">
                {{ __('Approval Pembelian') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('report.index')" :active="request()->routeIs('report.*')">
                {{ __('Laporan') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.all.index')" :active="request()->routeIs('management.all.index')">
                {{ __('Data Karyawan') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <!-- Notifikasi Mobile -->
            <div class="mt-3 space-y-1">
                <div class="px-4 py-2 font-semibold text-sm text-gray-700 border-t border-b">Notifikasi (@if($unreadNotificationsCount > 0)<span id="notification-badge-mobile">{{ $unreadNotificationsCount }}</span>@else 0 @endif)</div>
                @forelse($unreadNotifications as $notification)
                <x-responsive-nav-link :href="$notification->data['url'] ?? '#'" style="white-space: normal;">
                    {{ $notification->data['message'] }}
                    <span class="block text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                </x-responsive-nav-link>
                @empty
                <div class="px-4 py-3 text-sm text-gray-500">
                    Tidak ada notifikasi baru.
                </div>
                @endforelse
            </div>

            <!-- User Menu Mobile -->
            <div class="mt-3 space-y-1 border-t">
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
    function markNotificationsAsRead() {
        const badge = document.getElementById('notification-badge');
        const mobileBadge = document.getElementById('notification-badge-mobile');

        if (badge || mobileBadge) {
            fetch('{{ route("notifications.markAsRead") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (badge) badge.remove();
                        if (mobileBadge) mobileBadge.remove();
                    }
                }).catch(error => console.error('Error marking notifications as read:', error));
        }
    }
</script>