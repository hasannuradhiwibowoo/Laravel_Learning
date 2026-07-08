<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    /**
     * Kompresi gambar agar ukurannya <= $maxKb KB (default 300KB)
     * karena storage VPS terbatas (40-50GB).
     */
    public function kompres(string $sourcePath, int $maxKb = 300): string
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($sourcePath);

        $quality = 85;
        do {
            $img->save($sourcePath, $quality);
            $sizeKb = filesize($sourcePath) / 1024;
            $quality -= 10;
        } while ($sizeKb > $maxKb && $quality > 10);

        if ($sizeKb > $maxKb) {
            $img->scale(width: 1024);
            $quality = 80;
            do {
                $img->save($sourcePath, $quality);
                $sizeKb = filesize($sourcePath) / 1024;
                $quality -= 10;
            } while ($sizeKb > $maxKb && $quality > 10);
        }

        return $sourcePath;
    }
}
