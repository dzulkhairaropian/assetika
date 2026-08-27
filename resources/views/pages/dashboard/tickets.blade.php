@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="Ticket Dashboard" />
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-2 shrink-0" x-data="{ filterOpen: false }">
                    <!-- Filter Options Toggle Button -->
                    <div class="relative">
                        <button type="button" @click="filterOpen = !filterOpen"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-feather="filter" class="w-3.5 h-3.5 text-gray-500"></i>
                            <span>Filter Options</span>
                            @if($activeFiltersCount > 0)
                                <span class="flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white ml-0.5">{{ $activeFiltersCount }}</span>
                            @endif
                        </button>

                        <!-- Filter Options Dropdown Panel -->
                        <div x-show="filterOpen" @click.away="filterOpen = false" x-transition
                            class="absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            style="display: none;">
                            <form action="{{ route('ticket-dashboard') }}" method="GET">
                                <div class="mb-3 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2.5">
                                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-800 dark:text-white">Filter Options</h4>
                                    <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i data-feather="x" class="w-4 h-4"></i>
                                    </button>
                                </div>

                                <div class="space-y-3 text-left">
                                    <!-- Status -->
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                                        <select name="status_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->id }}" {{ (string) $filterStatusId === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Priority -->
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Priority</label>
                                        <select name="priority_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Priorities</option>
                                            @foreach($priorities as $priority)
                                                <option value="{{ $priority->id }}" {{ (string) $filterPriorityId === (string) $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Category -->
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Category</label>
                                        <select name="category_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ (string) $filterCategoryId === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Month & Year Grid -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Month</label>
                                            <select name="month" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                                @foreach($monthList as $mVal => $mLabel)
                                                    <option value="{{ $mVal }}" {{ (string) $filterMonth === (string) $mVal ? 'selected' : '' }}>{{ $mLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Year</label>
                                            <select name="year" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
                                                <option value="all">All Years</option>
                                                @foreach($ticketYears as $y)
                                                    <option value="{{ $y }}" {{ (string) $filterYear === (string) $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Buttons -->
                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <a href="{{ route('ticket-dashboard') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600 dark:text-gray-400 transition">
                                        Clear All Filters
                                    </a>
                                    <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition">
                                        Apply Filters
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <a href="{{ route('tickets.export', request()->all()) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="download" class="w-3.5 h-3.5 text-gray-500"></i> Export Excel
                    </a>
                    <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-3.5 h-3.5"></i> New Ticket
                    </a>
                    <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="list" class="w-3.5 h-3.5"></i> All Tickets
                    </a>
                </div>
            </div>
        </div>

        <!-- Metrics Widgets Row 1 -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            @php $totalTicketsTrendColor = $totalTicketsTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Total Tickets"
                icon="ticket"
                :value="$totalTickets"
                :trend="$totalTicketsTrend"
                :trendColor="$totalTicketsTrendColor"
                description="All support tickets ever created"
            />

            @php $openTicketsTrendColor = $openTicketsTrend <= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Open Tickets"
                icon="alert-circle"
                :value="$openTickets"
                :trend="$openTicketsTrend"
                :trendColor="$openTicketsTrendColor"
                description="Active tickets needing attention"
            />

            @php $resolvedTrendColor = $resolvedTrend >= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Resolved"
                icon="check-circle"
                :value="$resolved"
                :trend="$resolvedTrend"
                :trendColor="$resolvedTrendColor"
                description="Closed tickets this month"
            />

            @php $unassignedTrendColor = $unassignedTrend <= 0 ? 'success' : 'error'; @endphp
            <x-common.stat-card
                title="Unassigned"
                icon="user-x"
                :value="$unassigned"
                :trend="$unassignedTrend"
                :trendColor="$unassignedTrendColor"
                description="Tickets without IT staff assigned"
            />
        </div>

        <!-- Quick Stats Row -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-3 min-w-0">
            <x-common.stat-card
                title="My Tickets"
                icon="user"
                :value="$myTickets"
                description="{{ $myOpenTickets }} open tickets"
            />
            @php $avgResolution = round($avgResolution, 1); @endphp
            <x-common.stat-card
                title="Avg Resolution Time"
                icon="clock"
                value="{{ $avgResolution }} hrs"
                description="Time from open to closed"
            />
            @php $rate = $totalTickets > 0 ? round(($resolved / $totalTickets) * 100, 1) : 0; @endphp
            <x-common.stat-card
                title="Resolution Rate"
                icon="activity"
                value="{{ $rate }}%"
                description="Tickets resolved vs total"
            />
        </div>

        <!-- Charts Row 1: Trend + Status -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <!-- 1. Ticket Trend -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ticket Trend</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Monthly volume for {{ $trendYear }}</p>
                    </div>
                    <div class="relative shrink-0" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-1 rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-feather="calendar" class="w-3 h-3 text-brand-500"></i>
                            <span>Year: {{ $trendYear }}</span>
                            <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 w-max max-h-56 overflow-y-auto" style="display: none;">
                            @foreach($ticketYears as $year)
                                <a href="{{ request()->fullUrlWithQuery(['trend_year' => $year]) }}"
                                   class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ (string) $trendYear === (string) $year ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 font-bold' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                                    {{ $year }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="ticketTrendChart" class="h-64"></div>
            </div>

            <!-- 2. Tickets by Status -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tickets by Status</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Distribution across lifecycle</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-1 rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $monthList[$statusMonth] ?? 'All Months' }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-28" style="display: none;">
                                @foreach($monthList as $mVal => $mLabel)
                                    <a href="{{ request()->fullUrlWithQuery(['status_month' => $mVal]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $statusMonth === (string) $mVal ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $mLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-1 rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $statusYear === 'all' ? 'All Years' : $statusYear }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-24" style="display: none;">
                                <a href="{{ request()->fullUrlWithQuery(['status_year' => 'all']) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ $statusYear === 'all' ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">All Years</a>
                                @foreach($ticketYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['status_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $statusYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($chartStatusData['data']) > 0)
                    <div id="ticketStatusChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No status data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Charts Row 2: Category + Priority + Type (3 per row) -->
        <div class="col-span-12 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 min-w-0">
            <!-- 3. Top Categories -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">Top Categories</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">Frequent ticket categories</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $monthList[$categoryMonth] ?? 'All' }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-28" style="display: none;">
                                @foreach($monthList as $mVal => $mLabel)
                                    <a href="{{ request()->fullUrlWithQuery(['category_month' => $mVal]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $categoryMonth === (string) $mVal ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $mLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $categoryYear === 'all' ? 'All' : $categoryYear }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-24" style="display: none;">
                                <a href="{{ request()->fullUrlWithQuery(['category_year' => 'all']) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ $categoryYear === 'all' ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">All Years</a>
                                @foreach($ticketYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['category_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $categoryYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($categoryChart['data']) > 0)
                    <div id="ticketCategoryChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No category data available</p>
                    </div>
                @endif
            </div>

            <!-- 4. By Priority -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">By Priority</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">Urgency breakdown</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $monthList[$priorityMonth] ?? 'All' }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-28" style="display: none;">
                                @foreach($monthList as $mVal => $mLabel)
                                    <a href="{{ request()->fullUrlWithQuery(['priority_month' => $mVal]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $priorityMonth === (string) $mVal ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $mLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $priorityYear === 'all' ? 'All' : $priorityYear }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-24" style="display: none;">
                                <a href="{{ request()->fullUrlWithQuery(['priority_year' => 'all']) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ $priorityYear === 'all' ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">All Years</a>
                                @foreach($ticketYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['priority_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $priorityYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($priorityChart['data']) > 0)
                    <div id="ticketPriorityChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No priority data available</p>
                    </div>
                @endif
            </div>

            <!-- 5. By Type -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden md:col-span-2 xl:col-span-1">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">By Type</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">Classification</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $monthList[$typeMonth] ?? 'All' }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-28" style="display: none;">
                                @foreach($monthList as $mVal => $mLabel)
                                    <a href="{{ request()->fullUrlWithQuery(['type_month' => $mVal]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $typeMonth === (string) $mVal ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $mLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $typeYear === 'all' ? 'All' : $typeYear }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-24" style="display: none;">
                                <a href="{{ request()->fullUrlWithQuery(['type_year' => 'all']) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ $typeYear === 'all' ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">All Years</a>
                                @foreach($ticketYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['type_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $typeYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($typeChart['data']) > 0)
                    <div id="ticketTypeChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No type data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Charts Row 3: Requesters + Workload -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <!-- 6. Top Requesters -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">Top Requesters</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">Users with most submissions</p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $monthList[$requesterMonth] ?? 'All' }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-28" style="display: none;">
                                @foreach($monthList as $mVal => $mLabel)
                                    <a href="{{ request()->fullUrlWithQuery(['requester_month' => $mVal]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $requesterMonth === (string) $mVal ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $mLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $requesterYear === 'all' ? 'All' : $requesterYear }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-24" style="display: none;">
                                <a href="{{ request()->fullUrlWithQuery(['requester_year' => 'all']) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ $requesterYear === 'all' ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">All Years</a>
                                @foreach($ticketYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['requester_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $requesterYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if(count($requesterChart['data']) > 0)
                    <div id="ticketRequesterChart" class="h-64 flex items-center justify-center"></div>
                @else
                    <div class="flex flex-col items-center justify-center h-64 text-center">
                        <p class="text-xs text-gray-400 font-medium">No requester data available</p>
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IT Staff Workload</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Open tickets per assigned staff</p>
                </div>
                <div class="space-y-2.5">
                    @forelse($workload as $row)
                        @php $maxWl = $workload->max('count') ?: 1; $pct = ($row->count / $maxWl) * 100; @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300">
                                    {{ $staffNames[$row->assigned_to_id] ?? 'Unknown' }}
                                </span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $row->count }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-brand-500 transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-4 text-center">No assigned tickets</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Unassigned + Problem Assets Row -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <x-common.dashboard-table
                title="Unassigned Tickets"
                description="Open tickets without IT staff"
                :headers="['Ticket', 'Category', 'Priority', 'Date']"
            >
                @forelse($unassignedTickets as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-brand-600 dark:text-brand-400">#{{ $ticket->request_id }}</p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ Str::limit($ticket->requester_name, 20) }}</p>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">{{ $ticket->category?->name ?? '-' }}</td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge 
                                size="sm" 
                                :color="$ticket->priority?->color ?? 'warning'"
                                :featherIcon="$ticket->priority?->icon ?? 'alert-circle'"
                            >
                                {{ $ticket->priority?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $ticket->created_at->format('M d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">All tickets assigned</td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Problem Assets</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Assets with most ticket reports</p>
                </div>
                @php $paMax = $problemAssets->max('count') ?: 1; @endphp
                <div class="space-y-2.5">
                    @forelse($problemAssets as $row)
                        @php
                            $asset = $assetDetails[$row->asset_id] ?? null;
                            $paPct = min(100, round(($row->count / $paMax) * 100));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="truncate font-medium text-gray-700 dark:text-gray-300">
                                    {{ $asset?->asset_tag ?? 'Unknown' }} ({{ $asset?->device_name ?? '-' }})
                                </span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $row->count }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-red-500 transition-all duration-500" style="width: {{ $paPct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-4 text-center">No asset ticket data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Tickets Table -->
        <div class="col-span-12 min-w-0">
            <x-common.dashboard-table
                title="Recent Tickets"
                description="Latest support ticket activity"
                :headers="['ID', 'Requester', 'Category', 'Priority', 'Status', 'Assigned To', 'Created']"
                :footerLink="route('tickets.index')"
                footerText="View All Tickets"
            >
                @forelse($recentTickets as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <p class="font-medium text-brand-600 dark:text-brand-400">#{{ $ticket->request_id }}</p>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                            {{ $ticket->requester_name }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                            {{ $ticket->category?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge 
                                size="sm" 
                                :color="$ticket->priority?->color ?? 'secondary'"
                                :featherIcon="$ticket->priority?->icon ?? 'alert-circle'"
                            >
                                {{ $ticket->priority?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            <x-ui.badge 
                                size="sm" 
                                :color="$ticket->status?->color ?? 'primary'"
                                :featherIcon="$ticket->status?->icon ?? 'disc'"
                            >
                                {{ $ticket->status?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                            {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $ticket->created_at->format('M d, H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-xs text-gray-400">No support tickets found</td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    window.ticketChartData = {
        trendChart: @json($trendChart),
        chartStatusData: @json($chartStatusData),
        categoryChart: @json($categoryChart),
        priorityChart: @json($priorityChart),
        typeChart: @json($typeChart),
        requesterChart: @json($requesterChart),
    };
</script>
@vite('resources/js/ticket-charts.js')
@endpush
