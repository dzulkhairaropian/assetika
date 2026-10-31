@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Title Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">Facilities & Physical Assets</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Live monitoring of physical assets, vehicle fleet, room reservations, and facility maintenance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('ga-assets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-brand-600 active:scale-[0.98] transition-colors duration-150">
                <i data-lucide="plus" class="w-4 h-4"></i> Add GA Asset
            </a>
            <a href="{{ route('ga-assets.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 hover:border-gray-300 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-900 transition-colors duration-150">
                <i data-lucide="briefcase" class="w-4 h-4"></i> Inventory List
            </a>
        </div>
    </div>

    <!-- Bento Primary Row: Hero + High Priority Operational KPIs -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
        <!-- Hero Card (Spans 2 columns on lg) -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-xs transition-colors duration-200 hover:border-brand-500/30 dark:border-gray-800 dark:bg-gray-900 lg:col-span-2 flex flex-col justify-between">
            <div>
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">GA Physical Fleet</span>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Facilities & Asset Inventory</h2>
                </div>

                @php
                    $gaDeploymentRate = $totalAssets > 0 ? (int) round(($activeAssignments / $totalAssets) * 100) : 0;
                @endphp

                <div class="mt-4 flex flex-wrap items-baseline gap-3">
                    <span class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl tabular-nums">{{ number_format($totalAssets) }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">total physical items</span>
                    @if($totalAssetValue > 0)
                        <span class="ml-auto text-xs font-medium text-gray-500 dark:text-gray-400">
                            Capital Value: <strong class="text-gray-900 dark:text-white font-mono tabular-nums">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</strong>
                        </span>
                    @endif
                </div>

                <!-- Multi-segment Distribution Bar -->
                @php
                    $gaAssignedPct = $totalAssets > 0 ? round(($activeAssignments / $totalAssets) * 100) : 0;
                    $gaMaintPct = $totalAssets > 0 ? round(($pendingMaintenance / $totalAssets) * 100) : 0;
                    $gaAvailPct = max(0, 100 - $gaAssignedPct - $gaMaintPct);
                @endphp
                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Assignment Rate</span>
                        <span class="font-semibold text-brand-600 dark:text-brand-400 tabular-nums">{{ $gaDeploymentRate }}% Assigned</span>
                    </div>
                    <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div style="width: {{ $gaAssignedPct }}%" class="bg-brand-500 transition-all duration-300" title="Assigned: {{ $activeAssignments }} ({{ $gaAssignedPct }}%)"></div>
                        <div style="width: {{ $gaAvailPct }}%" class="bg-emerald-500 transition-all duration-300" title="Available: {{ max(0, $totalAssets - $activeAssignments - $pendingMaintenance) }} ({{ $gaAvailPct }}%)"></div>
                        <div style="width: {{ $gaMaintPct }}%" class="bg-amber-500 transition-all duration-300" title="Maintenance: {{ $pendingMaintenance }} ({{ $gaMaintPct }}%)"></div>
                    </div>
                </div>
            </div>

            <!-- Segmented Indicators Legend -->
            <div class="mt-4 grid grid-cols-3 gap-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-[11px]">
                <a href="{{ route('ga-assets.index') }}" class="group flex items-center gap-1.5 hover:text-brand-600 transition-colors">
                    <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Assigned</span>
                    <strong class="text-gray-900 dark:text-white tabular-nums">{{ $activeAssignments }}</strong>
                </a>
                <a href="{{ route('ga-assets.index') }}" class="group flex items-center gap-1.5 hover:text-emerald-600 transition-colors">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Available</span>
                    <strong class="text-gray-900 dark:text-white tabular-nums">{{ max(0, $totalAssets - $activeAssignments - $pendingMaintenance) }}</strong>
                </a>
                <a href="{{ route('ga-assets.index') }}" class="group flex items-center gap-1.5 hover:text-amber-600 transition-colors">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    <span class="text-gray-500 dark:text-gray-400">Maintenance</span>
                    <strong class="text-gray-900 dark:text-white tabular-nums">{{ $pendingMaintenance }}</strong>
                </a>
            </div>
        </div>

        <x-common.small-box
            title="Active Assignments"
            icon="user-check"
            :value="number_format($activeAssignments)"
            description="Assets currently checked out"
            color="info"
            :url="route('ga-assets.index')"
        />

        <x-common.small-box
            title="Pending Maintenance"
            icon="wrench"
            :value="number_format($pendingMaintenance)"
            description="Scheduled repairs & servicing"
            color="warning"
            :url="route('ga-assets.index')"
        />
    </div>

    <!-- Secondary Operational KPI Row (4-column symmetry) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-common.small-box
            title="Vehicles Fleet"
            icon="truck"
            :value="number_format($totalVehicles)"
            description="Cars, vans & delivery bikes"
            color="brand"
            :url="route('ga-assets.index')"
        />

        <x-common.small-box
            title="Tax Due Alerts"
            icon="alert-triangle"
            :value="number_format($upcomingVehicleTaxes)"
            description="Vehicle taxes expiring in 30 days"
            color="danger"
            :url="route('ga-assets.index')"
        />

        <x-common.small-box
            title="Active Reservations"
            icon="calendar"
            :value="number_format($activeReservations)"
            description="Room & vehicle schedules"
            color="info"
            :url="route('ga-assets.index')"
        />

        <x-common.small-box
            title="Maintenance Spend"
            icon="activity"
            value="Rp {{ number_format($monthlyMaintenanceSpend, 0, ',', '.') }}"
            description="Total repairs expenditure"
            color="warning"
            :url="route('ga-assets.index')"
        />
    </div>

    <!-- 3. GA Charts Grid (3 Donut Charts) -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Status Breakdown</h3>
                <p class="text-xs text-gray-400">GA Asset availability</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="gaStatusChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>
                @if(isset($gaStatusChart['labels']) && count($gaStatusChart['labels']) > 0)
                    @php $gaColors = ['#465fff', '#12b76a', '#f79009', '#ef4444', '#8b5cf6']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($gaStatusChart['labels'] as $index => $label)
                            @php
                                $count = $gaStatusChart['data'][$index] ?? 0;
                                $color = $gaColors[$index % count($gaColors)];
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
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Assets by Category</h3>
                <p class="text-xs text-gray-400">Facilities vs Furniture</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="gaCategoryChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>
                @if(isset($gaCategoryChart['labels']) && count($gaCategoryChart['labels']) > 0)
                    @php $gaCatColors = ['#3b82f6', '#22c55e', '#f97316', '#06b6d4', '#8b5cf6']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($gaCategoryChart['labels'] as $index => $label)
                            @php
                                $count = $gaCategoryChart['data'][$index] ?? 0;
                                $color = $gaCatColors[$index % count($gaCatColors)];
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

        <div class="rounded-xl border border-gray-200/80 bg-white shadow-sm transition-all duration-200 ease-out hover:border-brand-500/30 hover:shadow-md dark:border-gray-800/80 dark:bg-gray-900 md:col-span-2 xl:col-span-1 flex flex-col justify-between">
            <div class="p-5 pb-2 border-b border-gray-100 dark:border-gray-800/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Asset Condition</h3>
                <p class="text-xs text-gray-400">Physical rating</p>
            </div>
            <div class="flex flex-col gap-4 p-5">
                <div class="flex justify-center">
                    <div id="gaConditionChart" class="h-48 w-full flex items-center justify-center"></div>
                </div>
                @if(isset($gaConditionChart['labels']) && count($gaConditionChart['labels']) > 0)
                    @php $gaCondColors = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']; @endphp
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 border-t border-gray-100 pt-3 dark:border-gray-800 text-xs">
                        @foreach($gaConditionChart['labels'] as $index => $label)
                            @php
                                $count = $gaConditionChart['data'][$index] ?? 0;
                                $color = $gaCondColors[$index % count($gaCondColors)];
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

    <!-- 4. Spend & Maintenance Timeline Charts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Investment Spend Timeline</h3>
                <p class="text-xs text-gray-400">Monthly expenditure</p>
            </div>
            <div id="gaSpendTimelineChart" class="h-64"></div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Maintenance Cost by Category</h3>
                <p class="text-xs text-gray-400">Repair expenditure</p>
            </div>
            <div id="gaMaintCategoryChart" class="h-64"></div>
        </div>
    </div>

    <!-- 5. Data Tables Grid (2x2) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
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
                            <span class="block text-[11px] text-gray-400">{{ $assignment->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300">
                            {{ $assignment->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap tabular-nums">{{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5">
                            @if($assignment->returned_at)
                                <x-ui.badge color="success" variant="light" size="sm">Returned</x-ui.badge>
                            @else
                                <x-ui.badge color="primary" variant="light" size="sm">Active</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center">
                            <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                                <i data-lucide="user-check" class="h-4 w-4"></i>
                            </div>
                            <p class="mt-2 text-xs font-medium text-gray-600 dark:text-gray-400">No recent assignments found</p>
                            <p class="text-[11px] text-gray-400">Physical equipment checkouts will appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900">
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
                            <span class="block text-[11px] text-gray-400 font-mono">{{ $log->asset->asset_code ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400 whitespace-nowrap tabular-nums">{{ $log->scheduled_date ? $log->scheduled_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white whitespace-nowrap font-mono tabular-nums">Rp {{ number_format($log->cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-2.5">
                            @if($log->completed_date)
                                <x-ui.badge color="success" variant="light" size="sm">Completed</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" variant="light" size="sm">Scheduled</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center">
                            <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <i data-lucide="wrench" class="h-4 w-4"></i>
                            </div>
                            <p class="mt-2 text-xs font-medium text-gray-800 dark:text-gray-200">No maintenance logs found</p>
                            <p class="text-[11px] text-gray-400">Scheduled facility and vehicle servicing will be logged here.</p>
                        </td>
                    </tr>
                @endforelse
            </x-common.dashboard-table>
        </div>
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

        // Custom plugin to render center text in Doughnut charts (shadcn style)
        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw(chart) {
                const { ctx, chartArea } = chart;
                const centerConfig = chart.config.options.plugins?.centerText;
                if (!centerConfig || centerConfig.value === undefined) return;

                const { left, top, width, height } = chartArea;
                const centerX = left + width / 2;
                const centerY = top + height / 2;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                ctx.font = 'bold 22px Outfit, sans-serif';
                ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a';
                ctx.fillText(Number(centerConfig.value).toLocaleString(), centerX, centerY - 8);

                ctx.font = '500 12px Outfit, sans-serif';
                ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';
                ctx.fillText(centerConfig.label || 'Total', centerX, centerY + 14);

                ctx.restore();
            }
        };

        if (typeof Chart !== 'undefined') {
            Chart.register(centerTextPlugin);
        }

        const createDoughnutConfig = (labels, data, colors, centerLabel = 'Total') => ({
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                    hoverOffset: 6,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                onHover: (event, activeElements, chart) => {
                    chart.draw();
                },
                plugins: {
                    centerText: {
                        label: centerLabel,
                        value: data.reduce((a, b) => Number(a) + Number(b), 0)
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                        padding: { top: 8, bottom: 8, left: 12, right: 12 },
                        cornerRadius: 8,
                        boxPadding: 4
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

        // 4. Investment Spend Timeline Line Chart (TailAdmin Pro Statistics Line Style)
        const purchaseCanvas = mountCanvas("#gaSpendTimelineChart");
        if (purchaseCanvas && @json($purchaseChart['data'])?.length) {
            new Chart(purchaseCanvas, {
                type: 'line',
                data: {
                    labels: @json($purchaseChart['labels']),
                    datasets: [{
                        label: 'Acquisition Spend',
                        data: @json($purchaseChart['data']),
                        fill: true,
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const { ctx, chartArea } = chart;
                            if (!chartArea) return 'rgba(18, 183, 106, 0.1)';
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            const isDark = document.documentElement.classList.contains('dark');
                            gradient.addColorStop(0, isDark ? 'rgba(18, 183, 106, 0.35)' : 'rgba(18, 183, 106, 0.25)');
                            gradient.addColorStop(1, 'rgba(18, 183, 106, 0.0)');
                            return gradient;
                        },
                        borderColor: '#12b76a',
                        borderWidth: 2,
                        tension: 0,
                        pointBackgroundColor: '#12b76a',
                        pointBorderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#12b76a',
                        pointHoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                            bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                            padding: { top: 8, bottom: 8, left: 12, right: 12 },
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: (context) => ' Spend: ' + formatRupiah(context.raw)
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8' }
                        },
                        y: {
                            border: { display: false },
                            grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(148, 163, 184, 0.1)' : 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                            ticks: {
                                font: { family: 'Outfit, sans-serif', size: 11 },
                                color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8',
                                callback: (val) => 'Rp ' + (val >= 1000000 ? (val / 1000000) + 'M' : val)
                            }
                        }
                    }
                }
            });
        }

        // 5. Maintenance Cost by Category Bar Chart (TailAdmin Pro ChartSix Style)
        const maintCatCanvas = mountCanvas("#gaMaintCategoryChart");
        if (maintCatCanvas && @json($maintCategoryChart['data'])?.length) {
            new Chart(maintCatCanvas, {
                type: 'bar',
                data: {
                    labels: @json($maintCategoryChart['labels']),
                    datasets: [{
                        label: 'Maintenance Cost',
                        data: @json($maintCategoryChart['data']),
                        backgroundColor: '#465fff',
                        borderRadius: { topLeft: 8, topRight: 8, bottomLeft: 0, bottomRight: 0 },
                        borderSkipped: false,
                        barThickness: 28,
                        maxBarThickness: 36
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                            bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                            padding: { top: 8, bottom: 8, left: 12, right: 12 },
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: (context) => ' Cost: ' + formatRupiah(context.raw)
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8' }
                        },
                        y: {
                            border: { display: false },
                            grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(148, 163, 184, 0.1)' : 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                            ticks: {
                                font: { family: 'Outfit, sans-serif', size: 11 },
                                color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8',
                                callback: (val) => 'Rp ' + (val >= 1000000 ? (val / 1000000) + 'M' : val)
                            }
                        }
                    }
                }
            });
        }

        if (window.createIcons && window.lucideIcons) {
            window.createIcons({ icons: window.lucideIcons });
        }
    });
</script>
@endpush
