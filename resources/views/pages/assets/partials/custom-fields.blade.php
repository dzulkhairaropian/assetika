{{-- Dynamic Custom Fields for Asset Create/Edit --}}
{{-- Requires: $categoryFieldsets (array, keyed by category_id), $fieldsets (array, keyed by fieldset_id) --}}
{{-- Optional: $asset for pre-filled values in edit mode --}}

@php
    $productCategories = \App\Models\Product::with('category')->get()
        ->mapWithKeys(fn($p) => [$p->id => $p->category?->id]);
@endphp

<div id="custom-fields-container" class="mt-8">
    {{-- Populated by JavaScript --}}
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryFieldsets = @json($categoryFieldsets);
    const fieldsets = @json($fieldsets);
    const productCategories = @json($productCategories);
    const existingValues = @json(isset($asset) ? ($asset->customFieldValues?->pluck('value', 'custom_field_id') ?? []) : []);
    const productSelect = document.querySelector('select[name="product_id"]');
    const container = document.getElementById('custom-fields-container');

    function renderCustomFields(categoryId) {
        if (!categoryId || !categoryFieldsets[categoryId]) {
            container.innerHTML = '';
            return;
        }

        let html = '<div><h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">' +
            '<i data-feather="list" class="w-5 h-5 text-brand-500"></i>Custom Fields</h3>';

        categoryFieldsets[categoryId].forEach(fs => {
            const fieldset = fieldsets[fs.id];
            if (!fieldset || !fieldset.custom_fields) return;

            html += '<div class="mb-4"><h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">' +
                fieldset.name + '</h4>';
            html += '<div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">';

            fieldset.custom_fields.forEach(cf => {
                const value = existingValues[cf.id] || cf.default_value || '';
                const required = cf.pivot?.is_required ? 'required' : '';
                const requiredMark = cf.pivot?.is_required ? '<span class="text-error-500">*</span>' : '';

                html += '<div>';
                html += '<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">' +
                    cf.name + ' ' + requiredMark + '</label>';

                switch (cf.field_type) {
                    case 'textarea':
                        html += '<textarea name="custom_fields[' + cf.id + ']" rows="3" ' + required +
                            ' class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">' +
                            value + '</textarea>';
                        break;
                    case 'dropdown':
                        const options = cf.field_values || [];
                        html += '<select name="custom_fields[' + cf.id + ']" ' + required +
                            ' class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">' +
                            '<option value="">-- Select --</option>';
                        options.forEach(opt => {
                            const selected = (value === opt) ? 'selected' : '';
                            html += '<option value="' + opt + '" ' + selected + '>' + opt + '</option>';
                        });
                        html += '</select>';
                        break;
                    case 'date':
                        html += '<input type="date" name="custom_fields[' + cf.id + ']" value="' + value + '" ' + required +
                            ' class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">';
                        break;
                    case 'boolean':
                        const checkedTrue = (value === '1' || value === 'true') ? 'checked' : '';
                        const checkedFalse = (value === '0' || value === 'false' || !value) ? 'checked' : '';
                        html += '<div class="flex items-center gap-6 mt-2">' +
                            '<label class="flex items-center gap-2"><input type="radio" name="custom_fields[' + cf.id + ']" value="1" ' + checkedTrue + ' class="text-brand-500"> Yes</label>' +
                            '<label class="flex items-center gap-2"><input type="radio" name="custom_fields[' + cf.id + ']" value="0" ' + checkedFalse + ' class="text-brand-500"> No</label>' +
                            '</div>';
                        break;
                    case 'number':
                        html += '<input type="number" name="custom_fields[' + cf.id + ']" value="' + value + '" ' + required +
                            ' class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">';
                        break;
                    default:
                        html += '<input type="text" name="custom_fields[' + cf.id + ']" value="' + value + '" ' + required +
                            ' class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">';
                        break;
                }

                if (cf.help_text) {
                    html += '<p class="mt-1 text-xs text-gray-400">' + cf.help_text + '</p>';
                }
                html += '</div>';
            });

            html += '</div></div>';
        });

        html += '</div>';
        container.innerHTML = html;

        // Re-initialize feather icons
        if (typeof feather !== 'undefined') feather.replace();
    }

    function updateFields() {
        if (!productSelect) return;
        const productId = productSelect.value;
        const categoryId = productCategories[productId];
        renderCustomFields(categoryId);
    }

    if (productSelect) {
        productSelect.addEventListener('change', updateFields);
        // Initial render
        updateFields();
    }
});
</script>
@endpush
