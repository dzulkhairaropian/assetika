@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
            <!-- Form -->
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                    <div>
                        <div class="mb-5 sm:mb-8">
                            <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                                Sign In
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Enter your email and password to sign in!
                            </p>
                        </div>

                        @if(session('registration_success'))
                            <div x-data="{ show: true }" x-show="show"
                                class="mb-5 flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 shadow-md dark:border-emerald-900/40 dark:bg-emerald-950/40">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-sm">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 10 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Registration Successful!</h4>
                                        <p class="text-xs text-emerald-700 dark:text-emerald-400">{{ session('registration_success') }}</p>
                                    </div>
                                </div>
                                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        @if(session('success'))
                            <x-ui.alert variant="success" message="{{ session('success') }}" class="mb-5" />
                        @endif

                        <div>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                            Email Address<span class="text-error-500">*</span>
                                        </label>
                                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="user@company.com"
                                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                                        @error('email')
                                            <p class="mt-1 text-xs text-error-500 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <!-- Password -->
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                            Password<span class="text-error-500">*</span>
                                        </label>
                                        <div x-data="{ showPassword: false }" class="relative">
                                            <input :type="showPassword ? 'text' : 'password'"
                                                name="password"
                                                placeholder="••••••••"
                                                class="h-11 w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-11 pl-4 text-sm text-gray-900 placeholder:text-gray-400 transition-all duration-150 ease-out focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/15 dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500" />
                                            <button type="button" @click="showPassword = !showPassword"
                                                class="absolute top-1/2 right-3 -translate-y-1/2 p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors active:scale-95">
                                                <svg x-show="!showPassword" class="w-5 h-5 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" />
                                                </svg>
                                                <svg x-show="showPassword" class="w-5 h-5 fill-current" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0064 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" />
                                                </svg>
                                            </button>
                                        </div>
                                        @error('password')
                                            <p class="mt-1 text-xs text-error-500 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <!-- Checkbox -->
                                    <div class="flex items-center justify-between">
                                        <div x-data="{ checkboxToggle: false }">
                                            <label for="checkboxLabelOne"
                                                class="flex cursor-pointer items-center text-sm font-normal text-gray-700 select-none dark:text-gray-400">
                                                <div class="relative">
                                                    <input type="checkbox" name="remember" id="checkboxLabelOne" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                                                    <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' :
                                                        'bg-transparent border-gray-300 dark:border-gray-700'"
                                                        class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                        <span :class="checkboxToggle ? '' : 'opacity-0'">
                                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                                Keep me logged in
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400 text-sm">
                                            Forgot password?
                                        </a>
                                    </div>
                                    <!-- Button -->
                                    <div>
                                        <button type="submit"
                                            class="bg-brand-500 hover:bg-brand-600 active:scale-[0.98] flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-150 ease-out focus:outline-none focus:ring-4 focus:ring-brand-500/20">
                                            Sign In
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400">
                                    Don't have an account?
                                    <a href="{{ route('register') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">Sign Up</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-brand-950 relative hidden h-full w-full items-center lg:grid lg:w-1/2 dark:bg-white/5">
                <div class="z-1 flex items-center justify-center">
                    <!-- ===== Common Grid Shape Start ===== -->
                    <x-common.common-grid-shape/>
                    <div class="flex max-w-xs flex-col items-center">
                        <a href="/" class="mb-4 block">
                            <img src="/images/assetika.png" alt="Logo" width="180" />
                        </a>
                        <p class="text-center text-gray-400 dark:text-white/60">
                            Modern and Centralized Business Asset Management System
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dark/Light Toggle -->
        <div class="fixed z-50 hidden bottom-6 right-6 sm:block">
            <button
                class="inline-flex items-center justify-center text-white transition-colors rounded-full size-14 bg-brand-500 hover:bg-brand-600"
                @click="$store.theme.toggle()"
                aria-label="Toggle dark mode">
                <span class="dark:hidden"><i data-feather="moon" class="w-5 h-5"></i></span>
                <span class="hidden dark:block"><i data-feather="sun" class="w-5 h-5"></i></span>
            </button>
        </div>
    </div>
@endsection
