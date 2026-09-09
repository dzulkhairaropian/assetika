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
        class="relative flex items-center justify-center text-gray-500 bg-white border border-gray-200/90 rounded-xl hover:bg-gray-50 hover:text-gray-800 active:scale-95 dark:border-gray-800/90 dark:bg-gray-950 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-white h-10 w-10 transition-all duration-150 ease-out shrink-0"
        @click="toggleDropdown()"
        type="button"
        aria-label="Notifications"
    >
        <!-- Notification Badge -->
        <span
            x-show="notifying"
            class="absolute top-1 right-1 z-1 h-2 w-2 rounded-full bg-brand-500"
        >
            <span
                class="absolute inline-flex w-full h-full bg-brand-500 rounded-full opacity-75 -z-1 animate-ping"
            ></span>
        </span>

        <!-- Bell Icon -->
        <i data-feather="bell" class="w-4 h-4"></i>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-[240px] sm:right-0 mt-2 flex h-[420px] w-[340px] sm:w-[360px] origin-top-right flex-col rounded-xl border border-gray-200/90 bg-white p-3 shadow-lg dark:border-gray-800 dark:bg-gray-950 z-50"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="flex items-center justify-between pb-2.5 mb-2 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-800 dark:text-white">Notifications</h5>
                @if($unreadCount > 0)
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold text-white bg-brand-500 rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </div>

            <button @click="closeDropdown()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" type="button">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar grow gap-1 pr-0.5">
            @forelse ($notifications as $notification)
                <li>
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST" id="form-read-{{ $notification->id }}" style="display:none;">
                        @csrf
                    </form>
                    <a
                        class="flex gap-3 rounded-lg border-b border-gray-100/60 dark:border-gray-800/60 p-2.5 hover:bg-gray-50 dark:hover:bg-gray-900 active:scale-[0.98] transition-all duration-150 cursor-pointer"
                        onclick="event.preventDefault(); document.getElementById('form-read-{{ $notification->id }}').submit();"
                    >
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 shrink-0 mt-0.5">
                            @php
                                $iconName = $notification->data['icon'] ?? 'bell';
                            @endphp
                            <i data-feather="{{ $iconName }}" class="w-4 h-4"></i>
                        </div>

                        <div class="block overflow-hidden">
                            <span class="mb-0.5 block text-xs font-semibold text-gray-900 dark:text-white truncate">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </span>
                            <span class="mb-1 block text-[11px] text-gray-500 dark:text-gray-400 leading-snug line-clamp-2">
                                {{ $notification->data['message'] ?? '' }}
                            </span>
                            <span class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-500">
                                <span class="font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider">{{ $notification->data['category'] ?? 'System' }}</span>
                                <span class="w-1 h-1 bg-gray-300 dark:bg-gray-700 rounded-full"></span>
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="flex flex-col items-center justify-center p-6 text-center text-gray-400 dark:text-gray-500 h-full my-auto">
                    <div class="p-2.5 bg-gray-50 dark:bg-gray-900 rounded-full mb-2">
                        <i data-feather="bell-off" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">No new notifications</p>
                    <p class="text-[11px] mt-0.5 text-gray-400">We will notify you when something happens.</p>
                </li>
            @endforelse
        </ul>

        <!-- Mark All as Read / Bottom Area -->
        <div class="pt-2.5 border-t border-gray-100 dark:border-gray-800">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full justify-center rounded-lg border border-gray-200 bg-white py-2 px-3 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 active:scale-[0.98] dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-850 transition-all duration-150"
                    >
                        Mark All as Read
                    </button>
                </form>
            @else
                <div class="text-center py-1 text-[11px] text-gray-400 dark:text-gray-500 font-medium">
                    All caught up! ✨
                </div>
            @endif
        </div>
    </div>
    <!-- Dropdown End -->
</div>
