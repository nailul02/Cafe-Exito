<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceEvent extends Model
{
    public $timestamps = false; // hanya menggunakan created_at dan recorded_at

    protected $fillable = [
        'attendance_id', 'user_id', 'event_type', 'recorded_at', 'photo_path',
        'latitude', 'longitude', 'accuracy_meters', 'device_info', 'notes', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'created_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_meters' => 'float',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
