### Architectural Refactoring Plan: IT Projects Management Module

#### 1. Create `ProjectService` (`app/Services/ProjectService.php`)
- `createProject(array $data): Project`
- `updateProject(Project $project, array $data): Project`
- `addTask(Project $project, array $data): ProjectTask`
- `updateTaskStatus(ProjectTask $task, array $data): array`

#### 2. Option Preparation in `ProjectController.php`
- In `index` and `show`, prepare `$userOpts`, `$statusOpts`, `$assetOpts`, and `$ticketOpts` as clean array format `[['id' => ..., 'text' => ...]]`.
- Inject `ProjectService` into `ProjectController`.

#### 3. View Modernization & Blade Parser Protection
- Update `pages/projects/index.blade.php` and `pages/projects/show.blade.php`:
  - Isolate `:selected` variables into top `@php` blocks.
  - Standardize searchable-select dropdowns to clean single-line syntax.

#### 4. Verification & Build
- Run `vendor/bin/pint`, `php artisan test`, and `npm run build`.

#### Files to Create / Modify
- `app/Services/ProjectService.php` (New)
- `app/Http/Controllers/ProjectController.php` (Refactored)
- `resources/views/pages/projects/index.blade.php` (Updated)
- `resources/views/pages/projects/show.blade.php` (Updated)