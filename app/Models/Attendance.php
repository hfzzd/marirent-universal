<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'driver_id',
        'date',
        'check_in',
        'check_out',
        'check_in_notes',
        'check_out_notes',
        'check_in_location',
        'check_out_location',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function isCheckedin(): bool
    {
        return $this->status === 'checked_in';
    }

    public function isCheckedOut(): bool
    {
        return $this->status === 'checked_out';
    }

    public function getWorkDurationAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $minutes = $this->check_in->diffInMinutes($this->check_out);
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%dj %dm', $hours, $mins);
    }
}
