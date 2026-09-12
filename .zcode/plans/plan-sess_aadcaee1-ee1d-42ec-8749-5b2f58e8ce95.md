### Fix: Update `components.checkin` Route Definition in `routes/web.php`

#### Root Cause
In `routes/web.php` line 175, the checkin route was defined as:
`Route::post('components/{component}/checkin/{asset}', [ComponentController::class, 'checkin'])->name('components.checkin');`
Because Component is now an individual trackable unit with its `assigned_asset_id` stored directly on the component record, `ComponentController@checkin` only requires the `component` parameter. Calling `route('components.checkin', $component)` triggers a `UrlGenerationException: Missing parameter: asset`.

#### Solution Plan
1. In `routes/web.php`, update the route definition to:
   `Route::post('components/{component}/checkin', [ComponentController::class, 'checkin'])->name('components.checkin');`
2. Test loading `/components/2` and verify the checkin action works without errors.
3. Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify
- `routes/web.php`