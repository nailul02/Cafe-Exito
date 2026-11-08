<?php

namespace App\Enums;

/** Kunci izin yang valid. Peta role → izin ada di config/permissions.php. */
enum Permission: string
{
    case DashboardOwner = 'dashboard.owner';
    case DashboardManager = 'dashboard.manager';
    case DashboardCashier = 'dashboard.cashier';
    case DashboardStation = 'dashboard.station';
    case OrdersView = 'orders.view';
    case OrdersCancel = 'orders.cancel';
    case PaymentsVerify = 'payments.verify';
    case TransactionsView = 'transactions.view';
    case QueueBarista = 'queue.barista';
    case QueueKitchen = 'queue.kitchen';
    case QueueViewAll = 'queue.view_all';
    case MenusView = 'menus.view';
    case MenusManage = 'menus.manage';
    case IngredientsView = 'ingredients.view';
    case IngredientsManage = 'ingredients.manage';
    case RecipesManage = 'recipes.manage';
    case SuppliersManage = 'suppliers.manage';
    case PurchasesView = 'purchases.view';
    case PurchasesManage = 'purchases.manage';
    case PurchasesReceive = 'purchases.receive';
    case StockView = 'stock.view';
    case StockAdjust = 'stock.adjust';
    case TablesManage = 'tables.manage';
    case UsersManage = 'users.manage';
    case PermissionsGrant = 'permissions.grant';
    case ReportsView = 'reports.view';
    case AuditView = 'audit.view';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $p) => $p->value, self::cases());
    }
}
