<?php

declare(strict_types=1);

use App\Models\Photo;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads photos to a visit and generates thumbnails', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('visits.photos.store', $visit), [
            'photos' => [
                UploadedFile::fake()->image('bison.jpg'),
                UploadedFile::fake()->image('geyser.png'),
            ],
        ])
        ->assertRedirect();

    expect($visit->photos()->count())->toBe(2);

    $photo = $visit->photos()->first();
    expect($photo->uploaded_by_user_id)->toBe($user->id)
        ->and($photo->thumbnail_path)->not->toBeNull();
    Storage::disk('local')->assertExists($photo->path);
    Storage::disk('local')->assertExists($photo->thumbnail_path);
});

it('rejects non-image uploads', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('visits.photos.store', $visit), [
            'photos' => [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
        ])
        ->assertSessionHasErrors('photos.0');

    expect($visit->photos()->count())->toBe(0);
});

it('forbids uploading photos to another user\'s visit', function () {
    Storage::fake('local');
    $visit = Visit::factory()->for(User::factory())->create();

    $this->actingAs(User::factory()->create())
        ->post(route('visits.photos.store', $visit), [
            'photos' => [UploadedFile::fake()->image('x.jpg')],
        ])
        ->assertForbidden();
});

it('streams the full photo and the thumbnail variant to its owner', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();
    $this->actingAs($user)->post(route('visits.photos.store', $visit), [
        'photos' => [UploadedFile::fake()->image('x.jpg')],
    ]);
    $photo = $visit->photos()->sole();

    $this->actingAs($user)
        ->get(route('photos.show', $photo))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('photos.show', ['photo' => $photo, 'variant' => 'thumbnail']))
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
});

it('falls back to the original when a photo has no thumbnail', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();
    $this->actingAs($user)->post(route('visits.photos.store', $visit), [
        'photos' => [UploadedFile::fake()->image('x.jpg')],
    ]);
    $photo = $visit->photos()->sole();
    $photo->update(['thumbnail_path' => null]);

    $this->actingAs($user)
        ->get(route('photos.show', ['photo' => $photo, 'variant' => 'thumbnail']))
        ->assertOk();
});

it('forbids viewing another user\'s photo', function () {
    Storage::fake('local');
    $photo = Photo::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('photos.show', $photo))
        ->assertForbidden();
});

it('deletes a photo and both of its files', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();
    $this->actingAs($user)->post(route('visits.photos.store', $visit), [
        'photos' => [UploadedFile::fake()->image('x.jpg')],
    ]);
    $photo = $visit->photos()->sole();
    $originalPath = $photo->path;
    $thumbnailPath = $photo->thumbnail_path;

    $this->actingAs($user)
        ->delete(route('photos.destroy', $photo))
        ->assertRedirect();

    $this->assertModelMissing($photo);
    Storage::disk('local')->assertMissing($originalPath);
    Storage::disk('local')->assertMissing($thumbnailPath);
});
