<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
       class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200/80 bg-white px-4 dark:border-gray-800/80 dark:bg-gray-950 lg:static lg:translate-x-0 transition-all duration-300 ease-out">
    <!-- Sidebar Header -->
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
         class="flex items-center gap-2 pt-6 sidebar-header pb-6 border-b border-gray-100 dark:border-gray-800/60 mb-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <span class="logo flex items-center gap-2 font-bold text-lg text-gray-900 dark:text-white" :class="sidebarToggle ? 'hidden' : ''">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500 text-white font-extrabold shadow-sm">
                    A
                </div>
                <span class="tracking-tight">Assetika<span class="text-brand-500">.</span></span>
            </span>
            <div class="logo-icon flex h-9 w-9 items-center justify-center rounded-xl bg-brand-500 text-white font-extrabold shadow-sm" :class="sidebarToggle ? 'lg:flex' : 'hidden'">
                A
            </div>
        </a>
    </div>

    <!-- Main Navigation (Scrollable) -->
    <div class="flex-grow overflow-y-auto duration-300 ease-linear custom-scrollbar pb-6 pr-1">
        <nav x-data="{selected: $persist('Dashboard')}">
            @php
                $menuGroups = [
                    'Dashboards' => [
                        ['label' => 'IT Assets Control', 'route' => 'dashboard', 'icon' => 'grid'],
                        ['label' => 'GA & Facilities', 'route' => 'ga-dashboard', 'icon' => 'briefcase'],
                        ['label' => 'Financial Valuation', 'route' => 'finance-dashboard', 'icon' => 'trending-up'],
                        ['label' => 'Support Tickets', 'route' => 'ticket-dashboard', 'icon' => 'file-text'],
                    ],
                    'Assets Inventory' => [
                        ['label' => 'IT Hardware', 'route' => 'assets.index', 'icon' => 'hard-drive'],
                        ['label' => 'GA Assets', 'route' => 'ga-assets.index', 'icon' => 'package'],
                        ['label' => 'Software Licenses', 'route' => 'licenses.index', 'icon' => 'key'],
                        ['label' => 'Consumables', 'route' => 'consumables.index', 'icon' => 'box'],
                        ['label' => 'Component Kits', 'route' => 'kits.index', 'icon' => 'cpu'],
                        ['label' => 'Renewals & Subscriptions', 'route' => 'renewals.index', 'icon' => 'rotate-cw'],
                    ],
                    'Service Desk' => [
                        ['label' => 'Tickets Feed', 'route' => 'tickets.index', 'icon' => 'ticket'],
                        ['label' => 'Bulk Checkout', 'route' => 'assets.bulk-checkout', 'icon' => 'log-out'],
                    ],
                    'Master Data' => [
                        ['label' => 'Brands', 'route' => 'brands.index', 'icon' => 'tag'],
                        ['label' => 'Categories', 'route' => 'categories.index', 'icon' => 'layers'],
                        ['label' => 'Companies', 'route' => 'companies.index', 'icon' => 'globe'],
                        ['label' => 'Departments', 'route' => 'departments.index', 'icon' => 'git-branch'],
                        ['label' => 'Locations', 'route' => 'locations.index', 'icon' => 'map-pin'],
                        ['label' => 'Vendors', 'route' => 'vendors.index', 'icon' => 'truck'],
                        ['label' => 'Statuses', 'route' => 'statuses.index', 'icon' => 'check-circle'],
                    ],
                    'System & Sync' => [
                        ['label' => 'Assetika Sync', 'route' => 'admin.sync.index', 'params' => ['table' => 'assets'], 'icon' => 'refresh-cw'],
                        ['label' => 'Users & Access', 'route' => 'users.index', 'icon' => 'users'],
                        ['label' => 'System Backup', 'route' => 'admin.system.backup', 'icon' => 'database'],
                    ]
                ];
            @endphp

            @foreach($menuGroups as $group => $items)
                <div class="mb-5">
                    <h3 class="mb-2.5 px-2 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $group }}</span>
                    </h3>
                    <ul class="flex flex-col gap-1">
                        @foreach($items as $item)
                            @php
                                $params = $item['params'] ?? [];
                                $routeName = $item['route'];
                                $href = Route::has($routeName) ? route($routeName, $params) : '#';
                                $isActive = Route::has($routeName) && request()->routeIs(str_replace('.index', '', $routeName) . '*');
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                   class="group relative flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold transition-all duration-150 ease-out active:scale-[0.98] {{ $isActive ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 font-bold' : 'text-gray-600 hover:bg-gray-100/80 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-900/60 dark:hover:text-white' }}">
                                    @if($isActive)
                                        <span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r-full bg-brand-500"></span>
                                    @endif
                                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 shrink-0 transition-colors {{ $isActive ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-300' }}"></i>
                                    <span class="menu-item-text truncate" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </div>

    <!-- Pinned Bottom Section -->
    <div class="border-t border-gray-200/80 dark:border-gray-800/80 py-3 mt-auto">
        <a href="/profile" class="group relative flex items-center gap-3 rounded-lg px-3 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100/80 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-900/60 dark:hover:text-white transition-all duration-150 ease-out active:scale-[0.98]">
            <i data-lucide="user" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 dark:text-gray-500 dark:group-hover:text-gray-300"></i>
            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Profile Settings</span>
        </a>
    </div>
</aside>