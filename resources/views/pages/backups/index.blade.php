@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Backup Management" />

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif

        @if(session('error'))
            <x-ui.alert variant="danger" message="{{ session('error') }}" />
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Panel: Backup Schedules Settings -->
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-5 flex items-center gap-2">
                        <i data-feather="clock" class="w-5 h-5 text-brand-500"></i>
                        Backup Schedules
                    </h3>

                    <form action="{{ route('backups.settings') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Database Backup Schedule -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Database Backup
                                </label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="db_backup_enabled" value="1" class="sr-only peer" {{ !empty($settings['db_backup_enabled']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-brand-500/20 dark:peer-focus:ring-brand-500/30 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Frequency</label>
                                    <select name="db_backup_frequency" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                        <option value="daily" {{ ($settings['db_backup_frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ ($settings['db_backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ ($settings['db_backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Run Time</label>
                                    <input type="time" name="db_backup_time" value="{{ $settings['db_backup_time'] ?? '02:00' }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-800">

                        <!-- Full Backup Schedule -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Full Backup (App + DB)
                                </label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="full_backup_enabled" value="1" class="sr-only peer" {{ !empty($settings['full_backup_enabled']) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer dark:bg-gray-700 peer-focus:ring-4 peer-focus:ring-brand-500/20 dark:peer-focus:ring-brand-500/30 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Frequency</label>
                                    <select name="full_backup_frequency" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat">
                                        <option value="daily" {{ ($settings['full_backup_frequency'] ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ ($settings['full_backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ ($settings['full_backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Run Time</label>
                                    <input type="time" name="full_backup_time" value="{{ $settings['full_backup_time'] ?? '03:00' }}" class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-xs outline-none focus:border-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                            <i data-feather="save" class="w-4 h-4"></i>
                            Save Schedule
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Panel: Manual Trigger & Backup History -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Manual Actions Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        <i data-feather="play" class="w-5 h-5 text-brand-500"></i>
                        Manual Backup
                    </h3>
                    <p class="text-sm text-gray-500 mb-5">
                        Trigger an on-demand backup. Backing up the whole application may take a minute or two depending on files size.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <form action="{{ route('backups.create') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="type" value="db">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                                <i data-feather="database" class="w-4 h-4 text-brand-500"></i>
                                Backup Database Only
                            </button>
                        </form>

                        <form action="{{ route('backups.create') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="type" value="full">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                                <i data-feather="archive" class="w-4 h-4"></i>
                                Backup Application & Database
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Backup History List -->
                <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
                            <i data-feather="rotate-cw" class="w-5 h-5 text-brand-500"></i>
                            Backup History
                        </h3>
                        <span class="text-xs text-gray-500">{{ count($backups) }} files found</span>
                    </div>

                    <div class="max-w-full overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                                <tr>
                                    <th class="px-5 py-3 font-medium text-gray-500 text-xs">File Name</th>
                                    <th class="px-5 py-3 font-medium text-gray-500 text-xs">Size</th>
                                    <th class="px-5 py-3 font-medium text-gray-500 text-xs">Created At</th>
                                    <th class="px-5 py-3 font-medium text-gray-500 text-xs text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                                @forelse($backups as $backup)
                                    <tr>
                                        <td class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ $backup['name'] }}
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $backup['size'] }}
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $backup['date'] }}
                                        </td>
                                        <td class="px-5 py-4 text-sm text-right">
                                            <div class="flex items-center justify-end gap-3 text-gray-500">
                                                <a href="{{ route('backups.download', $backup['name']) }}" class="hover:text-brand-500 transition-colors" title="Download">
                                                    <i data-feather="download" class="w-5 h-5"></i>
                                                </a>
                                                <form action="{{ route('backups.destroy', $backup['name']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backup file?')" class="inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="hover:text-error-500 transition-colors" title="Delete">
                                                        <i data-feather="trash-2" class="w-5 h-5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center space-y-2">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400">
                                                    <i data-feather="archive" class="w-6 h-6"></i>
                                                </div>
                                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">No Backups Found</h4>
                                                <p class="text-xs text-gray-500">Trigger a manual backup or wait for schedule.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
