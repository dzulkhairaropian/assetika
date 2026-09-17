@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="System Settings" />

        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
            Configure application branding, system name, company details, logos, and favicon.
        </p>

        @if(session('success'))
            <x-ui.alert variant="success" message="{{ session('success') }}" />
        @endif
        @if(session('error'))
            <x-ui.alert variant="error" message="{{ session('error') }}" />
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[minmax(0,1.75fr)_380px]">
            @csrf

            <div class="space-y-6">
                <!-- System & Organization Branding Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="settings" class="w-5 h-5 text-brand-500"></i>
                            General Application Settings
                        </h3>

                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Application Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'Assetika') }}" required
                                    placeholder="e.g. Assetika ITSM"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('app_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Company / Organization Name
                                </label>
                                <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                                    placeholder="e.g. PT Diastika Biotekindo Tbk"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('company_name') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Application Slogan / Tagline
                                </label>
                                <input type="text" name="app_slogan" value="{{ old('app_slogan', $settings['app_slogan'] ?? '') }}"
                                    placeholder="e.g. Enterprise Asset & Maintenance Management System"
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('app_slogan') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Footer Copyright Text
                                </label>
                                <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text'] ?? '') }}"
                                    placeholder="e.g. Assetika. All rights reserved."
                                    class="h-9 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200" />
                                @error('footer_text') <p class="mt-1 text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo & Favicon Upload Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6 space-y-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <i data-lucide="image" class="w-5 h-5 text-brand-500"></i>
                            Logos & Favicon Customization
                        </h3>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <!-- Favicon Upload -->
                            <div class="space-y-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Favicon Icon (.png, .ico, .svg)
                                </label>
                                <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                        <img src="{{ $settings['favicon'] ?? '/images/favicon_assetika.png' }}" alt="Favicon" class="h-8 w-8 object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <input type="file" name="favicon" accept=".png,.ico,.svg,.webp" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-400" />
                                    </div>
                                </div>
                                @error('favicon') <p class="text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Light Logo Upload -->
                            <div class="space-y-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Light Theme Logo (.png, .svg, .webp)
                                </label>
                                <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                    <div class="flex h-12 w-24 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                        <img src="{{ $settings['logo_light'] ?? '/images/assetika.png' }}" alt="Light Logo" class="max-h-8 w-auto object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <input type="file" name="logo_light" accept=".png,.jpg,.jpeg,.svg,.webp" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-400" />
                                    </div>
                                </div>
                                @error('logo_light') <p class="text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- Dark Logo Upload -->
                            <div class="space-y-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400">
                                    Dark Theme Logo (.png, .svg, .webp)
                                </label>
                                <div class="flex items-center gap-4 rounded-xl border border-gray-200 bg-gray-50/50 p-3 dark:border-gray-800 dark:bg-gray-950">
                                    <div class="flex h-12 w-24 shrink-0 items-center justify-center rounded-lg border border-gray-800 bg-gray-900 p-2 shadow-sm">
                                        <img src="{{ $settings['logo_dark'] ?? '/images/assetika.png' }}" alt="Dark Logo" class="max-h-8 w-auto object-contain">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <input type="file" name="logo_dark" accept=".png,.jpg,.jpeg,.svg,.webp" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 dark:file:bg-brand-500/15 dark:file:text-brand-400" />
                                    </div>
                                </div>
                                @error('logo_dark') <p class="text-xs text-error-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar Card -->
            <div class="space-y-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-100 pb-3 dark:border-gray-800">
                        <i data-lucide="save" class="w-4 h-4 text-brand-500"></i>
                        Save Configuration
                    </h3>
                    <div class="space-y-3">
                        <button type="submit" class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-600 active:scale-[0.98] transition">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Save Settings & Branding
                        </button>
                        <a href="{{ route('assets.index') }}" class="inline-flex w-full justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
