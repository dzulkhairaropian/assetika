@props(['title' => 'Dashboard'])

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | IT Support Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ page: 'ecommerce', loaded: true, darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'), sidebarToggle: false, menuToggle: false }"
      x-init="
          darkMode = JSON.parse(localStorage.getItem('darkMode'));
          $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
      :class="{'dark bg-gray-900': darkMode === true}"
      class="bg-gray-50 dark:bg-gray-900">

    <!-- Preloader -->
    <div x-show="loaded"
         x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
         class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>

    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Content Area -->
        <div class="relative flex flex-1 flex-col overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay -->
            <div @click="sidebarToggle = false"
                 :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                 class="fixed w-full h-screen z-9 bg-gray-900/50"></div>

            <!-- Header -->
            <x-header />

            <!-- Main Content -->
            <main>
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>