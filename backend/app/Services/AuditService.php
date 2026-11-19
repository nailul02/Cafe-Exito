<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

/** Jejak aksi sensitif: ubah harga, koreksi stok, izin, verifikasi/penolakan pembayaran, pembatalan, login gagal. */
class AuditService
{
    private static ?bool $hasLegacyColumns = null;

    public function record(?User $user, string $action, string $subjectType, ?int $subjectId = null, ?array $changes = null): void
    {
        $payload = [
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'changes' => $changes,
            'ip' => request()?->ip(),
        ];

        if (self::$hasLegacyColumns === null) {
            self::$hasLegacyColumns = Schema::hasColumn('audit_logs', 'table_name');
        }

        if (self::$hasLegacyColumns) {
            $payload['table_name'] = $subjectType;
            $payload['record_id'] = $subjectId;
            $payload['ip_address'] = request()?->ip();
        }

        AuditLog::create($payload);
    }
}
