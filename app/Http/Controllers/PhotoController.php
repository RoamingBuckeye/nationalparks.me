<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Photos\ExtractPhotoMetadata;
use App\Http\Requests\StorePhotoRequest;
use App\Models\Photo;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    /**
     * Attach one or more photos to a visit.
     */
    public function store(StorePhotoRequest $request, Visit $visit, ExtractPhotoMetadata $extractMetadata): RedirectResponse
    {
        $disk = (string) config('filesystems.default');
        $userId = $request->user()->id;

        foreach (Arr::wrap($request->file('photos')) as $file) {
            $metadata = $extractMetadata($file->getRealPath(), $file->getMimeType());

            $visit->photos()->create([
                'disk' => $disk,
                'path' => $file->store("photos/{$userId}", $disk),
                'original_filename' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'taken_at' => $metadata['taken_at'],
                'latitude' => $metadata['latitude'],
                'longitude' => $metadata['longitude'],
                'uploaded_by_user_id' => $userId,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photos uploaded.')]);

        return back();
    }

    /**
     * Stream a photo to its owner (works for local disk now, S3 later).
     */
    public function show(Photo $photo): StreamedResponse
    {
        $this->authorize('view', $photo);

        return Storage::disk($photo->disk)->response($photo->path, $photo->original_filename, [
            'Content-Type' => $photo->mime,
        ]);
    }

    /**
     * Delete a photo and its underlying file.
     */
    public function destroy(Photo $photo): RedirectResponse
    {
        $this->authorize('delete', $photo);

        Storage::disk($photo->disk)->delete($photo->path);

        $photo->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photo deleted.')]);

        return back();
    }
}
