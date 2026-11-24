<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeExternalUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $url = trim((string) $value);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $fail('URL tidak valid.');

            return;
        }

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        if (! in_array($scheme, ['http', 'https'], true)) {
            $fail('URL harus menggunakan protokol HTTP atau HTTPS.');

            return;
        }

        if ($host === '' || $host === '0.0.0.0') {
            $fail('Host URL tidak valid.');

            return;
        }

        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            '169.254.169.254',
            'metadata.google.internal',
            'instance-data',
        ];

        if (in_array($host, $blockedHosts, true) || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            $fail('URL mengarah ke alamat lokal atau metadata internal yang tidak diizinkan (SSRF Protection).');

            return;
        }

        // If the host is an IP address, check for private/reserved ranges
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $fail('URL mengarah ke jaringan privat/terlarang (SSRF Protection).');

                return;
            }
        }
    }
}
