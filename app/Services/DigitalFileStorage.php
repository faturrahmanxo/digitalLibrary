<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class DigitalFileStorage
{
    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('File digital gagal disimpan ke penyimpanan privat.');
        }

        return $path;
    }
}
