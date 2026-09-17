@props([
    'name',
    'label' => null,
    'options' => [], // array of ['id' => ..., 'text' => ...]
    'selected' => null,
    'placeholder' => 'Select Option',
    'required' => false,
    'multiple' => false,
    'class' => '',
])

@php
    $initialSelected = [];
    if ($multiple) {
        if (is_array($selected)) {
            $initialSelected = array_map('strval', array_filter($selected));
        } elseif (is_string($selected) || is_numeric($selected)) {
            $initialSelected = array_values(array_filter(array_map('strval', array_map('trim', explode(',', (string) $selected)))));
        }
    } else {
        $initialSelected = $selected !== null ? (string) $selected : '';
    }
@endphp

<div class="relative {{ $class }}" x-data="{
    search: '',
    open: false,
    multiple: {{ $multiple ? 'true' : 'false' }},
    options: {{ json_encode($options) }},
    selectedId: {{ $multiple ? json_encode($initialSelected) : json_encode($initialSelected) }},
    placeholder: '{{ addslashes($placeholder) }}',
    toggleOpen() {
        this.open = !this.open;
        if (this.open) {
            $nextTick(() => this.$refs.searchInput?.focus());
        }
    },
    get selectedLabel() {
        if (this.multiple) {
            if (!Array.isArray(this.selectedId) || this.selectedId.length === 0) {
                return this.placeholder;
            }
            if (this.selectedId.length === 1) {
                const item = this.options.find(o => String(o.id) === String(this.selectedId[0]));
                return item ? item.text : this.placeholder;
            }
            return this.selectedId.length + ' Selected';
        } else {
            const item = this.options.find(o => String(o.id) === String(this.selectedId));
            return item ? item.text : this.placeholder;
        }
    },
    get filteredOptions() {
        if (!this.search) return this.options;
        return this.options.filter(o => String(o.text).toLowerCase().includes(this.search.toLowerCase()));
    },
    isSelected(id) {
        if (this.multiple) {
            return Array.isArray(this.selectedId) && this.selectedId.map(String).includes(String(id));
        }
        return String(this.selectedId) === String(id);
    },
    selectOption(id) {
        if (this.multiple) {
            if (!Array.isArray(this.selectedId)) this.selectedId = [];
            const strId = String(id);
            const idx = this.selectedId.map(String).indexOf(strId);
            if (idx > -1) {
                this.selectedId.splice(idx, 1);
            } else {
                this.selectedId.push(strId);
            }
            this.$dispatch('select-changed', { name: '{{ $name }}', id: this.selectedId });
        } else {
            this.selectedId = String(id);
            this.open = false;
            this.search = '';
            this.$dispatch('select-changed', { name: '{{ $name }}', id: id });
        }
    },
    clearSelection() {
        if (this.multiple) {
            this.selectedId = [];
        } else {
            this.selectedId = '';
        }
        this.$dispatch('select-changed', { name: '{{ $name }}', id: this.selectedId });
    },
    selectAllOptions() {
        if (this.multiple) {
            this.selectedId = this.options.map(o => String(o.id));
            this.$dispatch('select-changed', { name: '{{ $name }}', id: this.selectedId });
        }
    },
    updateOptions(newOptions, newSelectedId = null) {
        this.options = newOptions;
        if (newSelectedId !== null) {
            this.selectedId = newSelectedId;
        }
    }
}"
@set-select-options.window="if ($event.detail.name === '{{ $name }}') updateOptions($event.detail.options, $event.detail.selectedId)"
@set-selected-id.window="if ($event.detail.name === '{{ $name }}') selectedId = $event.detail.id"
>
    @if($label)
        <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ $label }}
            @if($required) <span class="text-error-500">*</span> @endif
        </label>
    @endif

    <template x-if="multiple">
        <div>
            <template x-for="id in selectedId" :key="id">
                <input type="hidden" name="{{ str_contains($name, '[') ? $name : $name.'[]' }}" :value="id">
            </template>
        </div>
    </template>
    <template x-if="!multiple">
        <input type="hidden" name="{{ $name }}" :value="selectedId" @if($required) required @endif>
    </template>

    <!-- Trigger Button -->
    <div @click="toggleOpen()" @click.away="open = false" 
        class="h-9 w-full cursor-pointer flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-gray-800 focus:border-brand-500 focus:bg-white dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200 transition">
        <span x-text="selectedLabel" :class="((multiple && (!selectedId || !selectedId.length)) || (!multiple && !selectedId)) && 'text-gray-400 dark:text-gray-500'" class="truncate"></span>
        <div class="flex items-center gap-1 shrink-0">
            <template x-if="multiple && selectedId && selectedId.length > 0">
                <span class="rounded-full bg-brand-500 px-1.5 py-0.2 text-[10px] font-bold text-white leading-none" x-text="selectedId.length"></span>
            </template>
            <svg class="stroke-current text-gray-400 shrink-0" width="16" height="16" viewBox="0 0 20 20" fill="none">
                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>

    <!-- Dropdown Menu with Search -->
    <div x-show="open" x-cloak x-transition
        class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-800 dark:bg-gray-900 space-y-1">
        <div class="p-1 sticky top-0 bg-white dark:bg-gray-900 z-10 space-y-1.5">
            <input type="text" x-ref="searchInput" x-model="search" @click.stop 
                placeholder="Search..." 
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-800 dark:bg-gray-950 dark:text-gray-200">
            
            <template x-if="multiple">
                <div class="flex items-center justify-between px-1 text-[11px]">
                    <button type="button" @click.stop="selectAllOptions()" class="font-medium text-brand-600 hover:underline dark:text-brand-400">Select All</button>
                    <button type="button" @click.stop="clearSelection()" class="font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Clear</button>
                </div>
            </template>
        </div>

        <div class="divide-y divide-gray-50 dark:divide-gray-800/50">
            <template x-if="!multiple && !required && placeholder">
                <div @click="selectOption('')" 
                     class="cursor-pointer rounded-lg px-3 py-2 text-xs font-medium text-gray-400 hover:bg-gray-50 dark:text-gray-500 dark:hover:bg-gray-800">
                    -- {{ $placeholder }} --
                </div>
            </template>
            <template x-for="item in filteredOptions" :key="item.id">
                <div @click.stop="selectOption(item.id)" 
                     :class="isSelected(item.id) ? 'bg-brand-50 text-brand-600 font-semibold dark:bg-brand-500/10 dark:text-brand-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800'"
                     class="cursor-pointer rounded-lg px-3 py-2 text-xs transition-colors flex items-center justify-between">
                    <span x-text="item.text" class="truncate pr-2"></span>
                    <template x-if="multiple">
                        <div :class="isSelected(item.id) ? 'border-brand-500 bg-brand-500' : 'border-gray-300 dark:border-gray-600'"
                             class="h-4 w-4 shrink-0 rounded border flex items-center justify-center transition-colors">
                            <svg x-show="isSelected(item.id)" width="10" height="10" viewBox="0 0 14 14" fill="none">
                                <path d="M11.6666 3.5L5.24992 9.91667L2.33325 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </template>
                </div>
            </template>
            <div x-show="filteredOptions.length === 0" class="p-3 text-center text-xs text-gray-400">
                No matching options found.
            </div>
        </div>
    </div>
</div>
