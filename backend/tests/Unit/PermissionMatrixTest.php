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

    public function test_role_produksi_memuat_dasar_izin_kedua_station_untuk_disaring_oleh_station_akun(): void
    {
        $r = $this->config['roles'];
        $this->assertContains('queue.barista', $r['barista_kitchen']);
        $this->assertContains('queue.kitchen', $r['barista_kitchen']);
        foreach (['reports.view', 'users.manage', 'purchases.manage', 'stock.adjust', 'payments.verify'] as $p) {
            $this->assertNotContains($p, $r['barista_kitchen'], "barista_kitchen tidak boleh punya $p");
        }
    }

    public function test_hanya_owner_mengelola_akun_karyawan_dan_izin(): void
    {
        foreach (['owner', 'manager', 'kasir', 'barista_kitchen'] as $role) {
            $this->assertSame($role === 'owner', in_array('users.manage', $this->config['roles'][$role], true), "users.manage untuk $role");
            $this->assertSame($role === 'owner', in_array('permissions.grant', $this->config['roles'][$role], true), "permissions.grant untuk $role");
        }
    }

    public function test_fitur_stok_dan_pembelian_tidak_diberikan_ke_role_operasional(): void
    {
        foreach (['stock.adjust', 'purchases.manage', 'purchases.receive'] as $p) {
            foreach ($this->config['roles'] as $role => $perms) {
                $this->assertNotContains($p, $perms, "izin $p sudah dinonaktifkan untuk $role");
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
