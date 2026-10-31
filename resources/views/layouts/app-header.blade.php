<header
    class="sticky top-0 flex w-full bg-white/85 backdrop-blur-md border-b border-gray-200/80 shadow-xs z-[9990] dark:border-gray-800/80 dark:bg-gray-900/85 transition-colors duration-150">
    <div class="flex items-center justify-between w-full px-4 xl:px-6 py-2.5 sm:py-3">

        <!-- Left: Hamburger + Mobile Logo -->
        <div class="flex items-center gap-3">
            <!-- Desktop Sidebar Toggle -->
            <button
                class="hidden xl:flex items-center justify-center w-10 h-10 text-gray-500 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 hover:text-brand-500 hover:bg-gray-50 dark:hover:text-white dark:hover:bg-white/[0.03] transition-colors shrink-0 active:scale-95"
                :class="{ 'bg-gray-100 dark:bg-white/[0.03]': !$store.sidebar.isExpanded }"
                @click.stop="$store.sidebar.toggleExpanded()" aria-label="Toggle Sidebar">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>

            <!-- Mobile Sidebar Toggle -->
            <button
                class="flex xl:hidden items-center justify-center w-10 h-10 text-gray-500 rounded-xl hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors shrink-0"
                @click.stop="$store.sidebar.toggleMobileOpen()" aria-label="Toggle Mobile Menu">
                <i data-lucide="menu" class="w-5 h-5" x-show="!$store.sidebar.isMobileOpen"></i>
                <i data-lucide="x" class="w-5 h-5" x-show="$store.sidebar.isMobileOpen" style="display: none;"></i>
            </button>

            <!-- Logo (mobile only) -->
            <a href="/" class="xl:hidden flex items-center">
                <img class="h-7 w-auto object-contain dark:hidden" src="{{ \App\Helpers\AppSetting::get('logo_light', '/images/assetika.png') }}" alt="{{ \App\Helpers\AppSetting::get('app_name', 'Assetika') }}" />
                <img class="h-7 w-auto object-contain hidden dark:block" src="{{ \App\Helpers\AppSetting::get('logo_dark', '/images/assetika.png') }}" alt="{{ \App\Helpers\AppSetting::get('app_name', 'Assetika') }}" />
            </a>
        </div>

        <!-- Center: Quick Search Bar on Desktop -->
        <div class="hidden lg:flex items-center flex-1 max-w-md mx-6">
            <form action="{{ route('assets.index') }}" method="GET" class="relative w-full">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </span>
                <input type="text" name="search" placeholder="Search assets, serial numbers, tags..."
                    class="h-9 w-full rounded-xl border border-gray-200 bg-gray-50/70 pl-9 pr-8 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 dark:focus:bg-gray-900 transition-all">
                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700 pointer-events-none">
                    /
                </span>
            </form>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Theme Toggle -->
            <button
                class="flex items-center justify-center w-10 h-10 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white transition-colors active:scale-95 shrink-0"
                @click="$store.theme.toggle()" aria-label="Toggle dark mode">
                <span class="dark:hidden"><i data-lucide="moon" class="w-4.5 h-4.5"></i></span>
                <span class="hidden dark:block"><i data-lucide="sun" class="w-4.5 h-4.5 text-amber-400"></i></span>
            </button>

            <!-- Notification Dropdown -->
            <x-header.notification-dropdown />

            <!-- User Dropdown -->
            <x-header.user-dropdown />
        </div>
    </div>
</header>
