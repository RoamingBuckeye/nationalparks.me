<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Photos\ExtractPhotoMetadata;
use App\Actions\Photos\GenerateThumbnail;
use App\Http\Requests\StorePhotoRequest;
use App\Models\Photo;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PhotoController extends Controller
{
    /**
     * Attach one or more photos to a visit.
     */
    public function store(
        StorePhotoRequest $request,
        Visit $visit,
        ExtractPhotoMetadata $extractMetadata,
        GenerateThumbnail $generateThumbnail,
    ): RedirectResponse {
        $disk = (string) config('filesystems.default');
        $userId = $request->user()->id;

        // Persist all photos atomically: if any row fails, roll back and remove
        // every file we already stored so nothing is left orphaned on disk.
        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $visit, $extractMetadata, $generateThumbnail, $disk, $userId, &$storedPaths): void {
                foreach (Arr::wrap($request->file('photos')) as $file) {
                    $metadata = $extractMetadata($file->getRealPath(), $file->getMimeType());

                    // Generate the thumbnail before store() moves the temp file.
                    $thumbnailPath = $generateThumbnail($file, $disk, $userId);
                    if ($thumbnailPath !== null) {
                        $storedPaths[] = $thumbnailPath;
                    }

                    $path = $file->store("photos/{$userId}", $disk);
                    if ($path === false) {
                        throw new RuntimeException('Failed to store uploaded photo.');
                    }
                    $storedPaths[] = $path;

                    $visit->photos()->create([
                        'disk' => $disk,
                        'path' => $path,
                        'thumbnail_path' => $thumbnailPath,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'taken_at' => $metadata['taken_at'],
                        'latitude' => $metadata['latitude'],
                        'longitude' => $metadata['longitude'],
                        'uploaded_by_user_id' => $userId,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($storedPaths);

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photos uploaded.')]);

        return back();
    }

    /**
     * Stream a photo to its owner (works for local disk now, S3 later).
     *
     * Pass `?variant=thumbnail` for the small gallery image; falls back to the
     * original when no thumbnail exists.
     */
    public function show(Request $request, Photo $photo): StreamedResponse
    {
        $this->authorize('view', $photo);

        $wantsThumbnail = $request->query('variant') === 'thumbnail'
            && $photo->thumbnail_path !== null;

        return Storage::disk($photo->disk)->response(
            $wantsThumbnail ? $photo->thumbnail_path : $photo->path,
            $photo->original_filename,
            ['Content-Type' => $wantsThumbnail ? 'image/jpeg' : $photo->mime],
        );
    }

    /**
     * Delete a photo and both of its underlying files.
     */
    public function destroy(Photo $photo): RedirectResponse
    {
        $this->authorize('delete', $photo);

        Storage::disk($photo->disk)->delete(
            array_filter([$photo->path, $photo->thumbnail_path]),
        );

        $photo->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photo deleted.')]);

        return back();
    }
}
