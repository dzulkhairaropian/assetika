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
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('signin', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('signin', [LoginController::class, 'login']);
    
    Route::get('signup', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('signup', [RegisterController::class, 'register']);
});

Route::get('scan/asset/{asset}', [AssetController::class, 'publicScan'])->name('assets.public-scan');

// New route for public asset view (canonical URL)
Route::get('asset/{asset}', [AssetController::class, 'publicScan'])->name('assets.public-view');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('ga-dashboard', [GaDashboardController::class, 'index'])->name('ga-dashboard');
    
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
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // Backup Management Routes
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups/create', [BackupController::class, 'create'])->name('backups.create');
    Route::get('backups/download/{fileName}', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('backups/{fileName}', [BackupController::class, 'destroy'])->name('backups.destroy');
    Route::post('backups/settings', [BackupController::class, 'updateSettings'])->name('backups.settings');
    
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
