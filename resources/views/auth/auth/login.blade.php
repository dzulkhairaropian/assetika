<x-auth-layout>
    <!-- Preloader -->
    <div x-show="loaded"
         x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
         class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>

    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div class="relative flex flex-col justify-center w-full h-screen dark:bg-gray-900 sm:p-0 lg:flex-row">
            <!-- Form -->
            <div class="flex flex-col flex-1 w-full lg:w-1/2">
                <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                    <div class="mb-5 sm:mb-8">
                        <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">Sign In</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Enter your email and password to sign in!</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4">
                            <x-alert type="error" title="Sign In Failed" :message="$errors->first()" />
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-4">
                            <x-alert type="success" title="Success" :message="session('status')" />
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="space-y-5">
                            <!-- Email -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Email<span class="text-error-500">*</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                       placeholder="info@gmail.com"
                                       class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>
                            <!-- Password -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Password<span class="text-error-500">*</span>
                                </label>
                                <div x-data="{ showPassword: false }" class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="password" required
                                           placeholder="Enter your password"
                                           class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                    <button type="button" @click="showPassword = !showPassword"
                                          class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-400">
                                        <svg x-show="!showPassword" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <svg x-show="showPassword" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                            <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                                            <path d="M14.084 14.158a10.745 10.745 0 0 1-12.022-1.81 1 1 0 0 1 0-.696 10.747 10.747 0 0 1 4.225-4.225"/>
                                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                            <line x1="2" y1="2" x2="22" y2="22"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <!-- Remember me / Forgot password -->
                            <div class="flex items-center justify-between">
                                <div x-data="{ checkboxToggle: false }">
                                    <label for="remember" class="flex items-center text-sm font-normal text-gray-700 cursor-pointer select-none dark:text-gray-400">
                                        <div class="relative">
                                            <input type="checkbox" id="remember" name="remember" class="sr-only" @change="checkboxToggle = !checkboxToggle" />
                                            <div :class="checkboxToggle ? 'border-brand-500 bg-brand-500' : 'bg-transparent border-gray-300 dark:border-gray-700'"
                                                 class="mr-3 flex h-5 w-5 items-center justify-center rounded-md border-[1.25px]">
                                                <span :class="checkboxToggle ? '' : 'opacity-0'">
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                        Keep me logged in
                                    </label>
                                </div>
                                <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400">
                                    Forgot password?
                                </a>
                            </div>
                            <!-- Submit Button -->
                            <div>
                                <button type="submit" class="w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 hover:bg-brand-600">
                                    Sign In
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-5">
                        <p class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">
                                Sign Up
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Banner -->
            <div class="relative items-center justify-center hidden w-full h-full bg-brand-950 dark:bg-white/5 lg:flex lg:w-1/2 isolate">
                        <div class="absolute right-0 top-0 -z-1 w-full max-w-[450px]"><img src="{{ asset('images/shape/grid-01.svg') }}" alt="grid" /></div>
                        <div class="absolute bottom-0 left-0 -z-1 w-full max-w-[250px] rotate-180 xl:max-w-[450px]"><img src="{{ asset('images/shape/grid-01.svg') }}" alt="grid" /></div>
                <div class="flex flex-col items-center max-w-xs text-center z-1">
                    <img src="{{ asset('images/auth-logo.svg') }}" alt="Logo" class="mb-4 mx-auto" />
                    <p class="text-gray-400 dark:text-white/60">Unified IT Support & Management System</p>
                </div>
            </div>
            
            <!-- Dark Mode Toggler -->
            <div class="fixed z-50 bottom-6 right-6">
                <button class="flex items-center justify-center text-white rounded-full size-14 bg-brand-500 hover:bg-brand-600 active:scale-95 shadow-lg"
                        @click="$store.theme.toggle()"
                        aria-label="Toggle dark mode">
                    <svg class="size-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                    </svg>
                    <svg class="size-5 hidden dark:block text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/>
                        <path d="M2 12h2"/><path d="M22 12h-2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</x-auth-layout>
