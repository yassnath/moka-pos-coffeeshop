<?php

use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.reports.index');
    }

    return redirect()->route('pos.index');
});

Route::get('/dashboard', function () {
    return redirect()->to('/');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:kasir'])
    ->prefix('pos')
    ->name('pos.')
    ->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/open-bill', [PosController::class, 'saveOpenBill'])->name('open-bill.save');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/history', [PosController::class, 'history'])->name('history');
        Route::get('/history/{order}', [PosController::class, 'show'])->name('show');
    });

Route::middleware(['auth', 'role:admin|kasir'])->group(function () {
    Route::get('/orders/{order}/receipt', [PosController::class, 'receipt'])->name('orders.receipt');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.reports.index');
        })->name('index');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('addons', AddonController::class)->except(['show']);
        Route::resource('payment-methods', PaymentMethodController::class)->except(['show']);
        Route::resource('cashiers', CashierController::class)->except(['show']);

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/void', [AdminOrderController::class, 'void'])->name('orders.void');
    });

require __DIR__.'/auth.php';
