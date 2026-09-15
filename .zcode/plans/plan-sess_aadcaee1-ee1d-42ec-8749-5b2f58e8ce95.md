### Enterprise Architecture Standardization: Full Service-Layer Refactoring across All Modules

#### 1. Create Dedicated Service Classes
- **`ComponentService`** (`app/Services/ComponentService.php`):
  - `createBatch(array $data)`
  - `updateComponent(Component $component, array $data)`
  - `checkout(Component $component, Asset $asset, array $params)`
  - `checkin(Component $component, array $params)`
- **`ConsumableService`** (`app/Services/ConsumableService.php`):
  - `processCheckout(Consumable $consumable, User $toUser, int $qty, array $params)`
  - `processBulkCheckout(array $items, User $toUser, array $params)`
  - `processRestock(Consumable $consumable, int $qty, array $params)`
- **`TicketService`** (`app/Services/TicketService.php`):
  - `createTicket(array $data, ?UploadedFile $attachment = null)`
  - `updateTicket(Ticket $ticket, array $data, ?UploadedFile $attachment = null)`
- **`LicenseService`** (`app/Services/LicenseService.php`):
  - `checkoutSeat(License $license, array $params)`
  - `checkinSeat(LicenseSeat $seat, array $params)`
- **`KitService`** (`app/Services/KitService.php`):
  - `createKit(array $data)`
  - `checkoutKit(Kit $kit, array $params)`

#### 2. Controller Refactoring
- Inject each respective Service into `ComponentController`, `ConsumableController`, `TicketController`, `LicenseController`, and `KitController`.
- Remove raw DB transactions and heavy business logic from controllers, keeping controllers purely responsible for HTTP Request validation, service delegation, and view rendering.

#### 3. Verification & Build
- Run `vendor/bin/pint` to ensure code formatting matches project standards.
- Run `php artisan test` to verify no regressions in unit and feature tests.
- Run `npm run build`.

#### Files to Create / Modify
- `app/Services/ComponentService.php` (New)
- `app/Services/ConsumableService.php` (New)
- `app/Services/TicketService.php` (New)
- `app/Services/LicenseService.php` (New)
- `app/Services/KitService.php` (New)
- `app/Http/Controllers/ComponentController.php` (Refactored)
- `app/Http/Controllers/ConsumableController.php` (Refactored)
- `app/Http/Controllers/TicketController.php` (Refactored)
- `app/Http/Controllers/LicenseController.php` (Refactored)
- `app/Http/Controllers/KitController.php` (Refactored)