<?php

use App\Http\Controllers\Api\V1\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Api\V1\Admin\RecipeController;
use App\Http\Controllers\Api\V1\Admin\TableController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\UserPermissionController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Cashier\CashierController;
use App\Http\Controllers\Api\V1\Cashier\PaymentController;
use App\Http\Controllers\Api\V1\Customer\CatalogController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Inventory\IngredientController;
use App\Http\Controllers\Api\V1\Inventory\StockAdjustmentController;
use App\Http\Controllers\Api\V1\Inventory\StockMovementController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\Order\TransactionController;
use App\Http\Controllers\Api\V1\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierController;
use App\Http\Controllers\Api\V1\Report\AlertsController;
use App\Http\Controllers\Api\V1\Report\AuditLogController;
use App\Http\Controllers\Api\V1\Report\DashboardController;
use App\Http\Controllers\Api\V1\Report\SalesReportController;
use App\Http\Controllers\Api\V1\Station\StationController;
use Illuminate\Support\Facades\Route;

/*
 | Seluruh rute berada di bawah /api/v1 (prefix diatur di bootstrap/app.php).
 | Otorisasi: middleware `permission:<kunci>` (config/permissions.php) dan `station:read|act` untuk Barista/Kitchen.
 | Kontrak lengkap: docs/API_DOCUMENTATION.md dan docs/api/openapi.yaml.
 */

Route::get('health', HealthController::class);

// ---------------------------------------------------------------- Autentikasi
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'update']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

// ---------------------------------------------------------------- Pelanggan (tanpa login)
Route::prefix('customer')->middleware(['throttle:customer', 'table.token'])->group(function () {
    Route::get('tables/{qrToken}', [CatalogController::class, 'table']);
    Route::get('menus', [CatalogController::class, 'menus']);
    Route::post('cart/validate', [CatalogController::class, 'validateCart']);
    Route::get('payment-info', [CatalogController::class, 'paymentInfo']);
    Route::post('orders', [CustomerOrderController::class, 'store']);
    Route::get('orders/{publicToken}', [CustomerOrderController::class, 'show']);
    Route::post('orders/{publicToken}/cancel', [CustomerOrderController::class, 'cancel']);
});

// ---------------------------------------------------------------- Staf (bearer token)
Route::middleware(['auth:sanctum', 'active', 'throttle:staff'])->group(function () {
    Route::get('staff/alerts', AlertsController::class);

    // Kasir
    Route::prefix('cashier')->group(function () {
        Route::get('dashboard', [CashierController::class, 'dashboard'])->middleware('permission:dashboard.cashier');
        Route::middleware('permission:payments.verify')->group(function () {
            Route::get('orders', [CashierController::class, 'orders']);
            Route::get('orders/{order}', [CashierController::class, 'order']);
            Route::post('payments/{payment}/verify', [PaymentController::class, 'verify']);
            Route::post('payments/{payment}/reject', [PaymentController::class, 'reject']);
        });
        Route::get('transactions/{order}/receipt', [CashierController::class, 'receipt'])->middleware('permission:payments.verify,transactions.view');
    });

    // Barista & Kitchen (stasiun = parameter; akses ditegakkan EnsureStationAccess)
    Route::prefix('station/{station}')->group(function () {
        Route::middleware('station:read')->group(function () {
            Route::get('dashboard', [StationController::class, 'dashboard']);
            Route::get('tickets', [StationController::class, 'index']);
            Route::get('tickets/{ticket}', [StationController::class, 'show'])->whereNumber('ticket');
        });
        Route::middleware('station:act')->group(function () {
            Route::post('tickets/{ticket}/start', [StationController::class, 'start'])->whereNumber('ticket');
            Route::post('tickets/{ticket}/complete', [StationController::class, 'complete'])->whereNumber('ticket');
        });
    });

    // Monitoring pesanan & transaksi (Owner/Manager)
    Route::middleware('permission:orders.view')->group(function () {
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
    });
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('permission:orders.cancel');
    Route::get('transactions', [TransactionController::class, 'index'])->middleware('permission:transactions.view');

    // Admin: menu, resep, meja, pengguna, izin
    Route::prefix('admin')->group(function () {
        Route::get('menu-categories', [AdminMenuController::class, 'categories'])->middleware('permission:menus.view');
        Route::get('menus', [AdminMenuController::class, 'index'])->middleware('permission:menus.view');
        Route::get('menus/{menu}', [AdminMenuController::class, 'show'])->middleware('permission:menus.view');
        Route::get('menus/{menu}/recipe', [RecipeController::class, 'show'])->middleware('permission:menus.view');
        Route::middleware('permission:menus.manage')->group(function () {
            Route::post('menus', [AdminMenuController::class, 'store']);
            Route::put('menus/{menu}', [AdminMenuController::class, 'update']);
            Route::delete('menus/{menu}', [AdminMenuController::class, 'destroy']);
            Route::patch('menus/{menu}/availability', [AdminMenuController::class, 'availability']);
        });
        Route::put('menus/{menu}/recipe', [RecipeController::class, 'update'])->middleware('permission:recipes.manage');

        Route::middleware('permission:tables.manage')->group(function () {
            Route::get('tables', [TableController::class, 'index']);
            Route::post('tables', [TableController::class, 'store']);
            Route::put('tables/{table}', [TableController::class, 'update']);
            Route::delete('tables/{table}', [TableController::class, 'destroy']);
            Route::post('tables/{table}/regenerate-qr', [TableController::class, 'regenerateQr']);
        });

        Route::middleware('permission:users.manage')->group(function () {
            Route::get('users', [UserController::class, 'index']);
            Route::post('users', [UserController::class, 'store']);
            Route::put('users/{user}', [UserController::class, 'update']);
            Route::patch('users/{user}/status', [UserController::class, 'status']);
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
        });
        Route::middleware('permission:permissions.grant')->group(function () {
            Route::get('users/{user}/permissions', [UserPermissionController::class, 'show']);
            Route::put('users/{user}/permissions', [UserPermissionController::class, 'update']);
        });
    });

    // Inventori
    Route::prefix('inventory')->group(function () {
        Route::middleware('permission:ingredients.view')->group(function () {
            Route::get('ingredients', [IngredientController::class, 'index']);
            Route::get('summary', [IngredientController::class, 'summary']);
        });
        Route::middleware('permission:ingredients.manage')->group(function () {
            Route::post('ingredients', [IngredientController::class, 'store']);
            Route::put('ingredients/{ingredient}', [IngredientController::class, 'update']);
            Route::delete('ingredients/{ingredient}', [IngredientController::class, 'destroy']);
        });
        Route::get('stock-movements', [StockMovementController::class, 'index'])->middleware('permission:stock.view');
        Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->middleware('permission:stock.adjust');
    });

    // Pembelian & penerimaan barang
    Route::prefix('purchasing')->group(function () {
        Route::middleware('permission:purchases.view')->group(function () {
            Route::get('suppliers', [SupplierController::class, 'index']);
            Route::get('purchases', [PurchaseController::class, 'index']);
            Route::get('purchases/{purchase}', [PurchaseController::class, 'show']);
            Route::get('receipts/{receipt}', [GoodsReceiptController::class, 'show']);
        });
        Route::middleware('permission:suppliers.manage')->group(function () {
            Route::post('suppliers', [SupplierController::class, 'store']);
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update']);
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy']);
        });
        Route::middleware('permission:purchases.manage')->group(function () {
            Route::post('purchases', [PurchaseController::class, 'store']);
            Route::put('purchases/{purchase}', [PurchaseController::class, 'update']);
            Route::post('purchases/{purchase}/place', [PurchaseController::class, 'place']);
            Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel']);
        });
        Route::middleware('permission:purchases.receive')->group(function () {
            Route::post('purchases/{purchase}/receipts', [GoodsReceiptController::class, 'store']);
            Route::put('receipts/{receipt}', [GoodsReceiptController::class, 'update']);
            Route::post('receipts/{receipt}/confirm', [GoodsReceiptController::class, 'confirm']);
        });
    });

    // Dashboard, laporan, audit
    Route::get('dashboard/owner', [DashboardController::class, 'owner'])->middleware('permission:dashboard.owner');
    Route::get('dashboard/manager', [DashboardController::class, 'manager'])->middleware('permission:dashboard.manager');
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/sales', [SalesReportController::class, 'sales']);
        Route::get('reports/stock-usage', [SalesReportController::class, 'stockUsage']);
    });
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view');
});
