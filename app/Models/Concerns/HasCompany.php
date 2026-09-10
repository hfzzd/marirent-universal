<?php

namespace App\Models\Concerns;

use App\Models\Company;

/**
 * Mengisi `company_id` secara otomatis dari company pemilik (owner)
 * ketika record item dibuat tanpa company_id.
 */
trait HasCompany
{
    public static function bootHasCompany(): void
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && !empty($model->owner_id)) {
                $model->company_id = Company::ensureForOwner((int) $model->owner_id)->id;
            }
        });
    }
}
