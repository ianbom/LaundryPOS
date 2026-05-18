<?php

use App\Http\Controllers\CurrentOutletController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCopyController;
use App\Http\Controllers\ServiceVariantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserOutletController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('current-outlet', [CurrentOutletController::class, 'show'])->name('current-outlet.show');
    Route::post('current-outlet', [CurrentOutletController::class, 'update'])->name('current-outlet.update');

    Route::patch('outlets/{outlet}/toggle-active', [OutletController::class, 'toggleActive'])->name('outlets.toggle-active');
    Route::patch('outlets/{outlet}/set-main', [OutletController::class, 'setMain'])->name('outlets.set-main');
    Route::resource('outlets', OutletController::class);

    Route::get('users/{user}/outlets', [UserOutletController::class, 'edit'])->name('users.outlets.edit');
    Route::put('users/{user}/outlets', [UserOutletController::class, 'update'])->name('users.outlets.update');
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserController::class);

    Route::resource('customers', CustomerController::class)->except(['show']);

    Route::patch('service-categories/{service_category}/toggle-active', [ServiceCategoryController::class, 'toggleActive'])->name('service-categories.toggle-active');
    Route::resource('service-categories', ServiceCategoryController::class)->except(['show']);

    Route::get('services/copy', [ServiceCopyController::class, 'create'])->name('services.copy.create');
    Route::post('services/copy', [ServiceCopyController::class, 'store'])->name('services.copy.store');
    Route::post('services/copy/preview', [ServiceCopyController::class, 'preview'])->name('services.copy.preview');
    Route::patch('services/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggle-active');
    Route::resource('services', ServiceController::class);

    Route::patch('services/{service}/variants/{variant}/toggle-active', [ServiceVariantController::class, 'toggleActive'])->name('services.variants.toggle-active');
    Route::resource('services.variants', ServiceVariantController::class)
        ->except(['show'])
        ->parameters(['variants' => 'variant']);
});

require __DIR__.'/settings.php';
