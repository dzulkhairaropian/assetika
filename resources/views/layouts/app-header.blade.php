<header
    class="sticky top-0 flex w-full bg-white border-b border-gray-200 z-[99999] dark:border-gray-800 dark:bg-gray-900 transition-colors duration-300"
    x-data="{
        isApplicationMenuOpen: false,
        toggleApplicationMenu() {
            this.isApplicationMenuOpen = !this.isApplicationMenuOpen;
        }
    }">
    <div class="flex flex-col items-center justify-between grow xl:flex-row xl:px-6 w-full">
        <div class="flex items-center justify-between w-full gap-2 px-4 py-3 border-b border-gray-100 dark:border-gray-800/50 sm:gap-4 xl:justify-normal xl:border-b-0 xl:px-0 lg:py-4">
            
            <!-- Desktop Sidebar Toggle Button (visible on xl and up) -->
            <button
                class="hidden xl:flex items-center justify-center w-10 h-10 text-gray-500 hover:text-brand-500 hover:bg-gray-50 border border-gray-200 rounded-xl dark:border-gray-800 dark:text-gray-400 dark:hover:text-white dark:hover:bg-white/[0.03] transition-all duration-200"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            <!-- Mobile Menu Toggle Button (visible below xl) -->
            <button
                class="flex xl:hidden items-center justify-center w-10 h-10 text-gray-500 hover:text-brand-500 hover:bg-gray-50 border border-gray-200 rounded-xl dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-all duration-200"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': $store.sidebar.isMobileOpen }"
                @click="$store.sidebar.toggleMobileOpen()" aria-label="Toggle Mobile Menu">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            <!-- Logo (mobile only) -->
            <a href="/" class="xl:hidden flex items-center gap-2">
                <img class="dark:hidden h-7" src="/images/assetika.png" alt="Logo" />
                <img class="hidden dark:block h-7" src="/images/assetika.png" alt="Logo" />
            </a>



            <!-- Application Menu Toggle (mobile only) -->
            <button @click="toggleApplicationMenu()"
                class="flex items-center justify-center w-10 h-10 text-gray-500 rounded-xl hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-850 xl:hidden transition-all duration-200">
                <i data-lucide="more-vertical" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Application Menu (mobile) and Right Side Actions (desktop) -->
        <div :class="isApplicationMenuOpen ? 'flex' : 'hidden'"
            class="items-center justify-between w-full gap-4 px-4 py-3 xl:flex shadow-theme-md xl:justify-end xl:px-0 xl:shadow-none bg-white dark:bg-gray-900 xl:bg-transparent border-t border-gray-150 dark:border-gray-800 xl:border-0">
            <div class="flex items-center gap-3 w-full xl:w-auto xl:justify-end">
                
                <!-- Theme Toggle Button -->
                <button
                    class="relative flex items-center justify-center text-gray-500 bg-white border border-gray-200 rounded-xl hover:text-brand-500 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white h-10 w-10 transition-all duration-200"
                    @click="$store.theme.toggle()"
                    aria-label="Toggle dark mode">
                    <span class="dark:hidden">
                        <i data-lucide="moon" class="w-5 h-5 text-gray-500"></i>
                    </span>
                    <span class="hidden dark:block">
                        <i data-lucide="sun" class="w-5 h-5 text-yellow-500"></i>
                    </span>
                </button>

                <!-- Notification Dropdown -->
                <div class="relative">
                    <x-header.notification-dropdown />
                </div>

                <div class="h-6 w-px bg-gray-200 dark:bg-gray-800 hidden sm:block"></div>

                <!-- User Dropdown -->
                <div class="relative">
                    <x-header.user-dropdown />
                </div>
            </div>
        </div>
    </div>
</header>
