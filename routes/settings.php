<?php

use App\Http\Controllers\Settings\BusinessSettingController;
use App\Http\Controllers\Settings\IntegrationSettingController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\WhatsAppTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::get('settings/business', [BusinessSettingController::class, 'edit'])->name('settings.business.edit');
    Route::put('settings/business', [BusinessSettingController::class, 'update'])->name('settings.business.update');

    Route::get('settings/integrations', [IntegrationSettingController::class, 'edit'])->name('settings.integrations.edit');
    Route::put('settings/integrations', [IntegrationSettingController::class, 'update'])->name('settings.integrations.update');
    Route::post('settings/integrations/test-whatsapp', [IntegrationSettingController::class, 'testWhatsapp'])->name('settings.integrations.test-whatsapp');
    Route::post('settings/integrations/test-midtrans', [IntegrationSettingController::class, 'testMidtrans'])->name('settings.integrations.test-midtrans');

    Route::patch('settings/whatsapp-templates/{template}/toggle-active', [WhatsAppTemplateController::class, 'toggleActive'])->name('settings.whatsapp-templates.toggle-active');
    Route::post('settings/whatsapp-templates/{template}/preview', [WhatsAppTemplateController::class, 'preview'])->name('settings.whatsapp-templates.preview');
    Route::resource('settings/whatsapp-templates', WhatsAppTemplateController::class)
        ->except(['show'])
        ->parameters(['whatsapp-templates' => 'template'])
        ->names('settings.whatsapp-templates');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');
});
