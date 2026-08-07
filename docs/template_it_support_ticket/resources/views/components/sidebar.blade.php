<aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
       class="sidebar fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-black lg:static lg:translate-x-0">
    <!-- Sidebar Header -->
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
         class="flex items-center gap-2 pt-8 sidebar-header pb-7">
        <a href="{{ route('dashboard') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="dark:hidden" src="{{ asset('images/logo.svg') }}" alt="Logo" />
                <img class="hidden dark:block" src="{{ asset('images/logo-dark.svg') }}" alt="Logo" />
            </span>
            <img class="logo-icon" :class="sidebarToggle ? 'lg:block' : 'hidden'"
                 src="{{ asset('images/logo-icon.svg') }}" alt="Logo" />
        </a>
    </div>

    <!-- Main Navigation (Scrollable) -->
    <div class="flex-grow overflow-y-auto duration-300 ease-linear no-scrollbar pb-6">
        <nav x-data="{selected: $persist('Dashboard')}">
            @php
                $menuGroups = [
                    'Main' => [
                        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
                        ['label' => 'Tickets', 'route' => 'tickets.index', 'icon' => 'file-text'],
                    ],
                    'Assetika Sync' => [
                        ['label' => 'Companies', 'route' => 'admin.sync.index', 'params' => ['table' => 'companies'], 'icon' => 'briefcase'],
                        ['label' => 'Departments', 'route' => 'admin.sync.index', 'params' => ['table' => 'departments'], 'icon' => 'git-branch'],
                        ['label' => 'Positions', 'route' => 'admin.sync.index', 'params' => ['table' => 'positions'], 'icon' => 'user-check'],
                        ['label' => 'Locations', 'route' => 'admin.sync.index', 'params' => ['table' => 'locations'], 'icon' => 'map-pin'],
                        ['label' => 'Users', 'route' => 'admin.sync.index', 'params' => ['table' => 'users'], 'icon' => 'users'],
                        ['label' => 'Assets', 'route' => 'admin.sync.index', 'params' => ['table' => 'assets'], 'icon' => 'monitor'],
                    ],
                    'Master Data' => [
                        ['label' => 'Priorities', 'route' => 'admin.priorities.index', 'icon' => 'alert-circle'],
                        ['label' => 'Labels', 'route' => 'admin.labels.index', 'icon' => 'bookmark'],
                        ['label' => 'Sources', 'route' => 'admin.sources.index', 'icon' => 'globe'],
                        ['label' => 'Ticket Types', 'route' => 'admin.ticket-types.index', 'icon' => 'layers'],
                        ['label' => 'Statuses', 'route' => 'admin.ticket-statuses.index', 'icon' => 'check-circle'],
                        ['label' => 'SLA Statuses', 'route' => 'admin.sla-statuses.index', 'icon' => 'clock'],
                    ],
                    'System & Logs' => [
                        ['label' => 'Comments', 'route' => 'admin.system.comments', 'icon' => 'message-square'],
                        ['label' => 'Histories', 'route' => 'admin.system.histories', 'icon' => 'activity'],
                        ['label' => 'Attachments', 'route' => 'admin.system.attachments', 'icon' => 'paperclip'],
                        ['label' => 'Backup', 'route' => 'admin.system.backup', 'icon' => 'database'],
                    ]
                ];
            @endphp

            @foreach($menuGroups as $group => $items)
                <div class="mb-6">
                    <h3 class="mb-4 text-xs uppercase leading-[20px] text-gray-400">
                        <span class="menu-group-title" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $group }}</span>
                    </h3>
                    <ul class="flex flex-col gap-2">
                        @foreach($items as $item)
                            @php
                                $params = $item['params'] ?? [];
                                $routeName = $item['route'];
                                $href = $routeName === '#' ? '#' : route($routeName, $params);
                                $isActive = request()->routeIs($routeName . '*');
                                $iconClass = $isActive ? 'menu-item-icon-active' : 'menu-item-icon-inactive';
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                   class="menu-item group {{ $isActive ? 'menu-item-active' : 'menu-item-inactive' }}">
                                    <i data-feather="{{ $item['icon'] }}" class="{{ $iconClass }} size-5"></i>
                                    <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </div>

    <!-- Pinned Bottom Section -->
    <div class="border-t border-gray-200 dark:border-gray-800 p-4">
        <a href="/profile" class="menu-item group {{ request()->routeIs('profile') ? 'menu-item-active' : 'menu-item-inactive' }}">
            <i data-feather="user" class="{{ request()->routeIs('profile') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }} size-5"></i>
            <span class="menu-item-text" :class="sidebarToggle ? 'lg:hidden' : ''">Profile</span>
        </a>
    </div>
</aside>