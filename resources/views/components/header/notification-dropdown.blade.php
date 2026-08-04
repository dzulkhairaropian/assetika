{{-- Notification Dropdown Component --}}
@php
    $notifications = auth()->user() ? auth()->user()->unreadNotifications->take(8) : collect();
    $unreadCount = auth()->user() ? auth()->user()->unreadNotifications->count() : 0;
@endphp

<div class="relative" x-data="{
    dropdownOpen: false,
    notifying: {{ $unreadCount > 0 ? 'true' : 'false' }},
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        this.notifying = false;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- Notification Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 bg-white border border-gray-200 rounded-xl hover:text-brand-500 hover:bg-gray-50 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-white h-10 w-10 transition-all duration-200"
        @click="toggleDropdown()"
        type="button"
        aria-label="Notifications"
    >
        <!-- Notification Badge -->
        <span
            x-show="notifying"
            class="absolute top-1.5 right-1.5 z-1 h-2 w-2 rounded-full bg-orange-400"
        >
            <span
                class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"
            ></span>
        </span>

        <!-- Bell Icon -->
        <i data-feather="bell" class="w-5 h-5"></i>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark sm:w-[361px] lg:right-0 z-99999"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notifications</h5>
                @if($unreadCount > 0)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold text-white bg-brand-500 rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </div>

            <button @click="closeDropdown()" class="text-gray-500 dark:text-gray-400" type="button">
                <svg
                    class="fill-current"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill=""
                    />
                </svg>
            </button>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar grow gap-1">
            @forelse ($notifications as $notification)
                <li>
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST" id="form-read-{{ $notification->id }}" style="display:none;">
                        @csrf
                    </form>
                    <a
                        class="flex gap-3 rounded-xl border-b border-gray-50/50 dark:border-gray-800/50 p-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-200 cursor-pointer"
                        onclick="event.preventDefault(); document.getElementById('form-read-{{ $notification->id }}').submit();"
                    >
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-brand-50/10 dark:bg-brand-500/10 text-brand-500 shrink-0">
                            @php
                                $iconName = $notification->data['icon'] ?? 'bell';
                            @endphp
                            <i data-feather="{{ $iconName }}" class="w-5 h-5"></i>
                        </div>

                        <div class="block">
                            <span class="mb-1 block text-theme-sm text-gray-800 dark:text-white/90 font-medium">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </span>
                            <span class="mb-1.5 block text-theme-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                {{ $notification->data['message'] ?? '' }}
                            </span>
                            <span class="flex items-center gap-2 text-gray-400 text-theme-xs dark:text-gray-500">
                                <span class="font-medium text-brand-500 dark:text-brand-400">{{ $notification->data['category'] ?? 'System' }}</span>
                                <span class="w-1 h-1 bg-gray-300 dark:bg-gray-750 rounded-full"></span>
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="flex flex-col items-center justify-center p-8 text-center text-gray-400 dark:text-gray-500 h-full my-auto">
                    <div class="p-3 bg-gray-50 dark:bg-white/[0.03] rounded-full mb-3">
                        <i data-feather="bell-off" class="w-8 h-8 opacity-60"></i>
                    </div>
                    <p class="text-sm font-medium">No new notifications</p>
                    <p class="text-xs mt-1 text-gray-400">We will notify you when something happens.</p>
                </li>
            @endforelse
        </ul>

        <!-- Mark All as Read / Bottom Area -->
        <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full justify-center rounded-xl border border-gray-200 bg-white p-3 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 transition-all duration-200"
                    >
                        Mark All as Read
                    </button>
                </form>
            @else
                <div class="text-center py-2 text-theme-xs text-gray-400 dark:text-gray-500 font-medium">
                    All caught up! ✨
                </div>
            @endif
        </div>
    </div>
    <!-- Dropdown End -->
</div>
