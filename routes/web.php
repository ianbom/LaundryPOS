<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CurrentOutletController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\OrderWhatsAppController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\POSOrderController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\PublicTrackingController;
use App\Http\Controllers\ReportController;
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

Route::post('webhooks/midtrans', [MidtransWebhookController::class, 'handle'])->name('webhooks.midtrans');
Route::get('track/{trackingToken}', [PublicTrackingController::class, 'show'])->name('public.tracking.show');
Route::get('public/invoice/{trackingToken}', [PublicInvoiceController::class, 'show'])->name('public.invoice.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    Route::get('pos/orders/create', [POSOrderController::class, 'index'])->name('pos.orders.create');
    Route::post('pos/orders/qris', [POSOrderController::class, 'generateQris'])->name('pos.orders.qris');
    Route::get('pos/orders/qris/{intent}', [POSOrderController::class, 'qrisStatus'])->name('pos.orders.qris.status');
    Route::post('pos/orders', [POSOrderController::class, 'store'])->name('pos.orders.store');
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('orders/{order}/payments/cash', [PaymentController::class, 'payCash'])->name('orders.payments.cash');
    Route::post('orders/{order}/payments/qris', [PaymentController::class, 'generateQris'])->name('orders.payments.qris');
    Route::post('orders/{order}/whatsapp/payment-receipt', [OrderWhatsAppController::class, 'paymentReceipt'])->name('orders.whatsapp.payment-receipt');
    Route::post('orders/{order}/whatsapp/order-ready', [OrderWhatsAppController::class, 'orderReady'])->name('orders.whatsapp.order-ready');
    Route::post('orders/{order}/whatsapp/order-completed', [OrderWhatsAppController::class, 'orderCompleted'])->name('orders.whatsapp.order-completed');
    Route::post('orders/{order}/whatsapp/custom', [OrderWhatsAppController::class, 'custom'])->name('orders.whatsapp.custom');
    Route::patch('orders/{order}/status', [OrderStatusController::class, 'update'])->name('orders.status.update');
    Route::resource('orders', OrderController::class)->only(['index', 'show']);

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

    Route::get('reports/transactions', [ReportController::class, 'transactions'])->name('reports.transactions');
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/services', [ReportController::class, 'services'])->name('reports.services');
    Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});

require __DIR__.'/settings.php';
