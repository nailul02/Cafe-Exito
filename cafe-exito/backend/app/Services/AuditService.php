<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

/** Jejak aksi sensitif: ubah harga, koreksi stok, izin, verifikasi/penolakan pembayaran, pembatalan, login gagal. */
class AuditService
{
    public function record(?User $user, string $action, string $subjectType, ?int $subjectId = null, ?array $changes = null): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'changes' => $changes,
            'ip' => request()?->ip(),
        ]);
    }
}
