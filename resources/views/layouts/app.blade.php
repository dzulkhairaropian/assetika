<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Assetika</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon_assetika.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();
        });
    </script>

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,

                triggerResize() {
                    window.dispatchEvent(new Event('resize'));
                    setTimeout(() => window.dispatchEvent(new Event('resize')), 150);
                    setTimeout(() => window.dispatchEvent(new Event('resize')), 350);
                },

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                    this.triggerResize();
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                    this.triggerResize();
                },

                setMobileOpen(val) {
                    if (this.isMobileOpen !== val) {
                        this.isMobileOpen = val;
                        this.triggerResize();
                    }
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        if (this.isHovered !== val) {
                            this.isHovered = val;
                            this.triggerResize();
                        }
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>

</head>

<body
    x-data="{ 'loaded': true}"
    x-init="let wasMobile = window.innerWidth < 1280;
    const checkMobile = () => {
        const isNowMobile = window.innerWidth < 1280;
        if (isNowMobile && !wasMobile) {
            // Desktop → mobile: close menu
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else if (!isNowMobile && wasMobile) {
            // Mobile → desktop: restore expanded
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
        wasMobile = isNowMobile;
    };
    window.addEventListener('resize', checkMobile);
    setTimeout(() => window.dispatchEvent(new Event('resize')), 150);
    setTimeout(() => window.dispatchEvent(new Event('resize')), 350);
    setTimeout(() => { loaded = false; }, 350);
">

    {{-- preloader --}}
    <x-common.preloader/>
    {{-- preloader end --}}

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">
            <!-- app header start -->
            @include('layouts.app-header')
            <!-- app header end -->
            <main class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6 flex-grow w-full">
                @yield('content')
            </main>
        </div>

    </div>

</body>

@stack('scripts')

</html>
