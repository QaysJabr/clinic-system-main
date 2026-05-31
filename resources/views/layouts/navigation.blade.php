@php $uiRtl = in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true); @endphp
<nav x-data="{ open: false, dropdownOpen: false }" class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg border-b border-blue-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-white text-2xl font-bold flex items-center gap-2 hover:opacity-80 transition">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        {{ __('navigation.clinic_product') }}
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden lg:flex lg:space-x-1 lg:ms-10">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                            </svg>
                            {{ __('navigation.dashboard') }}
                        </div>
                    </a>

                    @role('super_admin')
                    <a href="{{ route('platform.dashboard') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('platform.*', 'admin.*') ? 'bg-indigo-600 shadow-lg ring-1 ring-white/30' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3h12v4H4V3zm0 6h5v8H4V9zm7 0h5v8h-5V9z"/></svg>
                            {{ __('navigation.platform_dashboard') }}
                        </div>
                    </a>
                    @endrole

                    @can(\App\Support\ClinicPermissions::MANAGE_VISITS)
                    <a href="{{ route('reception.dashboard') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('reception.dashboard') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12v2H4V4zm0 4h12v10H4V8zm2 2v6h8v-6H6z"/></svg>
                            {{ __('navigation.reception_dashboard') }}
                        </div>
                    </a>
                    @endcan

                    @if(auth()->user()?->hasRole('doctor') || auth()->user()?->hasRole('admin'))
                    <a href="{{ route('clinical.dashboard') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('clinical.dashboard') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3a1 1 0 001 1h3a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                            {{ __('doctors.nav_doctor_desk') }}
                        </div>
                    </a>
                    @endif

                    <!-- Patients -->
                    <a href="{{ route('patients.index') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('patients.*') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            {{ __('patients.title') }}
                        </div>
                    </a>

                    <!-- Doctors -->
                    <a href="{{ route('doctors.index') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('doctors.*') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM15.657 14.243a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 17v-1a1 1 0 10-2 0v1a1 1 0 102 0zM5.343 14.243a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414l-.707.707zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zM5.343 5.757a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 011.414-1.414l.707.707z"/>
                            </svg>
                            {{ __('doctors.nav_doctors') }}
                        </div>
                    </a>

                    <!-- Appointments -->
                    <a href="{{ route('appointments.index') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('appointments.*') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v2h16V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h12a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('appointments.nav_appointments') }}
                        </div>
                    </a>

                    <!-- Visits -->
                    <a href="{{ route('visits.index') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('visits.*') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2A4 4 0 000 5v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 000 2 2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('visits.nav_visits') }}
                        </div>
                    </a>

                    <!-- Invoices -->
                    <a href="{{ route('invoices.index') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('invoices.*') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('invoices.nav') }}
                        </div>
                    </a>

                    @can(\App\Support\ClinicPermissions::MANAGE_INVOICES)
                    <a href="{{ route('services.index') }}" class="group relative px-4 py-2 text-white font-medium rounded-lg transition-all duration-200 {{ request()->routeIs('services.*') ? 'bg-blue-500 shadow-lg' : 'hover:bg-blue-500/50' }}">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM6 11a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z"/></svg>
                            {{ __('navigation.services_nav') }}
                        </div>
                    </a>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:flex lg:items-center lg:gap-4">
                <!-- Search Bar -->
                <div class="relative hidden xl:block">
                    <input type="text" placeholder="{{ __('navigation.search_placeholder') }}" class="bg-blue-500/50 text-white placeholder-blue-200 rounded-lg px-4 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-white/50 focus:bg-blue-500" dir="{{ $uiRtl ? 'rtl' : 'ltr' }}">
                    <svg class="absolute start-3 top-2.5 w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- User Dropdown -->
                <div class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-500/50 text-white hover:bg-blue-500 transition-all duration-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': dropdownOpen }" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div x-show="dropdownOpen"
                        @click.away="dropdownOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute {{ $uiRtl ? 'right-0' : 'left-0' }} mt-2 w-48 bg-white rounded-lg shadow-xl z-50">

                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-medium text-gray-800 dark:text-[#F3F4F6]">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-[#9CA3AF]">{{ Auth::user()->email }}</p>
                        </div>

                        @role('super_admin')
                        <a href="{{ route('platform.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-[#E5E7EB] hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3h12v4H4V3zm0 6h5v8H4V9zm7 0h5v8h-5V9z"/></svg>
                            {{ __('navigation.platform_dashboard') }}
                        </a>
                        @endrole

                        @role('admin')
                        @unless(auth()->user()->hasRole('super_admin'))
                        <a href="{{ route('saas.billing') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-[#E5E7EB] hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12v2H4V4zm0 4h12v8H4V8zm2 2v4h8v-4H6z"/></svg>
                            {{ __('subscriptions.nav_billing') }}
                        </a>
                        @endunless
                        @endrole

                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-[#E5E7EB] hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            {{ __('navigation.profile') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200">
                            @csrf
                            <button type="submit" class="w-full text-end flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-[#E5E7EB] hover:bg-red-50 transition text-red-600 hover:text-red-700">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 1.293a1 1 0 010 1.414L9.414 9l1.293 1.293a1 1 0 01-1.414 1.414L8 10.414l-1.293 1.293a1 1 0 01-1.414-1.414L6.586 9 5.293 7.707a1 1 0 011.414-1.414L8 7.586l1.293-1.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ __('navigation.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Hamburger Menu -->
            <div class="flex lg:hidden items-center gap-4">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-lg text-white hover:bg-blue-500 focus:outline-none transition">
                    <svg class="h-6 w-6" :class="{ 'hidden': open }" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="h-6 w-6 hidden" :class="{ 'hidden': !open }" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-cloak :class="{ 'block': open, 'hidden': !open }" class="hidden lg:hidden bg-blue-700 border-t border-blue-800">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('dashboard') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                    {{ __('navigation.dashboard') }}
                </div>
            </a>

            @role('super_admin')
            <a href="{{ route('platform.dashboard') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('platform.*') ? 'bg-indigo-600' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3h12v4H4V3zm0 6h5v8H4V9zm7 0h5v8h-5V9z"/></svg>
                    {{ __('navigation.platform_dashboard') }}
                </div>
            </a>
            @endrole

            @can(\App\Support\ClinicPermissions::MANAGE_VISITS)
            <a href="{{ route('reception.dashboard') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('reception.dashboard') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4h12v2H4V4zm0 4h12v10H4V8zm2 2v6h8v-6H6z"/></svg>
                    {{ __('navigation.reception_dashboard') }}
                </div>
            </a>
            @endcan

            @if(auth()->user()?->hasRole('doctor') || auth()->user()?->hasRole('admin'))
            <a href="{{ route('clinical.dashboard') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('clinical.dashboard') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3a1 1 0 001 1h3a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                    {{ __('doctors.nav_doctor_desk') }}
                </div>
            </a>
            @endif

            <a href="{{ route('patients.index') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('patients.*') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                    {{ __('patients.title') }}
                </div>
            </a>

            <a href="{{ route('doctors.index') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('doctors.*') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM15.657 14.243a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM11 17v-1a1 1 0 10-2 0v1a1 1 0 102 0zM5.343 14.243a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414l-.707.707zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zM5.343 5.757a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 011.414-1.414l.707.707z"/>
                    </svg>
                    {{ __('doctors.nav_doctors') }}
                </div>
            </a>

            <a href="{{ route('appointments.index') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('appointments.*') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v2h16V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h12a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('appointments.nav_appointments') }}
                </div>
            </a>

            <a href="{{ route('visits.index') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('visits.*') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2A4 4 0 000 5v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 000 2 2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('visits.nav_visits') }}
                </div>
            </a>

            <a href="{{ route('invoices.index') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('invoices.*') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('invoices.nav') }}
                </div>
            </a>

            @can(\App\Support\ClinicPermissions::MANAGE_INVOICES)
            <a href="{{ route('services.index') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition {{ request()->routeIs('services.*') ? 'bg-blue-500' : '' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM6 11a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z"/></svg>
                    {{ __('navigation.services_nav') }}
                </div>
            </a>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-blue-800 px-2 py-3 space-y-1">
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-white font-medium hover:bg-blue-500 transition">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    {{ __('navigation.profile') }}
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-end px-3 py-2 rounded-lg text-white font-medium hover:bg-red-600 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 1.293a1 1 0 010 1.414L9.414 9l1.293 1.293a1 1 0 01-1.414 1.414L8 10.414l-1.293 1.293a1 1 0 01-1.414-1.414L6.586 9 5.293 7.707a1 1 0 011.414-1.414L8 7.586l1.293-1.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    {{ __('navigation.logout') }}
                </button>
            </form>
        </div>
    </div>
</nav>
