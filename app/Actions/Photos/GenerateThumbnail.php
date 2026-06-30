<?php

declare(strict_types=1);

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Interfaces\ImageManagerInterface;
use Throwable;

class GenerateThumbnail
{
    public function __construct(
        protected ImageManagerInterface $imageManager,
    ) {}

    /**
     * Cover-crop a square JPEG thumbnail for the given upload and store it on
     * the same disk. Returns the stored path, or null if generation failed
     * (callers fall back to serving the original).
     */
    public function __invoke(UploadedFile $file, string $disk, int $userId): ?string
    {
        try {
            $encoded = $this->imageManager
                ->decodePath($file->getRealPath())
                ->cover(500, 500)
                ->encode(new JpegEncoder(quality: 80));

            $path = "thumbnails/{$userId}/".Str::ulid().'.jpg';

            Storage::disk($disk)->put($path, $encoded->toString());

            return $path;
        } catch (Throwable) {
            return null;
        }
    }
}
