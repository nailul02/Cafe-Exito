<?php

namespace Tests\Unit;

use App\Enums\Permission;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

/** Menjaga matriks hak akses (config/permissions.php) tetap konsisten dengan keputusan proyek. */
class PermissionMatrixTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        $this->config = require __DIR__.'/../../config/permissions.php';
    }

    public function test_setiap_role_staf_punya_entri_dan_tidak_ada_role_asing(): void
    {
        $this->assertEqualsCanonicalizing(UserRole::values(), array_keys($this->config['roles']));
    }

    public function test_semua_kunci_izin_valid(): void
    {
        $valid = Permission::values();
        foreach ($this->config['roles'] as $role => $perms) {
            $this->assertSame([], array_diff($perms, $valid), "role $role memakai izin tidak dikenal");
        }
        $this->assertSame([], array_diff($this->config['grantable'], $valid));
    }

    public function test_barista_dan_kitchen_terpisah(): void
    {
        $r = $this->config['roles'];
        $this->assertContains('queue.barista', $r['barista']);
        $this->assertNotContains('queue.kitchen', $r['barista']);
        $this->assertContains('queue.kitchen', $r['kitchen']);
        $this->assertNotContains('queue.barista', $r['kitchen']);
        foreach (['barista', 'kitchen'] as $role) {
            foreach (['reports.view', 'users.manage', 'purchases.manage', 'stock.adjust', 'payments.verify'] as $p) {
                $this->assertNotContains($p, $r[$role], "$role tidak boleh punya $p");
            }
        }
    }

    public function test_owner_hanya_membaca(): void
    {
        foreach ($this->config['roles']['owner'] as $perm) {
            $this->assertDoesNotMatchRegularExpression('/\.(manage|adjust|receive|verify|cancel|grant)$/', $perm, "owner tidak boleh punya $perm");
        }
    }

    public function test_hanya_admin_mengelola_pengguna_dan_stok(): void
    {
        foreach (['users.manage', 'permissions.grant', 'stock.adjust', 'purchases.manage', 'purchases.receive'] as $p) {
            foreach ($this->config['roles'] as $role => $perms) {
                $this->assertSame($role === 'admin', in_array($p, $perms, true), "izin $p untuk $role");
            }
        }
    }

    public function test_kasir_hanya_pembayaran_dan_baca_menu(): void
    {
        $kasir = $this->config['roles']['kasir'];
        $this->assertContains('payments.verify', $kasir);
        $this->assertNotContains('queue.barista', $kasir);
        $this->assertNotContains('queue.kitchen', $kasir);
        $this->assertNotContains('menus.manage', $kasir);
    }

    public function test_izin_yang_dapat_diberikan_tidak_memuat_izin_berbahaya(): void
    {
        foreach (['users.manage', 'permissions.grant', 'stock.adjust', 'payments.verify', 'menus.manage', 'reports.view', 'orders.cancel'] as $p) {
            $this->assertNotContains($p, $this->config['grantable'], "$p tidak boleh dapat diberikan");
        }
    }
}
