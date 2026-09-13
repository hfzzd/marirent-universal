<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'business_name',
        'preferred_date', 'preferred_time', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return ['preferred_date' => 'date'];
    }
}
