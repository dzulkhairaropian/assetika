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
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
            @if($required) <span class="text-error-500">*</span> @endif
        </label>
    @endif

    <input type="hidden" name="{{ $name }}" :value="selectedId" @if($required) required @endif>

    <div @click="open = !open" @click.away="open = false" class="cursor-pointer flex items-center justify-between h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        <span x-text="selectedLabel" class="truncate"></span>
        <i data-feather="chevron-down" class="w-4 h-4 text-gray-400 shrink-0"></i>
    </div>

    <div x-show="open" x-cloak class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-900">
        <input type="text" x-model="search" @click.stop placeholder="Search..." class="w-full mb-2 rounded-md border border-gray-200 p-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
        <template x-for="item in filteredOptions" :key="item.id">
            <div @click="selectOption(item.id)" class="cursor-pointer rounded-md p-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800" x-text="item.text"></div>
        </template>
    </div>
</div>
