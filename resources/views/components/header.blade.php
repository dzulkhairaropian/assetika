<header x-data="{menuToggle: false}"
        class="sticky top-0 z-9999 flex w-full border-b border-gray-200/80 bg-white/90 backdrop-blur-md dark:border-gray-800/80 dark:bg-gray-900/90 transition-all duration-200">
    <div class="flex grow flex-col items-center justify-between lg:flex-row lg:px-6">
        <!-- Top Row: Hamburger, Mobile Logo, Menu Toggle -->
        <div class="flex w-full items-center justify-between gap-2 border-b border-gray-200/80 px-3 py-2.5 sm:gap-4 lg:justify-normal lg:border-b-0 lg:px-0 lg:py-3 dark:border-gray-800/80">
            <!-- Hamburger Toggle BTN -->
            <button :class="sidebarToggle ? 'lg:bg-transparent dark:lg:bg-transparent bg-gray-100 dark:bg-gray-800' : ''"
                    class="z-9999 flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 active:scale-95 transition-all duration-150 ease-out lg:h-10 lg:w-10 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    @click.stop="sidebarToggle = !sidebarToggle"
                    aria-label="Toggle Sidebar">
                <svg class="hidden fill-current lg:block" width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z" fill="" />
                </svg>
                <svg :class="sidebarToggle ? 'hidden' : 'block lg:hidden'"
                     class="fill-current lg:hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.414 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z" fill="" />
                </svg>
                <svg :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                     class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9984 12L6.21967 7.28131Z" fill="" />
                </svg>
            </button>

            <!-- Logo (Mobile Only) -->
            <a href="{{ route('dashboard') }}" class="lg:hidden flex items-center gap-2 font-bold text-base text-gray-900 dark:text-white">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-500 text-white font-extrabold text-xs">A</div>
                <span>Assetika</span>
            </a>

            <!-- Mobile Menu Toggle -->
            <button class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 active:scale-95 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800 transition-all"
                    :class="menuToggle ? 'bg-gray-100 dark:bg-gray-800' : ''"
                    @click.stop="menuToggle = !menuToggle">
                <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.4951C6.82745 10.4951 7.49902 11.1667 7.49902 11.9951V12.0051C7.49902 12.8335 6.82745 13.5051 5.99902 13.5051C5.1706 13.5051 4.49902 12.8335 4.49902 12.0051V11.9951C4.49902 11.1667 5.1706 10.4951 5.99902 10.4951ZM17.999 10.4951C18.8275 10.4951 19.499 11.1667 19.499 11.9951V12.0051C19.499 12.8335 18.8275 13.5051 17.999 13.5051C17.1706 13.5051 16.499 12.8335 16.499 12.0051V11.9951C16.499 11.1667 17.1706 10.4951 17.999 10.4951ZM13.499 11.9951C13.499 11.1667 12.8275 10.4951 11.999 10.4951C11.1706 10.4951 10.499 11.1667 10.499 11.9951V12.0051C10.499 12.8335 11.1706 13.5051 11.999 13.5051C12.8275 13.5051 13.499 12.8335 13.499 12.0051V11.9951Z" fill="" />
                </svg>
            </button>
        </div>

        <!-- Bottom Row: Dark Mode, Notification, User -->
        <div :class="menuToggle ? 'flex' : 'hidden'"
             class="w-full items-center justify-between gap-3 px-4 py-3 lg:flex lg:justify-end lg:px-0 lg:py-2">
            <!-- Left actions -->
            <div class="flex items-center gap-2">
                <!-- Dark Mode Toggler -->
                <button class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/90 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800 active:scale-95 transition-all duration-150 ease-out dark:border-gray-800/90 dark:bg-gray-950 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-white"
                        @click.prevent="$store.theme.toggle()"
                        aria-label="Toggle Dark Mode">
                    <i data-lucide="sun" class="hidden dark:block w-4 h-4"></i>
                    <i data-lucide="moon" class="dark:hidden w-4 h-4"></i>
                </button>

                <!-- Notification Dropdown -->
                <div class="relative"
                     x-data="{ dropdownOpen: false, notifying: true }"
                     @click.outside="dropdownOpen = false">
                    <button class="relative flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/90 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800 active:scale-95 transition-all duration-150 ease-out dark:border-gray-800/90 dark:bg-gray-950 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-white"
                            @click.prevent="dropdownOpen = !dropdownOpen; notifying = false">
                        <span :class="!notifying ? 'hidden' : 'flex'"
                              class="absolute top-1 right-1 z-1 h-2 w-2 rounded-full bg-brand-500">
                            <span class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-brand-500 opacity-75"></span>
                        </span>
                        <i data-lucide="bell" class="w-4 h-4"></i>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute -right-[180px] mt-2 flex h-[380px] w-[320px] origin-top-right flex-col rounded-xl border border-gray-200/90 bg-white p-3 shadow-lg lg:right-0 dark:border-gray-800 dark:bg-gray-950"
                         style="display: none;">
                        <div class="mb-2 flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                            <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-800 dark:text-white">Notifications</h5>
                            <button @click="dropdownOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <ul class="custom-scrollbar flex h-auto flex-col overflow-y-auto space-y-1">
                            <li>
                                <a class="flex gap-3 rounded-lg p-2.5 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors" href="#">
                                    <span class="relative block h-8 w-8 shrink-0 rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 flex items-center justify-center font-bold text-xs">
                                        IT
                                    </span>
                                    <span class="block">
                                        <span class="text-xs mb-0.5 block text-gray-600 dark:text-gray-300">
                                            <span class="font-semibold text-gray-900 dark:text-white">New asset checkout</span> assigned
                                        </span>
                                        <span class="text-[10px] text-gray-400">5 min ago</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="relative"
                 x-data="{ dropdownOpen: false }"
                 @click.outside="dropdownOpen = false">
                <button class="flex items-center gap-2 rounded-lg p-1 hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors active:scale-[0.98]"
                   @click.prevent="dropdownOpen = !dropdownOpen">
                    <div class="relative h-9 w-9 overflow-hidden rounded-full border border-gray-200 dark:border-gray-800 shrink-0">
                        <img src="{{ auth()->user()->avatar ?? 'https://www.gravatar.com/avatar/' . md5(auth()->user()->email) . '?d=mp' }}" alt="User" class="h-full w-full object-cover" />
                    </div>
                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 hidden sm:block truncate max-w-[130px]">{{ auth()->user()->name }}</span>
                    <i data-lucide="chevron-down" :class="dropdownOpen ? 'rotate-180' : ''" class="transition-transform duration-200 w-3.5 h-3.5 text-gray-400"></i>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="dropdownOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 flex w-52 origin-top-right flex-col rounded-xl border border-gray-200/90 bg-white p-2 shadow-lg dark:border-gray-800 dark:bg-gray-950"
                     style="display: none;">
                    <div class="px-2 py-1.5 border-b border-gray-100 dark:border-gray-800 mb-1">
                        <span class="block text-xs font-semibold text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] text-gray-400 truncate">{{ auth()->user()->email }}</span>
                    </div>
                    <ul class="flex flex-col gap-0.5">
                        <li>
                            <a href="/profile" class="group flex items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-brand-600 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-brand-400 transition-colors">
                                <i data-lucide="user" class="w-4 h-4 text-gray-400 group-hover:text-brand-500"></i>
                                Edit Profile
                            </a>
                        </li>
                    </ul>
                    <div class="border-t border-gray-100 dark:border-gray-800 mt-1 pt-1">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="group flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10 transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4 text-error-500"></i>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>