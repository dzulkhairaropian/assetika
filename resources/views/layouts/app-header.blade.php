<header
    class="sticky top-0 flex w-full bg-white border-b border-gray-200 z-[99999] dark:border-gray-800 dark:bg-gray-900 transition-colors duration-300"
    x-data="{ isApplicationMenuOpen: false }">
    <div class="flex items-center justify-between w-full px-4 xl:px-6 py-3 xl:py-4">

        <!-- Left: Hamburger + Logo -->
        <div class="flex items-center gap-3">
            <!-- Desktop Sidebar Toggle -->
            <button
                class="hidden xl:flex items-center justify-center w-11 h-11 text-gray-500 hover:text-brand-500 hover:bg-gray-50 border border-gray-200 rounded-xl dark:border-gray-800 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.03] transition-all duration-200 shrink-0"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <i data-feather="menu" class="w-5 h-5"></i>
            </button>

            <!-- Mobile Sidebar Toggle -->
            <button
                class="flex xl:hidden items-center justify-center w-11 h-11 text-gray-500 hover:text-brand-500 hover:bg-gray-50 border border-gray-200 rounded-xl dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-all duration-200 shrink-0"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': $store.sidebar.isMobileOpen }"
                @click="$store.sidebar.toggleMobileOpen()" aria-label="Toggle Mobile Menu">
                <i data-feather="menu" class="w-5 h-5"></i>
            </button>

            <!-- Logo (mobile only) -->
            <a href="/" class="xl:hidden flex items-center">
                <img class="h-7" src="/images/assetika.png" alt="Logo" />
            </a>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Mobile app-menu toggle -->
            <button
                class="flex xl:hidden items-center justify-center w-10 h-10 text-gray-500 rounded-xl hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-all duration-200 shrink-0"
                @click="isApplicationMenuOpen = !isApplicationMenuOpen"
                aria-label="Toggle Application Menu">
                <i data-feather="more-vertical" class="w-5 h-5"></i>
            </button>

            <!-- Desktop actions (always visible on xl+) -->
            <div class="hidden xl:flex items-center gap-2">
                <!-- Theme Toggle -->
                <button
                    class="flex items-center justify-center w-11 h-11 text-gray-500 bg-white border border-gray-200 rounded-xl hover:text-brand-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white transition-all duration-200 shrink-0"
                    @click="$store.theme.toggle()" aria-label="Toggle dark mode">
                    <span class="dark:hidden"><i data-feather="moon" class="w-5 h-5"></i></span>
                    <span class="hidden dark:block"><i data-feather="sun" class="w-5 h-5 text-yellow-500"></i></span>
                </button>

                <!-- Notification Dropdown -->
                <x-header.notification-dropdown />

                <!-- User Dropdown -->
                <x-header.user-dropdown />
            </div>

            <!-- Mobile actions (shown when isApplicationMenuOpen) -->
            <div
                x-show="isApplicationMenuOpen"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full right-0 left-0 z-50 flex flex-col gap-0 bg-white dark:bg-gray-900 border-t border-b border-gray-200 dark:border-gray-800 shadow-lg xl:hidden"
                style="display: none;"
                @click.outside="isApplicationMenuOpen = false">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Quick Actions</span>
                    <div class="flex items-center gap-2">
                        <!-- Theme Toggle mobile -->
                        <button
                            class="flex items-center justify-center w-11 h-11 text-gray-500 bg-white border border-gray-200 rounded-xl hover:text-brand-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white transition-all duration-200 shrink-0"
                            @click="$store.theme.toggle()" aria-label="Toggle dark mode">
                            <span class="dark:hidden"><i data-feather="moon" class="w-5 h-5"></i></span>
                            <span class="hidden dark:block"><i data-feather="sun" class="w-5 h-5 text-yellow-500"></i></span>
                        </button>

                        <!-- Notification mobile -->
                        <x-header.notification-dropdown />

                        <!-- User mobile -->
                        <x-header.user-dropdown />
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
