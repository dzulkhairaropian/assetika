@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 min-w-0">

        <!-- Dashboard Header -->
        <div class="col-span-12 min-w-0">
            <x-common.page-breadcrumb pageTitle="General Affair Dashboard" />
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Hi, {{ $userName }}! 👋
                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $randomGreeting }}</span>
                    </h2>
                </div>
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 transition">
                        <i data-feather="plus" class="w-3.5 h-3.5"></i> Add GA Asset
                    </a>
                    <a href="{{ route('ga-assets.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="briefcase" class="w-3.5 h-3.5"></i> GA Inventory
                    </a>
                    <a href="{{ route('reservations.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                        <i data-feather="calendar" class="w-3.5 h-3.5"></i> Reservations
                    </a>
                </div>
            </div>
        </div>

        <!-- 1. Metrics Row 1 (Core GA Metrics) -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <x-common.stat-card
                title="Total GA Assets"
                icon="hard-drive"
                :value="number_format($totalAssets)"
                description="Total physical GA assets in inventory{{ $isTotalAssetsDummy ? ' (Dummy Value)' : '' }}"
            />

            <x-common.stat-card
                title="Total Capital Value"
                icon="dollar-sign"
                value="Rp {{ number_format($totalAssetValue, 0, ',', '.') }}"
                description="Capitalized investment value for GA{{ $isTotalAssetValueDummy ? ' (Dummy Value)' : '' }}"
            />

            <x-common.stat-card
                title="Active Assignments"
                icon="user-check"
                :value="number_format($activeAssignments)"
                description="Assets currently checked out or in use{{ $isActiveAssignmentsDummy ? ' (Dummy Value)' : '' }}"
            />

            <x-common.stat-card
                title="Pending Maintenance"
                icon="wrench"
                :value="number_format($pendingMaintenance)"
                description="Scheduled repairs and maintenance tasks{{ $isPendingMaintenanceDummy ? ' (Dummy Value)' : '' }}"
            />
        </div>

        <!-- 2. Metrics Row 2 (Fleet & Facilities Metrics - Dummy Placeholders) -->
        <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 min-w-0">
            <x-common.stat-card
                title="Vehicles Fleet"
                icon="truck"
                :value="number_format($totalVehicles)"
                description="Cars, vans & delivery bikes (Dummy Value)"
            />

            <x-common.stat-card
                title="Tax Due Alerts"
                icon="alert-triangle"
                :value="number_format($upcomingVehicleTaxes)"
                :trend="1"
                trendColor="warning"
                description="Vehicle taxes expiring in 30 days (Dummy Value)"
            />

            <x-common.stat-card
                title="Active Reservations"
                icon="calendar"
                :value="number_format($activeReservations)"
                description="Room & vehicle booking schedules (Dummy Value)"
            />

            <x-common.stat-card
                title="Maintenance Spend"
                icon="activity"
                value="Rp {{ number_format($monthlyMaintenanceSpend, 0, ',', '.') }}"
                description="Total maintenance cost this month (Dummy Value)"
            />
        </div>

        <!-- 3. Charts Row 1: 3 Donut Charts per Row (Status, Category, Condition) -->
        <div class="col-span-12 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 min-w-0">
            
            <!-- 1. Asset Status Distribution -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Status Distribution</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Breakdown by status{{ $isStatusChartDummy ? ' (Dummy Value)' : '' }}</p>
                    </div>
                    <!-- Status Filter Dropdowns -->
                    <div class="flex items-center gap-1 shrink-0">
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $monthList[$statusMonth] ?? 'All' }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-28" style="display: none;">
                                @foreach($monthList as $mVal => $mLabel)
                                    <a href="{{ request()->fullUrlWithQuery(['status_month' => $mVal]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $statusMonth === (string) $mVal ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $mLabel }}</a>
                                @endforeach
                            </div>
                        </div>
                        <div class="relative" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                            <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center gap-0.5 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                <span>{{ $statusYear === 'all' ? 'All' : $statusYear }}</span>
                                <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                            </button>
                            <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1 rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 max-h-56 overflow-y-auto w-24" style="display: none;">
                                <a href="{{ request()->fullUrlWithQuery(['status_year' => 'all']) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ $statusYear === 'all' ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">All Years</a>
                                @foreach($purchaseYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['status_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $statusYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div id="gaStatusChart" class="h-64 flex items-center justify-center"></div>
            </div>

            <!-- 2. Assets by Category -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Assets by Category</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Grouped by category{{ $isCategoryChartDummy ? ' (Dummy Value)' : '' }}</p>
                    </div>
                    <!-- Category Filter Dropdowns -->
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
                                @foreach($purchaseYears as $y)
                                    <a href="{{ request()->fullUrlWithQuery(['category_year' => $y]) }}" class="px-2 py-1 text-[11px] rounded-md transition {{ (string) $categoryYear === (string) $y ? 'bg-brand-50 text-brand-600 font-bold dark:bg-brand-500/15 dark:text-brand-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">{{ $y }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div id="gaCategoryChart" class="h-64 flex items-center justify-center"></div>
            </div>

            <!-- 3. Asset Condition Breakdown (Dummy Value) -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden md:col-span-2 xl:col-span-1">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Asset Condition</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Physical health rating (Dummy Value)</p>
                </div>
                <div id="gaConditionChart" class="h-64 flex items-center justify-center"></div>
            </div>
        </div>

        <!-- 4. Charts Row 2: Spend Timeline & Maintenance Cost -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <!-- Spend Timeline Line Chart -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="flex items-center justify-between mb-2 min-w-0">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Investment Spend Timeline</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Monthly expenditure for {{ $selectedYear }}{{ $isPurchaseChartDummy ? ' (Dummy Value)' : '' }}</p>
                    </div>
                    <!-- Year Dropdown -->
                    <div class="relative shrink-0" x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                        <button @click="dropdownOpen = !dropdownOpen" class="inline-flex items-center justify-between gap-1 rounded-md border border-gray-200 bg-white px-2 py-1 text-[11px] font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                            <i data-feather="calendar" class="w-3 h-3 text-brand-500"></i>
                            <span>Year: {{ $selectedYear }}</span>
                            <i data-feather="chevron-down" class="w-3 h-3" :class="{ 'rotate-180': dropdownOpen }"></i>
                        </button>
                        <div x-show="dropdownOpen" x-transition class="absolute right-0 mt-1.5 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-[9999] flex flex-col gap-0.5 w-max max-h-56 overflow-y-auto" style="display: none;">
                            @foreach($purchaseYears as $year)
                                <a href="{{ request()->fullUrlWithQuery(['purchase_year' => $year]) }}"
                                   class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ (string) $selectedYear === (string) $year ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 font-bold' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                                    {{ $year }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div id="gaSpendTimelineChart" class="h-64"></div>
            </div>

            <!-- Maintenance Cost by Category Bar Chart (Dummy Value) -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 min-w-0 overflow-hidden">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Maintenance Cost by Category</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Expenditure across repair types (Dummy Value)</p>
                </div>
                <div id="gaMaintCategoryChart" class="h-64"></div>
            </div>
        </div>

        <!-- 5. Activity & Fleet Tables Row (4 Tables in 2x2 Grid) -->
        <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-2 min-w-0">
            <!-- Table 1: Recent Assignments -->
            <x-common.dashboard-table
                title="Recent Assignments"
                description="Latest GA asset assignments to staff"
                :headers="['Asset', 'Assigned To', 'Date', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Inventory"
            >
                @forelse($recentAssignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $assignment->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $assignment->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300">
                            {{ $assignment->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5">
                            @if($assignment->returned_at)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">Returned</span>
                            @else
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No recent assignments found</td></tr>
                @endforelse
            </x-common.dashboard-table>

            <!-- Table 2: Recent Maintenance -->
            <x-common.dashboard-table
                title="Recent Maintenance Logs"
                description="Latest scheduled repairs & servicing"
                :headers="['Asset', 'Scheduled', 'Cost', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="View GA Maintenance"
            >
                @forelse($recentMaintenance as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $log->asset->name ?? 'Unknown' }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white whitespace-nowrap">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5">
                            @if($log->completed_date)
                                <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">Completed</span>
                            @else
                                <span class="rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-medium text-orange-700 dark:bg-orange-500/10 dark:text-orange-400">Scheduled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-xs text-gray-400">No maintenance logs found</td></tr>
                @endforelse
            </x-common.dashboard-table>

            <!-- Table 3: Vehicle Fleet & Tax Expiry Tracker (Dummy Value) -->
            <x-common.dashboard-table
                title="Vehicle Fleet & Tax Expiry"
                description="Tax renewal & operational vehicle status (Dummy Value)"
                :headers="['Vehicle Model', 'License Plate', 'Tax Expiry', 'Status']"
                :footerLink="route('ga-assets.index')"
                footerText="Manage Fleet"
            >
                @foreach($vehicleFleet as $vehicle)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $vehicle['name'] }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $vehicle['type'] }}</span>
                        </td>
                        <td class="px-4 py-2.5 font-mono text-xs font-semibold text-gray-800 dark:text-gray-200">
                            {{ $vehicle['plate'] }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                            {{ $vehicle['tax_expiry'] }}
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="rounded-full bg-{{ $vehicle['status_color'] }}-50 px-2 py-0.5 text-[11px] font-medium text-{{ $vehicle['status_color'] }}-700 dark:bg-{{ $vehicle['status_color'] }}-500/10 dark:text-{{ $vehicle['status_color'] }}-400">
                                {{ $vehicle['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-common.dashboard-table>

            <!-- Table 4: Facility & Vehicle Reservations (Dummy Value) -->
            <x-common.dashboard-table
                title="Active Facility Reservations"
                description="Room & vehicle booking schedules (Dummy Value)"
                :headers="['Facility / Vehicle', 'Reserved By', 'Time Schedule', 'Status']"
                :footerLink="route('reservations.index')"
                footerText="View All Reservations"
            >
                @foreach($facilityReservations as $res)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                        <td class="px-4 py-2.5">
                            <span class="block font-medium text-gray-900 dark:text-white">{{ $res['facility'] }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-gray-500">{{ $res['purpose'] }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 font-medium">
                            {{ $res['reserved_by'] }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $res['date'] }}
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                {{ $res['status'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-common.dashboard-table>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mountCanvas = (selector) => {
            const container = document.querySelector(selector);
            if (!container) return null;
            container.innerHTML = '<canvas></canvas>';
            return container.querySelector('canvas');
        };

        const chartColors = {
            blue: '#3b82f6',
            green: '#22c55e',
            orange: '#f97316',
            purple: '#8b5cf6',
            cyan: '#06b6d4',
            red: '#ef4444',
        };

        const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');

        const createDoughnutConfig = (labels, data, colors, centerLabel = 'Total') => ({
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    centerText: {
                        label: centerLabel,
                        value: data.reduce((a, b) => Number(a) + Number(b), 0)
                    },
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, pointStyle: 'circle', font: { family: 'Outfit, sans-serif', size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                        padding: { top: 8, bottom: 8, left: 12, right: 12 },
                        cornerRadius: 8
                    }
                }
            }
        });

        // 1. Status Donut Chart
        const statusCanvas = mountCanvas("#gaStatusChart");
        if (statusCanvas && @json($statusChart['data'])?.length) {
            new Chart(statusCanvas, createDoughnutConfig(
                @json($statusChart['labels']),
                @json($statusChart['data']),
                @json($statusChart['colors']) || [chartColors.green, chartColors.blue, chartColors.orange, chartColors.red],
                'Status'
            ));
        }

        // 2. Category Donut Chart
        const catCanvas = mountCanvas("#gaCategoryChart");
        if (catCanvas && @json($categoryChart['data'])?.length) {
            new Chart(catCanvas, createDoughnutConfig(
                @json($categoryChart['labels']),
                @json($categoryChart['data']),
                [chartColors.blue, chartColors.purple, chartColors.cyan, chartColors.green, chartColors.orange],
                'Categories'
            ));
        }

        // 3. Condition Donut Chart
        const condCanvas = mountCanvas("#gaConditionChart");
        if (condCanvas && @json($conditionChart['data'])?.length) {
            new Chart(condCanvas, createDoughnutConfig(
                @json($conditionChart['labels']),
                @json($conditionChart['data']),
                @json($conditionChart['colors']),
                'Condition'
            ));
        }

        // 4. Investment Spend Timeline Line Chart
        const purchaseCanvas = mountCanvas("#gaSpendTimelineChart");
        if (purchaseCanvas && @json($purchaseChart['data'])?.length) {
            new Chart(purchaseCanvas, {
                type: 'line',
                data: {
                    labels: @json($purchaseChart['labels']),
                    datasets: [{
                        label: 'Acquisition Spend',
                        data: @json($purchaseChart['data']),
                        fill: false,
                        borderColor: chartColors.green,
                        borderWidth: 2,
                        tension: 0,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: chartColors.green,
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => ' Spend: ' + formatRupiah(context.raw)
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                        y: {
                            border: { display: false },
                            grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                            ticks: {
                                font: { family: 'Outfit, sans-serif', size: 11 },
                                callback: (val) => 'Rp ' + (val >= 1000000 ? (val / 1000000) + 'M' : val)
                            }
                        }
                    }
                }
            });
        }

        // 5. Maintenance Cost by Category Bar Chart
        const maintCatCanvas = mountCanvas("#gaMaintCategoryChart");
        if (maintCatCanvas && @json($maintCategoryChart['data'])?.length) {
            new Chart(maintCatCanvas, {
                type: 'bar',
                data: {
                    labels: @json($maintCategoryChart['labels']),
                    datasets: [{
                        label: 'Maintenance Cost',
                        data: @json($maintCategoryChart['data']),
                        backgroundColor: chartColors.blue,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            cornerRadius: 8,
                            callbacks: {
                                label: (context) => ' Cost: ' + formatRupiah(context.raw)
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                        y: {
                            border: { display: false },
                            grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                            ticks: {
                                font: { family: 'Outfit, sans-serif', size: 11 },
                                callback: (val) => 'Rp ' + (val >= 1000000 ? (val / 1000000) + 'M' : val)
                            }
                        }
                    }
                }
            });
        }

        if (window.feather) {
            window.feather.replace();
        }
    });
</script>
@endpush
