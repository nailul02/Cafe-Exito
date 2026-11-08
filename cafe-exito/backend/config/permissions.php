<?php

/*
 | Matriks hak akses bawaan per role (docs/AUTHORIZATION_MATRIX di DEVELOPMENT_GUIDE). Hak khusus per akun disimpan di
 | tabel user_permissions dan hanya boleh untuk daftar `grantable`.
 | Status: CONFIRMED untuk enam role & pemisahan Barista/Kitchen; ASUMSI untuk sisanya (lihat docs/DECISIONS_PENDING.md K-B11).
 */
return [
    'roles' => [
        'owner' => ['dashboard.owner', 'orders.view', 'transactions.view', 'queue.view_all', 'menus.view', 'ingredients.view', 'purchases.view', 'stock.view', 'reports.view'],
        'admin' => ['dashboard.manager', 'orders.view', 'orders.cancel', 'transactions.view', 'queue.view_all', 'menus.view', 'menus.manage', 'ingredients.view', 'ingredients.manage', 'recipes.manage', 'suppliers.manage', 'purchases.view', 'purchases.manage', 'purchases.receive', 'stock.view', 'stock.adjust', 'tables.manage', 'users.manage', 'permissions.grant', 'reports.view', 'audit.view'],
        'kasir' => ['dashboard.cashier', 'payments.verify', 'transactions.view', 'menus.view'],
        'barista' => ['dashboard.station', 'queue.barista'],
        'kitchen' => ['dashboard.station', 'queue.kitchen'],
    ],
    'grantable' => ['queue.barista', 'queue.kitchen', 'purchases.receive', 'ingredients.view', 'stock.view', 'menus.view'],
];
