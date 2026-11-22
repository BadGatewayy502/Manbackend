<?php

namespace App\Support;

use Carbon\Carbon;

class JakartaDateTime
{
    public static function fromLocalInput(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        return Carbon::parse((string) $value, 'Asia/Jakarta');
    }
}
