<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidIsbn implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $clean = str_replace(['-', ' '], '', (string) $value);

        $isIsbn10 = (bool) preg_match('/^[0-9]{9}[0-9Xx]$/', $clean);
        $isIsbn13 = (bool) preg_match('/^(978|979)[0-9]{10}$/', $clean);

        if (! $isIsbn10 && ! $isIsbn13) {
            $fail('Format ISBN tidak valid (harus berupa format ISBN-10 atau ISBN-13 yang valid).');
        }
    }
}
