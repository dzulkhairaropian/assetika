# IT Asset Management & BAST Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete CRUD and management system for IT Assets, including assignment (checkout) and return (checkin) with professional BAST document generation.

**Architecture:** 
- `AssetController` for standard CRUD.
- `AssetManagementController` for movement (Checkout/Checkin) and BAST.
- Integration with existing `Asset` and `AssetCheckout` models.
- TailAdmin style UI components.

**Tech Stack:** Laravel 11, Tailwind CSS, Alpine.js, Lucide Icons.

---

### Task 1: Routing Setup

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add Asset and Management routes**

Add these routes inside the `auth` middleware group:
```php
// IT Asset Management
Route::resource('assets', App\Http\Controllers\AssetController::class);
Route::prefix('assets/{asset}')->group(function () {
    Route::get('checkout', [App\Http\Controllers\AssetManagementController::class, 'showCheckout'])->name('assets.checkout');
    Route::post('checkout', [App\Http\Controllers\AssetManagementController::class, 'processCheckout'])->name('assets.checkout.process');
    Route::get('checkin', [App\Http\Controllers\AssetManagementController::class, 'showCheckin'])->name('assets.checkin');
    Route::post('checkin', [App\Http\Controllers\AssetManagementController::class, 'processCheckin'])->name('assets.checkin.process');
    Route::get('bast/{checkout}', [App\Http\Controllers\AssetManagementController::class, 'showBAST'])->name('assets.bast');
});
```

- [ ] **Step 2: Verify routes exist**

Run: `php artisan route:list --name=assets`
Expected: List of routes starting with `assets.`

- [ ] **Step 3: Commit**

```bash
git add routes/web.php
git commit -m "chore: add IT asset management routes"
```

---

### Task 2: Controller Implementation (CRUD)

**Files:**
- Create: `app/Http/Controllers/AssetController.php`

- [ ] **Step 1: Create the controller with standard CRUD methods**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Product;
use App\Models\Status;
use App\Models\Vendor;
use App\Models\OperatingSystem;
use App\Models\Department;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with(['product.category', 'status', 'assignedToUser', 'department']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'like', "%$search%")
                  ->orWhere('serial_number', 'like', "%$search%")
                  ->orWhere('device_name', 'like', "%$search%");
            });
        }

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $assets = $query->latest()->paginate(10)->withQueryString();
        $statuses = Status::all();

        return view('pages.assets.index', compact('assets', 'statuses'));
    }

    public function create()
    {
        $products = Product::with('category')->get();
        $statuses = Status::all();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::all();
        
        return view('pages.assets.create', compact('products', 'statuses', 'vendors', 'operatingSystems', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'status_id' => 'required|exists:statuses,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'operating_system_id' => 'nullable|exists:operating_systems,id',
            'department_id' => 'nullable|exists:departments,id',
            'serial_number' => 'nullable|string|unique:assets,serial_number',
            'product_number' => 'nullable|string',
            'specification' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'warranty_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        Asset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['product.category', 'status', 'vendor', 'operatingSystem', 'department', 'location', 'checkoutHistory.checkedOutToUser']);
        return view('pages.assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $products = Product::with('category')->get();
        $statuses = Status::all();
        $vendors = Vendor::all();
        $operatingSystems = OperatingSystem::all();
        $departments = Department::all();

        return view('pages.assets.edit', compact('asset', 'products', 'statuses', 'vendors', 'operatingSystems', 'departments'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'status_id' => 'required|exists:statuses,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'operating_system_id' => 'nullable|exists:operating_systems,id',
            'department_id' => 'nullable|exists:departments,id',
            'serial_number' => 'nullable|string|unique:assets,serial_number,' . $asset->id,
            'product_number' => 'nullable|string',
            'specification' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'warranty_expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/AssetController.php
git commit -m "feat: implement AssetController"
```

---

### Task 3: Controller Implementation (Management)

**Files:**
- Create: `app/Http/Controllers/AssetManagementController.php`

- [ ] **Step 1: Implement Checkout and Checkin logic**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCheckout;
use App\Models\User;
use App\Models\Location;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AssetManagementController extends Controller
{
    public function showCheckout(Asset $asset)
    {
        if ($asset->isCurrentlyCheckedOut()) {
            return redirect()->back()->with('error', 'Asset is already checked out.');
        }

        $users = User::all();
        $locations = Location::all();
        $departments = Department::all();
        $supervisors = User::role('Admin')->get(); // Adjust role name as needed

        return view('pages.assets.management.checkout', compact('asset', 'users', 'locations', 'departments', 'supervisors'));
    }

    public function processCheckout(Request $request, Asset $asset)
    {
        $request->validate([
            'checked_out_to_user_id' => 'required|exists:users,id',
            'location_id' => 'required|exists:locations,id',
            'department_id' => 'required|exists:departments,id',
            'supervisor_user_id' => 'nullable|exists:users,id',
            'checked_out_at' => 'required|date',
            'checkout_notes' => 'nullable|string',
            'checkout_evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $evidencePath = null;
        if ($request->hasFile('checkout_evidence')) {
            $evidencePath = $request->file('checkout_evidence')->store('checkouts', 'public');
        }

        $checkout = AssetCheckout::create([
            'asset_id' => $asset->id,
            'checked_out_by_user_id' => auth()->id(),
            'checked_out_to_user_id' => $request->checked_out_to_user_id,
            'supervisor_user_id' => $request->supervisor_user_id,
            'department_id' => $request->department_id,
            'location_id' => $request->location_id,
            'checked_out_at' => $request->checked_out_at,
            'checkout_notes' => $request->checkout_notes,
            'checkout_evidence_file_path' => $evidencePath,
            'checkout_evidence_file_name' => $request->file('checkout_evidence')?->getClientOriginalName(),
        ]);

        $asset->update([
            'current_checkout_id' => $checkout->id,
            'assigned_to_user_id' => $request->checked_out_to_user_id,
            'location_id' => $request->location_id,
            'department_id' => $request->department_id,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked out successfully.');
    }

    public function showCheckin(Asset $asset)
    {
        if (!$asset->isCurrentlyCheckedOut()) {
            return redirect()->back()->with('error', 'Asset is not checked out.');
        }

        return view('pages.assets.management.checkin', compact('asset'));
    }

    public function processCheckin(Request $request, Asset $asset)
    {
        $request->validate([
            'checked_in_at' => 'required|date',
            'condition_report' => 'required|string',
            'checkin_notes' => 'nullable|string',
            'checkin_evidence' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ]);

        $checkout = $asset->currentCheckout;

        $evidencePath = null;
        if ($request->hasFile('checkin_evidence')) {
            $evidencePath = $request->file('checkin_evidence')->store('checkins', 'public');
        }

        $checkout->update([
            'checked_in_at' => $request->checked_in_at,
            'checked_in_by_user_id' => auth()->id(),
            'condition_report' => $request->condition_report,
            'checkin_notes' => $request->checkin_notes,
            'checkin_evidence_file_path' => $evidencePath,
            'checkin_evidence_file_name' => $request->file('checkin_evidence')?->getClientOriginalName(),
        ]);

        $asset->update([
            'current_checkout_id' => null,
            'assigned_to_user_id' => null,
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function showBAST(Asset $asset, AssetCheckout $checkout)
    {
        return view('pages.assets.management.bast', compact('asset', 'checkout'));
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/AssetManagementController.php
git commit -m "feat: implement AssetManagementController"
```

---

### Task 4: UI Development (Views Part 1: Index)

**Files:**
- Create: `resources/views/pages/assets/index.blade.php`

- [ ] **Step 1: Create Index View with TailAdmin style**

(Similar to `pages.users.index`, but with asset-specific columns and badges)

- [ ] **Step 2: Commit**

```bash
git add resources/views/pages/assets/index.blade.php
git commit -m "feat: add asset index view"
```

---

### Task 5: UI Development (Views Part 2: Create/Edit)

**Files:**
- Create: `resources/views/pages/assets/create.blade.php`
- Create: `resources/views/pages/assets/edit.blade.php`

- [ ] **Step 1: Create forms with logical sections (General, Tech, Financial)**

- [ ] **Step 2: Commit**

```bash
git add resources/views/pages/assets/create.blade.php resources/views/pages/assets/edit.blade.php
git commit -m "feat: add asset create/edit views"
```

---

### Task 6: UI Development (Views Part 3: Show & History)

**Files:**
- Create: `resources/views/pages/assets/show.blade.php`

- [ ] **Step 1: Create detail view with technical specs and movement history tab**

- [ ] **Step 2: Commit**

```bash
git add resources/views/pages/assets/show.blade.php
git commit -m "feat: add asset show view"
```

---

### Task 7: UI Development (Views Part 4: Checkout/Checkin/BAST)

**Files:**
- Create: `resources/views/pages/assets/management/checkout.blade.php`
- Create: `resources/views/pages/assets/management/checkin.blade.php`
- Create: `resources/views/pages/assets/management/bast.blade.php`

- [ ] **Step 1: Implement management views**

- [ ] **Step 2: Implement BAST document view (Printable)**

- [ ] **Step 3: Commit**

```bash
git add resources/views/pages/assets/management/*.blade.php
git commit -m "feat: add asset management and BAST views"
```

---

### Task 8: Verification

- [ ] **Step 1: Verify Asset Creation**
- [ ] **Step 2: Verify Asset Checkout and BAST view**
- [ ] **Step 3: Verify Asset Checkin**
- [ ] **Step 4: Verify History tab in Detail view**
