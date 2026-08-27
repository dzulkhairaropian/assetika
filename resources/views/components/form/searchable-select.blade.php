@props([
    'name',
    'label' => null,
    'options' => [], // array of ['id' => ..., 'text' => ...]
    'selected' => null,
    'placeholder' => 'Select Option',
    'required' => false,
    'class' => '',
])

<div class="relative {{ $class }}" x-data="{
    search: '',
    open: false,
    options: {{ json_encode($options) }},
    selectedId: '{{ $selected }}',
    placeholder: '{{ addslashes($placeholder) }}',
    toggleOpen() {
        this.open = !this.open;
        if (this.open) {
            $nextTick(() => this.$refs.searchInput?.focus());
        }
    },
    get selectedLabel() {
        const item = this.options.find(o => o.id == this.selectedId);
        return item ? item.text : this.placeholder;
    },
    get filteredOptions() {
        if (!this.search) return this.options;
        return this.options.filter(o => String(o.text).toLowerCase().includes(this.search.toLowerCase()));
    },
    selectOption(id) {
        this.selectedId = id;
        this.open = false;
        this.search = '';
    }
}">
    @if($label)
        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ $label }}
            @if($required) <span class="text-error-500">*</span> @endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="selectedId" @if($required) required @endif>

    <!-- Trigger Button -->
    <div @click="toggleOpen()" @click.away="open = false" 
        class="h-9 w-full cursor-pointer flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
        <span x-text="selectedLabel" :class="!selectedId && 'text-gray-400 dark:text-gray-500'" class="truncate"></span>
        <svg class="stroke-current text-gray-400 shrink-0" width="16" height="16" viewBox="0 0 20 20" fill="none">
            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>

    <!-- Dropdown Menu with Search -->
    <div x-show="open" x-cloak x-transition
        class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-800 dark:bg-gray-900 space-y-1">
        <div class="p-1 sticky top-0 bg-white dark:bg-gray-900 z-10">
            <input type="text" x-ref="searchInput" x-model="search" @click.stop 
                placeholder="Search..." 
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-800/50">
            <div @click="selectOption('')" 
                 class="cursor-pointer rounded-lg px-3 py-2 text-xs font-medium text-gray-400 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-800">
                -- {{ $placeholder }} --
            </div>
            <template x-for="item in filteredOptions" :key="item.id">
                <div @click="selectOption(item.id)" 
                     :class="selectedId == item.id ? 'bg-brand-50 text-brand-600 font-semibold dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                     class="cursor-pointer rounded-lg px-3 py-2 text-xs transition-colors" 
                     x-text="item.text">
                </div>
            </template>
            <div x-show="filteredOptions.length === 0" class="p-3 text-center text-xs text-gray-400">
                No matching options found.
            </div>
        </div>
    </div>
</div>
