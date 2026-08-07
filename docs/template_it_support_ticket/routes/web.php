<?php

use App\Http\Controllers\Admin\AssetikaViewController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\SlaStatusController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Admin\SystemViewController;
use App\Http\Controllers\Admin\TicketStatusController;
use App\Http\Controllers\Admin\TicketTypeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Admin CRUD Routes
    Route::resource('admin/priorities', PriorityController::class, ['names' => 'admin.priorities']);
    Route::resource('admin/labels', LabelController::class, ['names' => 'admin.labels']);
    Route::resource('admin/sources', SourceController::class, ['names' => 'admin.sources']);
    Route::resource('admin/ticket-types', TicketTypeController::class, ['names' => 'admin.ticket-types']);
    Route::resource('admin/ticket-statuses', TicketStatusController::class, ['names' => 'admin.ticket-statuses']);
    Route::resource('admin/sla-statuses', SlaStatusController::class, ['names' => 'admin.sla-statuses']);

    // Admin Sync Data View
    Route::get('admin/sync/{table}', [AssetikaViewController::class, 'index'])->name('admin.sync.index');
    Route::get('admin/sync/{table}/{id}', [AssetikaViewController::class, 'show'])->name('admin.sync.show');

    // Tickets
    Route::resource('tickets', TicketController::class);

    // System & Logs
    Route::get('admin/system/comments', [SystemViewController::class, 'comments'])->name('admin.system.comments');
    Route::get('admin/system/histories', [SystemViewController::class, 'histories'])->name('admin.system.histories');
    Route::get('admin/system/attachments', [SystemViewController::class, 'attachments'])->name('admin.system.attachments');
    Route::get('admin/system/backup', [SystemViewController::class, 'backup'])->name('admin.system.backup');
    Route::post('admin/system/backup/run', [SystemViewController::class, 'runBackup'])->name('admin.system.backup.run');
});

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
