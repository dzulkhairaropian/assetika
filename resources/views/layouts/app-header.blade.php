<header
    class="sticky top-0 flex w-full bg-white/85 backdrop-blur-md border-b border-gray-200/80 shadow-xs z-[9990] dark:border-gray-800/80 dark:bg-gray-900/85 transition-all duration-200"
    x-data="{ isApplicationMenuOpen: false }">
    <div class="flex items-center justify-between w-full px-4 xl:px-6 py-3 xl:py-4">

        <!-- Left: Hamburger + Logo -->
        <div class="flex items-center gap-3">
            <!-- Desktop Sidebar Toggle -->
            <button
                class="z-99999 hidden xl:flex items-center justify-center w-11 h-11 text-gray-500 rounded-lg border border-gray-200 dark:border-gray-800 dark:text-gray-400 hover:text-brand-500 hover:bg-gray-50 dark:hover:text-white dark:hover:bg-white/[0.03] transition-colors shrink-0 active:scale-95"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click.stop="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <i data-feather="menu" class="w-5 h-5"></i>
            </button>

            <!-- Mobile Sidebar Toggle -->
            <button
                class="z-99999 flex xl:hidden items-center justify-center w-10 h-10 text-gray-500 rounded-lg hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors shrink-0"
                :class="{ 'bg-gray-100 dark:bg-gray-800': $store.sidebar.isMobileOpen }"
                @click.stop="$store.sidebar.toggleMobileOpen()" aria-label="Toggle Mobile Menu">
                <svg class="fill-current" :class="$store.sidebar.isMobileOpen ? 'hidden' : 'block'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z" fill="" />
                </svg>
                <svg class="fill-current" :class="$store.sidebar.isMobileOpen ? 'block' : 'hidden'" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z" fill="" />
                </svg>
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
            <div class="hidden xl:flex items-center gap-4">
                <div class="flex items-center gap-2">
                     <!-- Theme Toggle -->
                    <button
                        class="relative flex items-center justify-center w-11 h-11 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors active:scale-95 shrink-0"
                        @click="$store.theme.toggle()" aria-label="Toggle dark mode">
                        <span class="dark:hidden"><i data-feather="moon" class="w-5 h-5"></i></span>
                        <span class="hidden dark:block"><i data-feather="sun" class="w-5 h-5 text-amber-400"></i></span>
                    </button>

                    <!-- Notification Dropdown -->
                    <x-header.notification-dropdown />
                </div>

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
                            class="relative flex items-center justify-center w-11 h-11 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors shrink-0"
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
