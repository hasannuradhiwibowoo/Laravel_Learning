<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    public function kompres(string $sourcePath, int $maxKb = 300): string
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->decodePath($sourcePath);

        $quality = 85;
        do {
            $img->save($sourcePath, quality: $quality);
            $sizeKb = filesize($sourcePath) / 1024;
            $quality -= 10;
        } while ($sizeKb > $maxKb && $quality > 10);

        if ($sizeKb > $maxKb) {
            $img->scale(1024);
            $quality = 80;
            do {
                $img->save($sourcePath, quality: $quality);
                $sizeKb = filesize($sourcePath) / 1024;
                $quality -= 10;
            } while ($sizeKb > $maxKb && $quality > 10);
        }

        return $sourcePath;
    }
}
