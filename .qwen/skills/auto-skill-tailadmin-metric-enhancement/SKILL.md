---
name: tailadmin-metric-enhancement
description: Add trend badges (up/down %), stroke SVG icons, and description text to TailAdmin metric widgets and chart headers
source: auto-skill
extracted_at: '2026-07-09T08:36:34.119Z'
---

# TailAdmin Metric Widget Enhancement

Enhance dashboard metric widgets and chart headers with trend badges, modern icons, and descriptions — following TailAdmin's built-in style conventions.

## Metrics Widget Pattern

```blade
<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
    <!-- Icon box -->
    <div class="flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="..."></path>
        </svg>
    </div>

    <div class="flex items-end justify-between mt-5">
        <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Metric Label</span>
            <h4 class="mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90">{{ $value }}</h4>
        </div>

        <!-- Trend badge: up (green) -->
        <span class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
            <svg class="fill-current" width="10" height="10" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.56462 1.62393C5.70193 1.47072 5.90135 1.37432 6.12329 1.37432C6.1236 1.37432 6.12391 1.37432 6.12422 1.37432C6.31631 1.37415 6.50845 1.44731 6.65505 1.59381L9.65514 4.5918C9.94814 4.88459 9.94831 5.35947 9.65552 5.65246C9.36273 5.94546 8.88785 5.94562 8.59486 5.65283L6.87329 3.93247L6.87329 10.125C6.87329 10.5392 6.53751 10.875 6.12329 10.875C5.70908 10.875 5.37329 10.5392 5.37329 10.125L5.37329 3.93578L3.65516 5.65282C3.36218 5.94562 2.8873 5.94547 2.5945 5.65248C2.3017 5.35949 2.30185 4.88462 2.59484 4.59182L5.56462 1.62393Z" fill="currentColor"/>
            </svg>
            +3.8%
        </span>

        <!-- Trend badge: down (red) -->
        <span class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
            <svg class="fill-current" width="10" height="10" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C5.8736 10.6257 5.8739 10.6257 5.87421 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z" fill="currentColor"/>
            </svg>
            -1.2%
        </span>
    </div>

    <!-- Description line -->
    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Description text explaining the metric</p>
</div>
```

## Chart Header Pattern

Wrap the existing `<h3>` in a `<div class="mb-4">` so a description line can sit below the title:

```blade
<div class="mb-4">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center gap-2">
        <svg class="w-5 h-5 text-brand-500" ...></svg>
        Chart Title
    </h3>
    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Brief chart description</p>
</div>
```

## Key Classes Reference

| Element | Classes |
|---|---|
| Widget container | `rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6` |
| Icon box | `flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl dark:bg-gray-800 text-gray-800 dark:text-white/90` |
| Metric value | `mt-2 font-bold text-gray-800 text-title-sm dark:text-white/90` |
| Trend badge (up) | `flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500` |
| Trend badge (down) | `flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500` |
| Description text | `mt-2 text-xs text-gray-400 dark:text-gray-500` |
| Chart title wrapper | `mb-4` (on parent div) + `mt-1` (on description) |

## SVG Icons

Use **stroke** (`fill="none" stroke="currentColor"`) icons for metric icon boxes — matches TailAdmin's updated style over the older `fill` approach. Use up/down arrow SVGs with `fill="currentColor"` inside trend badges.

## Notes

- Match tailwind color semantics: `success-*` for positive trends, `error-*` for negative trends
- Do not hardcode trend values; compute them on the controller side as Month-over-Month (MoM) trends (or custom periods) comparing the current total against the count at the end of the previous period.
- For dynamic trend rendering in Blade:
  ```blade
  @php
      $trendSign = $metricTrend >= 0 ? '+' : '';
      $trendColor = $metricTrend >= 0 ? 'success' : 'error';
  @endphp
  <span class="flex items-center gap-1 rounded-full bg-{{ $trendColor }}-50 py-0.5 pl-2 pr-2.5 text-xs font-medium text-{{ $trendColor }}-600 dark:bg-{{ $trendColor }}-500/15 dark:text-{{ $trendColor }}-500">
      @if($metricTrend >= 0)
          <!-- Up SVG -->
          <svg class="fill-current" ...></svg>
      @else
          <!-- Down SVG -->
          <svg class="fill-current" ...></svg>
      @endif
      {{ $trendSign }}{{ $metricTrend }}%
  </span>
  ```
- Controller-side MoM Calculation Pattern:
  ```php
  private function calculateTrend($current, $previous) {
      if ($previous === 0) {
          return $current > 0 ? 100 : 0;
      }
      return round((($current - $previous) / $previous) * 100, 1);
  }

  // Example metric trend retrieval:
  $current = Model::count();
  $previous = Model::where('created_at', '<', now()->startOfMonth())->count();
  $trend = $this->calculateTrend($current, $previous);
  ```
- Chart description goes **inside** the chart card, between the title and the chart container
- For multi-row charts (like Most Used Products with dropdown), put the description inside the left `<div>` side-by-side with the dropdown on the right
