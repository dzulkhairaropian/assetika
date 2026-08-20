<?php

use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\Master\PositionController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\BrandController;
use App\Http\Controllers\Master\MasterCategoryController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\StatusController;
use App\Http\Controllers\Master\VendorController;
use App\Http\Controllers\Master\OperatingSystemController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\CustomFieldController;
use App\Http\Controllers\Master\FieldsetController;
use App\Http\Controllers\Master\DepreciationController;
use App\Http\Controllers\Master\EulaController;
use App\Http\Controllers\Master\MaintenanceTypeController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\AssetLabelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\StockCountController;
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ConsumableController;
use App\Http\Controllers\ConsumableRequestController;
use App\Http\Controllers\ConsumableTransactionController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetManagementController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\GaDashboardController;
use App\Http\Controllers\TicketDashboardController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\TicketStatusController;
use App\Http\Controllers\Admin\TicketTypeController;
use App\Http\Controllers\Admin\SlaStatusController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\PasswordResetController;

Route::middleware('guest')->group(function () {
    Route::get('signin', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('signin', [LoginController::class, 'login']);
    
    Route::get('signup', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('signup', [RegisterController::class, 'register']);

    Route::get('forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::get('scan/asset/{asset}', [AssetController::class, 'publicScan'])->name('assets.public-scan');

// New route for public asset view (canonical URL)
Route::get('asset/{asset}', [AssetController::class, 'publicScan'])->name('assets.public-view');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('ga-dashboard', [GaDashboardController::class, 'index'])->name('ga-dashboard');
    Route::get('ticket-dashboard', [TicketDashboardController::class, 'index'])->name('ticket-dashboard');
    Route::post('tickets/{ticket}/comment', [\App\Http\Controllers\TicketController::class, 'comment'])->name('tickets.comment');
    Route::post('tickets/bulk-delete', [\App\Http\Controllers\TicketController::class, 'bulkDestroy'])->name('tickets.bulk-delete');
    Route::resource('tickets', \App\Http\Controllers\TicketController::class);
    
    // Master Data Routes
    Route::post('companies/bulk-delete', [CompanyController::class, 'bulkDestroy'])->name('companies.bulk-delete');
    Route::post('departments/bulk-delete', [DepartmentController::class, 'bulkDestroy'])->name('departments.bulk-delete');
    Route::post('positions/bulk-delete', [PositionController::class, 'bulkDestroy'])->name('positions.bulk-delete');
    Route::post('locations/bulk-delete', [LocationController::class, 'bulkDestroy'])->name('locations.bulk-delete');
    Route::post('brands/bulk-delete', [BrandController::class, 'bulkDestroy'])->name('brands.bulk-delete');
    Route::post('master-categories/bulk-delete', [MasterCategoryController::class, 'bulkDestroy'])->name('master-categories.bulk-delete');
    Route::post('categories/bulk-delete', [CategoryController::class, 'bulkDestroy'])->name('categories.bulk-delete');
    Route::post('statuses/bulk-delete', [StatusController::class, 'bulkDestroy'])->name('statuses.bulk-delete');
    Route::post('vendors/bulk-delete', [VendorController::class, 'bulkDestroy'])->name('vendors.bulk-delete');
    Route::post('operating-systems/bulk-delete', [OperatingSystemController::class, 'bulkDestroy'])->name('operating-systems.bulk-delete');
    Route::post('products/bulk-delete', [ProductController::class, 'bulkDestroy'])->name('products.bulk-delete');
    Route::post('custom-fields/bulk-delete', [CustomFieldController::class, 'bulkDestroy'])->name('custom-fields.bulk-delete');
    Route::post('fieldsets/bulk-delete', [FieldsetController::class, 'bulkDestroy'])->name('fieldsets.bulk-delete');
    Route::post('components/bulk-delete', [ComponentController::class, 'bulkDestroy'])->name('components.bulk-delete');
    Route::post('licenses/bulk-delete', [LicenseController::class, 'bulkDestroy'])->name('licenses.bulk-delete');
    Route::post('kits/bulk-delete', [KitController::class, 'bulkDestroy'])->name('kits.bulk-delete');
    Route::post('depreciations/bulk-delete', [DepreciationController::class, 'bulkDestroy'])->name('depreciations.bulk-delete');
    Route::post('eulas/bulk-delete', [EulaController::class, 'bulkDestroy'])->name('eulas.bulk-delete');
    Route::post('maintenance-types/bulk-delete', [MaintenanceTypeController::class, 'bulkDestroy'])->name('maintenance-types.bulk-delete');

    Route::resource('companies', CompanyController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('master-categories', MasterCategoryController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('statuses', StatusController::class);
    Route::resource('vendors', VendorController::class);
    Route::resource('operating-systems', OperatingSystemController::class);
    Route::resource('products', ProductController::class);
    Route::resource('custom-fields', CustomFieldController::class);
    Route::resource('fieldsets', FieldsetController::class);
    Route::resource('depreciations', DepreciationController::class);
    Route::resource('eulas', EulaController::class);
    Route::resource('maintenance-types', MaintenanceTypeController::class);

    // Consumable Management Routes
    Route::get('consumables/bulk-checkout', [ConsumableController::class, 'bulkCheckoutForm'])->name('consumables.bulk-checkout');
    Route::post('consumables/bulk-checkout', [ConsumableController::class, 'processBulkCheckout'])->name('consumables.process-bulk-checkout');
    Route::resource('consumables', ConsumableController::class);
    Route::get('consumables/{consumable}/restock', [ConsumableController::class, 'restock'])->name('consumables.restock');
    Route::post('consumables/{consumable}/restock', [ConsumableController::class, 'processRestock']);
    Route::get('consumables/{consumable}/checkout', [ConsumableController::class, 'checkout'])->name('consumables.checkout');
    Route::post('consumables/{consumable}/checkout', [ConsumableController::class, 'processCheckout']);

    Route::resource('consumable-requests', ConsumableRequestController::class);
    Route::post('consumable-requests/{consumable_request}/process', [ConsumableRequestController::class, 'process'])->name('consumable-requests.process');

    Route::get('consumable-transactions/export', [ConsumableTransactionController::class, 'export'])->name('consumable-transactions.export');
    Route::get('consumable-transactions', [ConsumableTransactionController::class, 'index'])->name('consumable-transactions.index');

    // IT Asset Management
    Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
    Route::get('my-assets', [AssetController::class, 'myAssets'])->name('assets.my-assets');
    Route::post('assets/bulk-delete', [AssetController::class, 'bulkDestroy'])->name('assets.bulk-delete');
    Route::post('assets/bulk-edit', [AssetController::class, 'bulkEdit'])->name('assets.bulk-edit');
    Route::get('assets/bulk-checkout', [AssetController::class, 'bulkCheckoutForm'])->name('assets.bulk-checkout');
    Route::post('assets/bulk-checkout', [AssetController::class, 'bulkCheckout'])->name('assets.bulk-checkout.process');
    Route::resource('assets', AssetController::class);
    Route::get('asset-transactions/export', [\App\Http\Controllers\AssetTransactionController::class, 'export'])->name('asset-transactions.export');
    Route::get('asset-transactions', [\App\Http\Controllers\AssetTransactionController::class, 'index'])->name('asset-transactions.index');
    Route::prefix('assets/{asset}')->group(function () {
        Route::get('checkout', [AssetManagementController::class, 'showCheckout'])->name('assets.checkout');
        Route::post('checkout', [AssetManagementController::class, 'processCheckout'])->name('assets.checkout.process');
        Route::get('checkin', [AssetManagementController::class, 'showCheckin'])->name('assets.checkin');
        Route::post('checkin', [AssetManagementController::class, 'processCheckin'])->name('assets.checkin.process');
        Route::get('bast/{checkout}', [AssetManagementController::class, 'showBAST'])->name('assets.bast');
        Route::get('maintenance', [AssetManagementController::class, 'showMaintenance'])->name('assets.maintenance');
        Route::post('maintenance', [AssetManagementController::class, 'processMaintenance'])->name('assets.maintenance.process');
        Route::get('maintenance/{maintenance}/complete', [AssetManagementController::class, 'showCompleteMaintenance'])->name('assets.maintenance.complete');
        Route::post('maintenance/{maintenance}/complete', [AssetManagementController::class, 'processCompleteMaintenance'])->name('assets.maintenance.complete.process');
    });

    // Component Management
    Route::resource('components', ComponentController::class);
    Route::post('components/{component}/checkout', [ComponentController::class, 'checkout'])->name('components.checkout');
    Route::post('components/{component}/checkin/{asset}', [ComponentController::class, 'checkin'])->name('components.checkin');

    // License Management
    Route::resource('licenses', LicenseController::class);
    Route::post('licenses/{license}/checkout', [LicenseController::class, 'checkout'])->name('licenses.checkout');
    Route::post('licenses/{license}/checkin/{seat}', [LicenseController::class, 'checkin'])->name('licenses.checkin');

    // Kit Management
    Route::resource('kits', KitController::class);
    Route::get('kits/{kit}/checkout-form', [KitController::class, 'checkoutForm'])->name('kits.checkout-form');
    Route::post('kits/{kit}/checkout', [KitController::class, 'checkout'])->name('kits.checkout');

    // Labels
    Route::get('labels', [AssetLabelController::class, 'index'])->name('labels.index');
    Route::post('labels/generate', [AssetLabelController::class, 'generate'])->name('labels.generate');
    Route::get('labels/{asset}/print', [AssetLabelController::class, 'generateSingle'])->name('labels.print-single');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/activity', [ReportController::class, 'activity'])->name('reports.activity');
    Route::get('reports/assets', [ReportController::class, 'assets'])->name('reports.assets');
    Route::get('reports/assets/export', [ReportController::class, 'exportAssets'])->name('reports.assets.export');
    Route::get('reports/depreciation', [ReportController::class, 'depreciation'])->name('reports.depreciation');
    Route::get('reports/custom', [ReportController::class, 'custom'])->name('reports.custom');

    // Import
    Route::get('import', [ImportController::class, 'index'])->name('import.index');
    Route::post('import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('import/process', [ImportController::class, 'process'])->name('import.process');
    Route::get('import/template/{type}', [ImportController::class, 'downloadTemplate'])->name('import.template');

    // Stock Count / Audit
    Route::resource('stock-counts', StockCountController::class);
    Route::get('stock-counts/{stockCount}/add-assets', [StockCountController::class, 'addAssets'])->name('stock-counts.add-assets');
    Route::post('stock-counts/{stockCount}/add-assets', [StockCountController::class, 'processAddAssets'])->name('stock-counts.process-add-assets');
    Route::post('stock-counts/{stockCount}/complete', [StockCountController::class, 'markComplete'])->name('stock-counts.complete');
    Route::post('stock-counts/{stockCount}/items/{item}/verify', [StockCountController::class, 'verifyItem'])->name('stock-counts.items.verify');

    // Self-Service Requests
    Route::get('requestable-assets', [AssetRequestController::class, 'catalog'])->name('requestable-assets.catalog');
    Route::post('requestable-assets', [AssetRequestController::class, 'store'])->name('asset-requests.store');
    Route::get('asset-requests', [AssetRequestController::class, 'index'])->name('asset-requests.index');
    Route::post('asset-requests/{assetRequest}/process', [AssetRequestController::class, 'process'])->name('asset-requests.process');

    // Profile Routes
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Notification Routes
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // GA Asset Management Routes
    Route::resource('ga-categories', \App\Http\Controllers\GaAssetCategoryController::class);
    Route::post('ga-assets/bulk-delete', [\App\Http\Controllers\GaAssetController::class, 'bulkDestroy'])->name('ga-assets.bulk-delete');
    Route::resource('ga-assets', \App\Http\Controllers\GaAssetController::class);
    Route::post('ga-assets/{asset}/checkout', [\App\Http\Controllers\GaAssetController::class, 'checkout'])->name('ga-assets.checkout');
    Route::post('ga-assets/{asset}/checkin', [\App\Http\Controllers\GaAssetController::class, 'checkin'])->name('ga-assets.checkin');
    Route::post('ga-assets/{asset}/maintenance', [\App\Http\Controllers\GaAssetController::class, 'maintenance'])->name('ga-assets.maintenance');

    // User & Role Management
    Route::post('users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk-delete');
    Route::post('users/bulk-status', [UserController::class, 'bulkStatus'])->name('users.bulk-status');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // Backup Management Routes
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('backups/download/{fileName}', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('backups/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('backups/settings', [BackupController::class, 'updateSettings'])->name('backups.settings');

    // Admin Bulk Delete Routes (must be before resource routes)
    Route::post('admin/priorities/bulk-delete', [PriorityController::class, 'bulkDestroy'])->name('admin.priorities.bulk-delete');
    Route::post('admin/labels/bulk-delete', [LabelController::class, 'bulkDestroy'])->name('admin.labels.bulk-delete');
    Route::post('admin/sources/bulk-delete', [SourceController::class, 'bulkDestroy'])->name('admin.sources.bulk-delete');
    Route::post('admin/ticket-types/bulk-delete', [TicketTypeController::class, 'bulkDestroy'])->name('admin.ticket-types.bulk-delete');
    Route::post('admin/ticket-statuses/bulk-delete', [TicketStatusController::class, 'bulkDestroy'])->name('admin.ticket-statuses.bulk-delete');
    Route::post('admin/sla-statuses/bulk-delete', [SlaStatusController::class, 'bulkDestroy'])->name('admin.sla-statuses.bulk-delete');

    // Admin CRUD Routes (Ticketing)
    Route::get('admin/ticket-categories', [\App\Http\Controllers\Admin\TicketCategoryController::class, 'index'])->name('admin.ticket-categories.index');
    Route::post('admin/ticket-categories', [\App\Http\Controllers\Admin\TicketCategoryController::class, 'storeCategory'])->name('admin.ticket-categories.store');
    Route::delete('admin/ticket-categories/{category}', [\App\Http\Controllers\Admin\TicketCategoryController::class, 'destroyCategory'])->name('admin.ticket-categories.destroy');
    Route::post('admin/ticket-subcategories', [\App\Http\Controllers\Admin\TicketCategoryController::class, 'storeSubcategory'])->name('admin.ticket-subcategories.store');
    Route::delete('admin/ticket-subcategories/{subcategory}', [\App\Http\Controllers\Admin\TicketCategoryController::class, 'destroySubcategory'])->name('admin.ticket-subcategories.destroy');
    Route::get('api/ticket-categories/{category}/subcategories', [\App\Http\Controllers\Admin\TicketCategoryController::class, 'getSubcategories'])->name('api.ticket-categories.subcategories');

    Route::resource('admin/priorities', PriorityController::class, ['names' => 'admin.priorities']);
    Route::resource('admin/labels', LabelController::class, ['names' => 'admin.labels']);
    Route::resource('admin/sources', SourceController::class, ['names' => 'admin.sources']);
    Route::resource('admin/ticket-types', TicketTypeController::class, ['names' => 'admin.ticket-types']);
    Route::resource('admin/ticket-statuses', TicketStatusController::class, ['names' => 'admin.ticket-statuses']);
    Route::resource('admin/sla-statuses', SlaStatusController::class, ['names' => 'admin.sla-statuses']);
    
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
