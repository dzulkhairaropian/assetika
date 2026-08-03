# Refactor Route Imports implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor `routes/web.php` to use `use` imports for Asset and Consumable controllers instead of fully qualified class names.

**Architecture:** 
- Standard Laravel route file organization.
- Imports at the top of the file for better readability.

**Tech Stack:** Laravel 11.

---

### Task 1: Refactor Asset and Consumable Route Imports

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Add use statements for the controllers**

Add these to the top of `routes/web.php`:
```php
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\ConsumableRequestController;
use App\Http\Controllers\ConsumableTransactionController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetManagementController;
```

- [ ] **Step 2: Update Consumable routes to use short class names**

Replace `App\Http\Controllers\ConsumableController::class` with `ConsumableController::class`, etc.

- [ ] **Step 3: Update Asset routes to use short class names**

Replace `App\Http\Controllers\AssetController::class` with `AssetController::class`, etc.

- [ ] **Step 4: Commit the changes**

```bash
git add routes/web.php
git commit -m "refactor: use class imports in routes/web.php"
```
