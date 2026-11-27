<?php

use App\Http\Controllers\Api\V1\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Api\V1\Admin\TableController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\UserPermissionController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceController;
use App\Http\Controllers\Api\V1\Attendance\AttendancePhotoController;
use App\Http\Controllers\Api\V1\Cashier\CashierController;
use App\Http\Controllers\Api\V1\Cashier\PaymentController;
use App\Http\Controllers\Api\V1\Cashier\TableSessionController as CashierTableSessionController;
use App\Http\Controllers\Api\V1\Cashier\PosOrderController;
use App\Http\Controllers\Api\V1\Customer\CatalogController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\TableSessionController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\Order\TransactionController;
use App\Http\Controllers\Api\V1\Report\AlertsController;
use App\Http\Controllers\Api\V1\Report\AuditLogController;
use App\Http\Controllers\Api\V1\Report\DashboardController;
use App\Http\Controllers\Api\V1\Report\SalesReportController;
use App\Http\Controllers\Api\V1\RefundController;
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
Route::prefix('customer')->middleware('throttle:customer')->group(function () {
    Route::get('tables/{qrToken}', [CatalogController::class, 'table']);
    Route::post('sessions/open', [TableSessionController::class, 'open']);
    Route::get('menus', [CatalogController::class, 'menus']);
    Route::get('payment-info', [CatalogController::class, 'paymentInfo']);
    Route::middleware('table.session')->group(function () {
        Route::get('sessions/current', [TableSessionController::class, 'current']);
        Route::post('cart/validate', [CatalogController::class, 'validateCart']);
        Route::post('sessions/orders', [CustomerOrderController::class, 'store']);
        // Keep existing customer clients working while requiring the session credential.
        Route::post('orders', [CustomerOrderController::class, 'store']);
        Route::get('orders/{publicToken}', [CustomerOrderController::class, 'show']);
        Route::post('sessions/orders/{publicToken}/cancel', [CustomerOrderController::class, 'cancel']);
        Route::post('sessions/orders/{publicToken}/payment-proof', [CustomerOrderController::class, 'uploadProof'])->middleware('throttle:customer-proof');
        Route::post('orders/{publicToken}/cancel', [CustomerOrderController::class, 'cancel']);
        Route::post('orders/{publicToken}/payment-proof', [CustomerOrderController::class, 'uploadProof'])->middleware('throttle:customer-proof');
    });
});

// ---------------------------------------------------------------- Staf (bearer token)
Route::middleware(['auth:sanctum', 'active', 'throttle:staff'])->group(function () {
    Route::get('staff/alerts', AlertsController::class);

    // Absensi foto/GPS; foto hanya dapat diakses staf pemilik atau pemegang izin view_all.
    Route::prefix('attendance')->group(function () {
        Route::middleware('permission:attendance.record')->group(function () {
            Route::get('today-status', [AttendanceController::class, 'todayStatus']);
            Route::post('check-in', [AttendanceController::class, 'checkIn']);
            Route::post('check-out', [AttendanceController::class, 'checkOut']);
            Route::get('my-history', [AttendanceController::class, 'myHistory']);
        });
        Route::get('list', [AttendanceController::class, 'list'])->middleware('permission:attendance.view_all');
        Route::patch('{attendance}', [AttendanceController::class, 'correct'])->whereNumber('attendance')->middleware('permission:attendance.report');
        Route::get('events/{event}/photo', AttendancePhotoController::class)->middleware('permission:attendance.record,attendance.report');
    });

    // Kasir
    Route::prefix('cashier')->group(function () {
        Route::get('dashboard', [CashierController::class, 'dashboard'])->middleware('permission:dashboard.cashier');
        Route::get('tables', [CashierController::class, 'tables'])->middleware('permission:orders.create_pos');
        Route::post('pos/orders', [PosOrderController::class, 'store'])->middleware('permission:orders.create_pos');
        Route::post('orders/{order}/complete', [CashierController::class, 'completeOrder'])->middleware('permission:payments.verify');
        Route::middleware('permission:payments.verify')->group(function () {
            Route::get('sessions', [CashierTableSessionController::class, 'index']);
            Route::get('sessions/{tableSession}', [CashierTableSessionController::class, 'show']);
            Route::post('sessions/{tableSession}/close', [CashierTableSessionController::class, 'close']);
            Route::get('orders', [CashierController::class, 'orders']);
            Route::get('orders/{order}', [CashierController::class, 'order']);
            Route::post('payments/{payment}/verify', [PaymentController::class, 'verify']);
            Route::post('payments/{payment}/reject', [PaymentController::class, 'reject']);
            Route::get('payments/{payment}/proof', [PaymentController::class, 'proof']);
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
            Route::post('tickets/{ticket}/items/{detail}/start', [StationController::class, 'startItem'])->whereNumber('ticket')->whereNumber('detail');
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
    Route::post('orders/{order}/items/cancel', [OrderController::class, 'cancelItem'])->middleware('permission:orders.cancel');
    Route::get('transactions', [TransactionController::class, 'index'])->middleware('permission:transactions.view');

    Route::get('refunds', [RefundController::class, 'index'])->middleware('permission:refund.view,refund.review');
    Route::post('refunds', [RefundController::class, 'createManual'])->middleware('permission:refund.request');
    Route::post('refunds/{refund}/decision', [RefundController::class, 'decide'])->middleware('permission:refund.review');
    Route::post('refunds/{refund}/withdraw', [RefundController::class, 'withdraw'])->middleware('permission:refund.view');
    Route::post('refunds/{refund}/revoke', [RefundController::class, 'revoke'])->middleware('permission:refund.review');
    Route::post('refunds/{refund}/execute', [RefundController::class, 'execute'])->middleware('permission:refund.execute');
    Route::get('refunds/{refund}/proof', [RefundController::class, 'proof'])->middleware('permission:refund.view,refund.review');
    Route::post('refunds/{refund}/conflict-notes', [RefundController::class, 'conflictNote'])->middleware('permission:refund.conflict.document');
    Route::get('refunds/{refund}/conflict-notes', [RefundController::class, 'conflictNotes'])->middleware('permission:refund.review');
    Route::post('refund-conflict-notes/{note}/verify', [RefundController::class, 'verifyConflict'])->middleware('permission:refund.review');
    Route::get('refund-conflict-notes/{note}/proof', [RefundController::class, 'conflictProof'])->middleware('permission:refund.conflict.document,refund.review');
    Route::get('notifications', [RefundController::class, 'notifications'])->middleware('permission:notifications.view');
    Route::post('notifications/{notification}/read', [RefundController::class, 'readNotification'])->middleware('permission:notifications.view');

    // Admin: menu, meja, pengguna, izin
    Route::prefix('admin')->group(function () {
        Route::get('menu-categories', [AdminMenuController::class, 'categories'])->middleware('permission:menus.view');
        Route::get('menus', [AdminMenuController::class, 'index'])->middleware('permission:menus.view');
        Route::get('menus/{menu}', [AdminMenuController::class, 'show'])->middleware('permission:menus.view');
        Route::middleware('permission:menus.manage')->group(function () {
            Route::post('menus', [AdminMenuController::class, 'store']);
            Route::put('menus/{menu}', [AdminMenuController::class, 'update']);
            Route::delete('menus/{menu}', [AdminMenuController::class, 'destroy']);
        });
        Route::patch('menus/{menu}/availability', [AdminMenuController::class, 'availability'])->middleware('permission:menus.availability');
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

    // Dashboard, laporan, audit
    Route::get('dashboard/owner', [DashboardController::class, 'owner'])->middleware('permission:dashboard.owner');
    Route::get('dashboard/manager', [DashboardController::class, 'manager'])->middleware('permission:dashboard.manager');
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/sales', [SalesReportController::class, 'sales']);
    });
    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit.view');
});
