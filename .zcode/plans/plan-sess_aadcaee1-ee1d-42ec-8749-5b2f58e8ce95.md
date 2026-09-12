### Fix: Blade Compiler Misparse on `page-breadcrumb` `:items` Attribute

#### Root Cause
In Laravel 13 Blade Compiler, passing inline array expressions with nested null-coalescing property accesses (e.g., `:items="[..., ['label' => $component->component_tag ?? $component->name]]"`) inside component attribute values causes Blade regex attribute parsing to misinterpret `$component->name` as an undefined component property `$name`.

#### Solution Plan
1. In `resources/views/pages/components/show.blade.php`, extract breadcrumb items array to a clean `@php` block at the top of the file:
   ```php
   @php
       $breadcrumbLabel = $component->component_tag ?? $component->name;
       $breadcrumbItems = [
           ['label' => 'Components', 'url' => route('components.index')],
           ['label' => $breadcrumbLabel],
       ];
   @endphp
   ```
2. Pass `:items="$breadcrumbItems"` cleanly to `<x-common.page-breadcrumb>`.
3. Verify GET `/components/2` loads 100% successfully.
4. Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `resources/views/pages/components/show.blade.php`