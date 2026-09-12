### Fix: Isolate Inline String Evaluation in `x-form.searchable-select` Attributes

#### Root Cause
In Laravel 13's Blade Compiler, passing inline function calls with single-quoted string parameters (e.g. `:selected="old('product_id', ...)"`) inside component attribute values causes the tag compiler regex to misinterpret `'product_id'` as a component variable property `$product_id`.

#### Solution Plan
1. In `edit.blade.php`, `create.blade.php`, and `show.blade.php`, extract selected values to clean `@php` variables at the top of the file/block:
   - `@php $selectedProduct = old('product_id', $component->product_id); @endphp`
   - Pass `:selected="$selectedProduct"` directly.
2. Verify GET `/components/2/edit` loads successfully without any Blade compiler errors.
3. Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/edit.blade.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/show.blade.php`