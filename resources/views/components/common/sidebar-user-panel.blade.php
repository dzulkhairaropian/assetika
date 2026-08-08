<div class="border-t border-gray-200 dark:border-gray-800 p-5 duration-300 ease-linear"
    :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
        'xl:p-2.5' : 'xl:p-5'">
    <div class="flex items-center gap-3"
        x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
        <div class="relative w-full">
            <button @click="dropdownOpen = !dropdownOpen"
                class="flex w-full items-center justify-between overflow-hidden rounded-lg p-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                    'xl:justify-center' : 'xl:justify-between'">
                <div class="flex items-center gap-2.5 overflow-hidden"
                    x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                    <img src="{{ auth()->user()->avatar ?? '/images/avatars/avatar-boy.jpg' }}" alt="User Avatar"
                        class="h-8 w-8 rounded-full object-cover shrink-0">
                    <div class="flex flex-col text-left truncate leading-tight">
                        <span class="truncate font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name ?? 'User' }}</span>
                        <span class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                            {{ auth()->user()->position?->name ?? auth()->user()->getRoleNames()->first() ?? 'User' }}
                        </span>
                    </div>
                </div>
                <div x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                    <img src="{{ auth()->user()->avatar ?? '/images/avatars/avatar-boy.jpg' }}" alt="User Avatar"
                        class="h-7 w-7 rounded-full object-cover">
                </div>
                <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                    class="h-4 w-4 transform transition-transform duration-200 shrink-0 ml-1"
                    :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div x-show="dropdownOpen" x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute bottom-full left-0 z-10 mb-2 w-full rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900"
                :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ?
                    'xl:left-full xl:ml-2' : ''">
                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800 text-xs text-gray-500">
                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->email }}</p>
                    <p class="text-[11px] text-brand-600 dark:text-brand-400 capitalize mt-0.5">
                        Role: {{ auth()->user()->getRoleNames()->first() ?? 'Member' }}
                    </p>
                </div>
                <ul class="py-1">
                    <li>
                        <a href="{{ route('profile.index') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Profile</a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Sign
                                Out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
