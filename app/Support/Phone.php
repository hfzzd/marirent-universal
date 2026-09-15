<?php

namespace App\Support;

class Phone
{
    /**
     * Normalisasi nomor HP ke format internasional tanpa spasi/karakter khusus,
     * mis. "0812-3456-7890", "+628123456789", "628123456789" => "628123456789".
     * Input kosong / non-digit => null.
     *
     * @param  string|null  $phone
     * @return string|null
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } else {
            $digits = '62' . $digits;
        }

        return $digits;
    }
}