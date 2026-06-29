<?php

declare(strict_types=1);

use App\Models\Photo;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads photos to a visit', function () {
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
    expect($photo->uploaded_by_user_id)->toBe($user->id);
    Storage::disk('local')->assertExists($photo->path);
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

it('streams a photo to its owner', function () {
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
});

it('forbids viewing another user\'s photo', function () {
    Storage::fake('local');
    $photo = Photo::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('photos.show', $photo))
        ->assertForbidden();
});

it('deletes a photo and its file', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $visit = Visit::factory()->for($user)->create();
    $this->actingAs($user)->post(route('visits.photos.store', $visit), [
        'photos' => [UploadedFile::fake()->image('x.jpg')],
    ]);
    $photo = $visit->photos()->sole();

    $this->actingAs($user)
        ->delete(route('photos.destroy', $photo))
        ->assertRedirect();

    $this->assertModelMissing($photo);
    Storage::disk('local')->assertMissing($photo->path);
});
