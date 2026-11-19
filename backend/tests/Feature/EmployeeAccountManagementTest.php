<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeAccountManagementTest extends ApiTestCase
{
    public function test_owner_can_manage_employee_account_without_exposing_password(): void
    {
        $this->actingAsRole(UserRole::Owner);
        $this->getJson(self::API.'/admin/users')->assertOk()->assertJsonMissingPath('data.0.password');

        $created = $this->postJson(self::API.'/admin/users', [
            'name' => 'Staff Baru',
            'username' => 'staff.baru',
            'email' => 'Staff.Baru@Cafe.Test',
            'role' => 'barista_kitchen',
            'station' => 'kitchen',
            'password' => 'initial-secret-123',
        ])->assertCreated()
            ->assertJsonPath('data.username', 'staff.baru')
            ->assertJsonPath('data.email', 'staff.baru@cafe.test')
            ->assertJsonPath('data.role.code', 'barista_kitchen')
            ->assertJsonPath('data.station', 'kitchen')
            ->assertJsonMissingPath('data.password');

        $user = User::query()->where('username', 'staff.baru')->firstOrFail();
        $this->assertTrue(Hash::check('initial-secret-123', $user->password));

        $this->putJson(self::API.'/admin/users/'.$user->id, [
            'name' => 'Staff Kasir',
            'username' => 'staff.kasir',
            'email' => 'staff.kasir@cafe.test',
            'role' => 'kasir',
        ])->assertOk()->assertJsonPath('data.role.code', 'kasir')->assertJsonPath('data.station', null);

        $user->refresh()->createToken('existing-session');
        $this->postJson(self::API.'/admin/users/'.$user->id.'/reset-password', ['password' => 'new-secret-456'])
            ->assertOk()->assertJsonMissing(['new-secret-456']);
        $this->assertTrue(Hash::check('new-secret-456', $user->refresh()->password));
        $this->assertSame(0, $user->tokens()->count());

        $user->createToken('another-session');
        $this->patchJson(self::API.'/admin/users/'.$user->id.'/status', ['is_active' => false])->assertOk();
        $this->assertFalse($user->refresh()->is_active);
        $this->assertSame(0, $user->tokens()->count());
        $this->postJson(self::API.'/auth/login', ['login' => 'staff.kasir', 'password' => 'new-secret-456'])->assertForbidden();
    }

    public function test_owner_cannot_create_owner_or_mutate_any_owner_account(): void
    {
        $owner = $this->actingAsRole(UserRole::Owner);
        $this->postJson(self::API.'/admin/users', [
            'name' => 'Owner Lain', 'username' => 'owner.lain', 'email' => 'owner.lain@cafe.test',
            'role' => 'owner', 'password' => 'secret-password',
        ])->assertUnprocessable();

        $this->patchJson(self::API.'/admin/users/'.$owner->id.'/status', ['is_active' => false])->assertForbidden();
        $this->putJson(self::API.'/admin/users/'.$owner->id, ['name' => 'Nama berubah'])->assertForbidden();
        $this->postJson(self::API.'/admin/users/'.$owner->id.'/reset-password', ['password' => 'secret-password'])->assertForbidden();
        $this->deleteJson(self::API.'/admin/users/'.$owner->id)->assertStatus(405);
        $this->assertTrue($owner->refresh()->is_active);
    }

    public function test_non_owner_roles_cannot_list_or_change_employee_accounts(): void
    {
        $target = User::factory()->role(UserRole::Kasir)->create();

        foreach ([UserRole::Manager, UserRole::Kasir, UserRole::BaristaKitchen] as $role) {
            $this->actingAsRole($role);
            $this->getJson(self::API.'/admin/users')->assertForbidden();
            $this->postJson(self::API.'/admin/users', [])->assertForbidden();
            $this->putJson(self::API.'/admin/users/'.$target->id, ['name' => 'Diubah'])->assertForbidden();
            $this->patchJson(self::API.'/admin/users/'.$target->id.'/status', ['is_active' => false])->assertForbidden();
            $this->postJson(self::API.'/admin/users/'.$target->id.'/reset-password', ['password' => 'changed-password'])->assertForbidden();
            $this->getJson(self::API.'/admin/users/'.$target->id.'/permissions')->assertForbidden();
            $this->putJson(self::API.'/admin/users/'.$target->id.'/permissions', ['permissions' => []])->assertForbidden();
        }
        $this->assertSame('kasir', $target->refresh()->role->value);
        $this->assertTrue($target->is_active);
    }

    public function test_owner_account_validation_rejects_duplicates_and_invalid_station(): void
    {
        $this->actingAsRole(UserRole::Owner);
        User::factory()->role(UserRole::Kasir)->create(['username' => 'duplicate', 'email' => 'duplicate@cafe.test']);

        $this->postJson(self::API.'/admin/users', [
            'name' => 'Duplikat', 'username' => 'DUPLICATE', 'email' => 'new@cafe.test', 'role' => 'kasir', 'password' => 'initial-secret',
        ])->assertUnprocessable()->assertJsonValidationErrors(['username']);

        $this->postJson(self::API.'/admin/users', [
            'name' => 'Duplikat', 'username' => 'different', 'email' => 'DUPLICATE@CAFE.TEST', 'role' => 'kasir', 'password' => 'initial-secret',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);

        $this->postJson(self::API.'/admin/users', [
            'name' => 'Tanpa Station', 'username' => 'no.station', 'email' => 'no.station@cafe.test', 'role' => 'barista_kitchen', 'password' => 'initial-secret',
        ])->assertUnprocessable()->assertJsonValidationErrors(['station']);

        $this->postJson(self::API.'/admin/users', [
            'name' => 'Station Salah', 'username' => 'wrong.station', 'email' => 'wrong.station@cafe.test', 'role' => 'manager', 'station' => 'barista', 'password' => 'initial-secret',
        ])->assertUnprocessable()->assertJsonValidationErrors(['station']);
    }
}
