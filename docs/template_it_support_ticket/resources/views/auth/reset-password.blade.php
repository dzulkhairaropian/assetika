<x-auth-layout>
    <div x-data="{ loaded: true, darkMode: false }"
         x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')) || false; $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
         :class="{'dark bg-gray-900': darkMode === true}">
        <div x-show="loaded"
             x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
             class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
            <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
        </div>

        <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
            <div class="flex flex-col justify-center w-full h-screen dark:bg-gray-900 sm:p-0 lg:flex-row">
                <div class="flex flex-col flex-1 w-full lg:w-1/2">
                    <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
                        <div class="mb-5 sm:mb-8">
                            <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">Reset Password</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Create a new password for your account.</p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-600 text-sm">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="space-y-5">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
                                    <input type="email" name="email" value="{{ old('email', request('email')) }}" required autofocus class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Password</label>
                                    <input type="password" name="password" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Confirm Password</label>
                                    <input type="password" name="password_confirmation" required class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                                </div>
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="relative items-center hidden w-full h-full bg-brand-950 dark:bg-white/5 lg:grid lg:w-1/2">
                    <div class="flex items-center justify-center z-1">
                        <div class="absolute right-0 top-0 -z-1 w-full max-w-[250px] xl:max-w-[450px]"><img src="{{ asset('images/grid-01.svg') }}" alt="grid" /></div>
                        <div class="absolute bottom-0 left-0 -z-1 w-full max-w-[250px] rotate-180 xl:max-w-[450px]"><img src="{{ asset('images/grid-01.svg') }}" alt="grid" /></div>
                        <div class="flex flex-col items-center max-w-xs">
                            <div class="block mb-4"><img src="{{ asset('images/auth-logo.svg') }}" alt="Logo" /></div>
                            <p class="text-center text-gray-400 dark:text-white/60">IT Support Ticket Tracker</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-auth-layout>
