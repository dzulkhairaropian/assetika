### Dynamic Master Status Integration for Component Module

#### 1. Schema & Relationships
- **Migration**: Add `status_id` (foreign key `statuses.id`, nullable) to `components` table.
- **`Component` Model**:
  - Add `status_id` to `$fillable`.
  - Add `public function status(): BelongsTo { return $this->belongsTo(Status::class); }`.
- **`Status` Model**:
  - Add `public function components(): HasMany { return $this->hasMany(Component::class); }`.

#### 2. Controller Workflow (`ComponentController.php`)
- **`create` & `edit`**:
  - Load statuses under Master Category `IT Component` (`$statusOpts`).
  - Pre-select `Ready` status by default.
- **`store` & `update`**:
  - Validate `'status_id' => 'required|exists:statuses,id'`.
- **`checkout`**:
  - Dynamically find and assign the `Installed` status for `IT Component`.
- **`checkin`**:
  - Dynamically find and revert to the `Ready` status for `IT Component`.

#### 3. View Modernization
- **`create.blade.php` & `edit.blade.php`**:
  - Add `Status` (`status_id`) searchable-select dropdown loaded from `statuses` table.
- **`index.blade.php` & `show.blade.php`**:
  - Render status dynamically using `<x-ui.badge>` component with database-backed status name, color, and icon (`optional($component->status)->name`, `optional($component->status)->color`, `optional($component->status)->icon`).

#### 4. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Modify / Create
- `database/migrations/YYYY_MM_DD_HHMMSS_add_status_id_to_components_table.php` (New)
- `app/Models/Component.php`
- `app/Models/Status.php`
- `app/Http/Controllers/ComponentController.php`
- `resources/views/pages/components/index.blade.php`
- `resources/views/pages/components/show.blade.php`
- `resources/views/pages/components/create.blade.php`
- `resources/views/pages/components/edit.blade.php`