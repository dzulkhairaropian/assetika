<footer class="mt-6 flex flex-col gap-3 border-t border-gray-100 bg-gray-50 p-4 text-xs text-gray-400 dark:border-gray-800 dark:bg-[#121212] dark:text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:gap-6 sm:px-6">
    <div class="flex items-center gap-2">
        <img src="{{ asset('images/favicon_assetika.png') }}" alt="{{ config('app.name') }} Logo" class="h-5 w-5 shrink-0">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
    </div>
    <div class="flex flex-wrap items-center gap-3 sm:gap-5">
        <a href="{{ url('/') }}" class="hover:text-brand-500 transition-colors">Home</a>
        @auth
            <span class="text-gray-300 dark:text-gray-700">|</span>
            <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
            <span class="text-gray-300 dark:text-gray-700">|</span>
            <a href="{{ route('profile.index') }}" class="hover:text-brand-500 transition-colors">Profile</a>
        @endauth
    </div>
</footer>
