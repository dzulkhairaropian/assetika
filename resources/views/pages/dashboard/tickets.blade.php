@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support & Ticket Operations Center</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Live tracking of service desk tickets, SLA response times, and staff workload.</p>
        </div>
	        <div class="flex flex-wrap items-center gap-2" x-data="{ filterOpen: false }">
	            <!-- Filter Options Toggle Button -->
	            <div class="relative">
	                <button type="button" @click="filterOpen = !filterOpen"
	                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
	                    <i data-lucide="filter" class="w-4 h-4 text-gray-500"></i>
	                    <span>Filter Options</span>
	                    @if($activeFiltersCount > 0)
	                        <span class="flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white ml-0.5">{{ $activeFiltersCount }}</span>
	                    @endif
	                </button>

	                <!-- Filter Options Dropdown Panel -->
	                <div x-show="filterOpen" @click.away="filterOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
	                    class="absolute right-0 top-full z-50 mt-2 origin-top-right w-80 sm:w-96 rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-800 dark:bg-gray-900"
	                    style="display: none;">
	                    <form action="{{ route('ticket-dashboard') }}" method="GET">
	                        <div class="mb-3 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2.5">
	                            <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-800 dark:text-white">Filter Options</h4>
	                            <button type="button" @click="filterOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
	                                <i data-lucide="x" class="w-4 h-4"></i>
	                            </button>
	                        </div>

	                        <div class="space-y-3 text-left">
	                            <div>
	                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
	                                <select name="status_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
	                                    <option value="">All Statuses</option>
	                                    @foreach($statuses as $status)
	                                        <option value="{{ $status->id }}" {{ (string) $filterStatusId === (string) $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
	                                    @endforeach
	                                </select>
	                            </div>
		                            <div>
		                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Priority</label>
		                                <select name="priority_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
		                                    <option value="">All Priorities</option>
		                                    @foreach($priorities as $priority)
		                                        <option value="{{ $priority->id }}" {{ (string) $filterPriorityId === (string) $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
		                                    @endforeach
		                                </select>
		                            </div>
		                            <div>
		                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">ITSM Category</label>
		                                <select name="category_id" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
		                                    <option value="">All Categories</option>
		                                    @foreach($categories as $category)
		                                        <option value="{{ $category->id }}" {{ (string) $filterCategoryId === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
		                                    @endforeach
		                                </select>
		                            </div>
		                        </div>

	                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
	                            <a href="{{ route('ticket-dashboard') }}" class="text-xs font-medium text-gray-500 hover:text-brand-600">Clear All</a>
	                            <button type="submit" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 active:scale-[0.97] transition-all duration-150">Apply</button>
	                        </div>
	                    </form>
	                </div>
	            </div>

	            <a href="{{ route('tickets.export', request()->all()) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-[0.97] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-all duration-150 ease-out">
	                <i data-lucide="download" class="w-4 h-4 text-gray-500"></i> Export
	            </a>
	            <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.97] transition-all duration-150 ease-out">
	                <i data-lucide="plus" class="w-4 h-4"></i> New Ticket
	            </a>
	        </div>
    </div>

    <!-- 1. Key Metrics Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php $totalTicketsTrendColor = $totalTicketsTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Total Tickets"
            icon="ticket"
            :value="$totalTickets"
            :trend="$totalTicketsTrend"
            :trendColor="$totalTicketsTrendColor"
            description="All support tickets ever created"
            color="brand"
            :url="route('tickets.index')"
        />

        @php $openTicketsTrendColor = $openTicketsTrend <= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Open Tickets"
            icon="alert-circle"
            :value="$openTickets"
            :trend="$openTicketsTrend"
            :trendColor="$openTicketsTrendColor"
            description="Active tickets needing resolution"
            color="danger"
            :url="route('tickets.index')"
        />

        @php $resolvedTrendColor = $resolvedTrend >= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Resolved Tickets"
            icon="circle-check"
            :value="$resolved"
            :trend="$resolvedTrend"
            :trendColor="$resolvedTrendColor"
            description="Closed tickets this period"
            color="success"
            :url="route('tickets.index')"
        />

        @php $unassignedTrendColor = $unassignedTrend <= 0 ? 'emerald' : 'red'; @endphp
        <x-common.small-box
            title="Unassigned Tickets"
            icon="user-x"
            :value="$unassigned"
            :trend="$unassignedTrend"
            :trendColor="$unassignedTrendColor"
            description="Awaiting staff allocation"
            color="warning"
            :url="route('tickets.index')"
        />
    </div>

    <!-- 2. Performance Metric Cards Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-common.small-box
            title="My Assigned Tickets"
            icon="user"
            :value="$myTickets"
            description="{{ $myOpenTickets }} open tickets pending"
            color="info"
            :url="route('tickets.index')"
        />
        @php
            $avgRespFormatted = $avgResponse >= 60 ? round($avgResponse / 60, 1).' hrs' : round($avgResponse).' mins';
        @endphp
        <x-common.small-box
            title="Avg First Response"
            icon="message-square"
            value="{{ $avgRespFormatted }}"
            description="Mean Time to Acknowledge (MTTA)"
            color="info"
            :url="route('tickets.index')"
        />
        @php $avgResolution = round($avgResolution, 1); @endphp
        <x-common.small-box
            title="Avg Resolution Time"
            icon="clock"
            value="{{ $avgResolution }} hrs"
            description="Mean Time to Resolve (MTTR)"
            color="brand"
            :url="route('tickets.index')"
        />
        @php $rate = $totalTickets > 0 ? round(($resolved / $totalTickets) * 100, 1) : 0; @endphp
        <x-common.small-box
            title="SLA Resolution Rate"
            icon="activity"
            value="{{ $rate }}%"
            description="Overall ticket completion efficiency"
            color="success"
            :url="route('tickets.index')"
        />
    </div>

    <!-- 3. Ticket Analytics & Conversion Funnel Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900" x-data="{ timeframe: '30days' }">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ticket Volume Trend</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">Visitor analytics of last 30 days</span>
                </div>
                <div class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900 border border-gray-200/50 dark:border-gray-800">
                    <button @click="timeframe = '12months'" :class="timeframe === '12months' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">12 months</button>
                    <button @click="timeframe = '30days'" :class="timeframe === '30days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">30 days</button>
                    <button @click="timeframe = '7days'" :class="timeframe === '7days' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">7 days</button>
                    <button @click="timeframe = '24hours'" :class="timeframe === '24hours' ? 'shadow-xs text-gray-900 dark:text-white bg-white dark:bg-gray-800 font-medium' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'" class="text-xs rounded-md px-2.5 py-1.5 font-normal transition-all">24 hours</button>
                </div>
            </div>
            <div id="ticketTrendChart" class="h-64"></div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Breakdown</h3>
                <p class="text-xs text-gray-400">Distribution across lifecycle</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="ticketStatusChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>

                @if(isset($chartStatusData['labels']) && count($chartStatusData['labels']) > 0)
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($chartStatusData['labels'] as $index => $label)
                            @php
                                $count = $chartStatusData['data'][$index] ?? 0;
                                $color = $chartStatusData['colors'][$index] ?? '#465fff';
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Conversion Funnel Card (TailAdmin SaaS Funnel Style) -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-gray-800">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="filter" class="w-5 h-5 text-brand-500"></i>
                    Ticket Resolution Conversion Funnel
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Stage-by-stage progression from intake to resolution</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-md bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    Conversion Rate: {{ $funnelChart['rates'][3] ?? 0 }}%
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8">
                <div id="ticketFunnelChart" class="h-64"></div>
            </div>
            <div class="lg:col-span-4 space-y-3 border-t lg:border-t-0 lg:border-l border-gray-100 pt-4 lg:pt-0 lg:pl-6 dark:border-gray-800">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Funnel Stages Breakdown</h4>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-blue-50/50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-900/30">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">1. Received</span>
                        </div>
                        <span class="font-bold text-xs text-gray-900 dark:text-white">{{ number_format($funnelChart['data'][0] ?? 0) }} <span class="text-gray-400 font-normal">({{ $funnelChart['rates'][0] ?? 100 }}%)</span></span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-cyan-50/50 dark:bg-cyan-500/10 border border-cyan-100 dark:border-cyan-900/30">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-cyan-500"></span>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">2. Assigned</span>
                        </div>
                        <span class="font-bold text-xs text-gray-900 dark:text-white">{{ number_format($funnelChart['data'][1] ?? 0) }} <span class="text-gray-400 font-normal">({{ $funnelChart['rates'][1] ?? 0 }}%)</span></span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-amber-50/50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-900/30">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">3. In Progress</span>
                        </div>
                        <span class="font-bold text-xs text-gray-900 dark:text-white">{{ number_format($funnelChart['data'][2] ?? 0) }} <span class="text-gray-400 font-normal">({{ $funnelChart['rates'][2] ?? 0 }}%)</span></span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-900/30">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-200">4. Resolved & Closed</span>
                        </div>
                        <span class="font-bold text-xs text-gray-900 dark:text-white">{{ number_format($funnelChart['data'][3] ?? 0) }} <span class="text-gray-400 font-normal">({{ $funnelChart['rates'][3] ?? 0 }}%)</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Category & Priority Charts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Categories</h3>
                <p class="text-xs text-gray-400">By issue category</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="ticketCategoryChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>
                @if(isset($categoryChart['labels']) && count($categoryChart['labels']) > 0)
                    @php $catColors = ['#3b82f6', '#22c55e', '#f97316', '#06b6d4', '#8b5cf6']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($categoryChart['labels'] as $index => $label)
                            @php
                                $count = $categoryChart['data'][$index] ?? 0;
                                $color = $catColors[$index % count($catColors)];
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Priority Distribution</h3>
                <p class="text-xs text-gray-400">Urgency breakdown</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="ticketPriorityChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>
                @if(isset($priorityChart['labels']) && count($priorityChart['labels']) > 0)
                    @php $prioColors = ['#ef4444', '#f97316', '#3b82f6', '#22c55e', '#8b5cf6']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($priorityChart['labels'] as $index => $label)
                            @php
                                $count = $priorityChart['data'][$index] ?? 0;
                                $color = $prioColors[$index % count($prioColors)];
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Classification</h3>
                <p class="text-xs text-gray-400">Incident vs Request</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="ticketTypeChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>
                @if(isset($typeChart['labels']) && count($typeChart['labels']) > 0)
                    @php $typeColors = ['#06b6d4', '#8b5cf6', '#3b82f6', '#f97316', '#22c55e']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($typeChart['labels'] as $index => $label)
                            @php
                                $count = $typeChart['data'][$index] ?? 0;
                                $color = $typeColors[$index % count($typeColors)];
                            @endphp
                            <div class="flex items-center gap-1.5 shrink-0">
                                <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">({{ number_format($count) }})</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 5. Ticket Activity Data Table -->
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <x-common.dashboard-table
            title="Recent Support Tickets"
            description="Live feed of incoming tickets"
            :headers="['ID', 'Requester', 'Category', 'Linked Asset', 'Priority', 'Status', 'Assigned To', 'Created']"
            :footerLink="route('tickets.index')"
            footerText="View All Tickets"
        >
                @forelse($recentTickets as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">#{{ $ticket->request_id }}</a>
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $ticket->requester_name ?? $ticket->requesterUser?->name }}
                                </span>
                                @php
                                    $reqDept = $ticket->requesterDepartment?->name ?? $ticket->requesterUser?->department?->name;
                                @endphp
                                @if($reqDept)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                        {{ $reqDept }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            <x-ui.badge color="primary" variant="light" size="sm">
                                {{ $ticket->ticketCategory?->name ?? $ticket->category?->name ?? '-' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-2.5">
                            @if($ticket->asset)
                                <a href="{{ route('assets.show', $ticket->asset) }}" class="font-semibold text-xs text-gray-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-400 transition">
                                    {{ $ticket->asset->asset_tag ?? $ticket->asset->name }}
                                </a>
                                @php
                                    $brandName = optional(optional($ticket->asset->product)->brand)->name;
                                    $productName = optional($ticket->asset->product)->name;
                                @endphp
                                @if($brandName || $productName)
                                    <span class="block text-[11px] text-gray-400 dark:text-gray-500 truncate max-w-[160px]">
                                        {{ $brandName }}{{ ($brandName && $productName) ? ' • ' : '' }}{{ $productName }}
                                    </span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                            @endif
                        </td>
                    <td class="px-4 py-2.5">
                        <x-ui.badge 
                            size="sm" 
                            :color="$ticket->priority?->color ?? 'secondary'"
                            :featherIcon="$ticket->priority?->icon ?: 'alert-circle'"
                        >
                            {{ $ticket->priority?->name ?? '-' }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-2.5">
                        <x-ui.badge 
                            size="sm" 
                            :color="$ticket->status?->color ?? 'primary'"
                            :featherIcon="$ticket->status?->icon ?: 'disc'"
                        >
                            {{ $ticket->status?->name ?? '-' }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ $ticket->assignedTo?->name ?? 'Unassigned' }}
                            </span>
                            @if($ticket->assignedTo?->department)
                                <span class="text-[10px] text-gray-400 uppercase tracking-wider">
                                    {{ $ticket->assignedTo->department->name }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                        {{ $ticket->created_at->format('M d, H:i') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-6 text-center text-xs text-gray-400">No support tickets found</td></tr>
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
        funnelChart: @json($funnelChart),
    };
</script>
@vite('resources/js/ticket-charts.js')
@endpush
