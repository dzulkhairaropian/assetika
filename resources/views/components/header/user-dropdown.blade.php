<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- User Button -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-400 active:scale-[0.98] transition-all"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span class="mr-3 overflow-hidden rounded-full h-11 w-11 shrink-0">
            <img src="{{ auth()->user()->avatar ?? '/images/avatars/avatar-boy.jpg' }}" alt="User" class="h-full w-full object-cover" />
        </span>

        <span class="block mr-1 font-medium text-theme-sm text-gray-800 dark:text-gray-200 truncate max-w-[140px]">{{ auth()->user()->name }}</span>

        <!-- Chevron Icon -->
        <svg
            class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 flex w-56 origin-top-right flex-col rounded-xl border border-gray-200/90 bg-white p-2.5 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-50"
        style="display: none;"
    >
        <!-- User Info -->
        <div class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-800 mb-1">
            <span class="block text-xs font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</span>
            <span class="block text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</span>
        </div>

        <!-- Menu Items -->
        <ul class="flex flex-col gap-0.5">
            <li>
                <a
                    href="/profile"
                    class="flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-brand-600 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-brand-400 transition-colors"
                >
                    <i data-lucide="user" class="w-4 h-4 text-gray-400 group-hover:text-brand-500"></i>
                    Edit Profile
                </a>
            </li>
        </ul>

        <!-- Sign Out -->
        <div class="border-t border-gray-100 dark:border-gray-800 mt-1 pt-1">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition-colors"
                >
                    <i data-lucide="log-out" class="w-4 h-4 text-error-500"></i>
                    Sign out
                </button>
            </form>
        </div>
    </div>
    <!-- Dropdown End -->
</div>
