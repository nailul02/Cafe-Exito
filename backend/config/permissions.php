<?php

/*
 | Matriks hak akses bawaan per role (docs/AUTHORIZATION_MATRIX di DEVELOPMENT_GUIDE). Hak khusus per akun disimpan di
 | tabel user_permissions dan hanya boleh untuk daftar `grantable`.
 | Role produksi tunggal `barista_kitchen`; permission antrean efektif dibatasi oleh users.station.
 */
return [
    'roles' => [
        'owner' => ['dashboard.owner', 'orders.view', 'orders.cancel', 'transactions.view', 'queue.view_all', 'menus.view', 'reports.view', 'attendance.view_all', 'attendance.report', 'users.manage', 'permissions.grant', 'refund.review', 'refund.audit', 'audit.view', 'refund.conflict.document', 'notifications.view'],
        'manager' => ['dashboard.manager', 'orders.view', 'transactions.view', 'queue.view_all', 'menus.view', 'menus.manage', 'tables.manage', 'reports.view', 'audit.view', 'attendance.view_all', 'attendance.record', 'refund.conflict.document', 'notifications.view'],
        'kasir' => ['dashboard.cashier', 'payments.verify', 'transactions.view', 'menus.view', 'orders.create_pos', 'attendance.record', 'refund.view', 'refund.execute', 'refund.request', 'notifications.view'],
        'barista_kitchen' => ['dashboard.station', 'queue.barista', 'queue.kitchen', 'attendance.record', 'notifications.view'],
    ],
    'grantable' => ['queue.barista', 'queue.kitchen', 'menus.view', 'attendance.record'],
];
