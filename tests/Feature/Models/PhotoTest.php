<?php

declare(strict_types=1);

use App\Domain\Coordinates;
use App\Models\Photo;
use App\Models\Visit;
use App\Models\VisitPointOfInterest;

it('attaches polymorphically to a visit', function () {
    $visit = Visit::factory()->create();
    $photo = Photo::factory()->for($visit, 'photoable')->create();

    expect($visit->refresh()->photos)->toHaveCount(1)
        ->and($photo->photoable->is($visit))->toBeTrue();
});

it('attaches polymorphically to a visit POI', function () {
    $vpoi = VisitPointOfInterest::factory()->create();
    Photo::factory()->for($vpoi, 'photoable')->create();

    expect($vpoi->refresh()->photos)->toHaveCount(1);
});

it('exposes a Coordinates VO via the accessor when EXIF GPS is present', function () {
    $photo = Photo::factory()->create();

    expect($photo->coordinates)->toBeInstanceOf(Coordinates::class);
});

it('returns null coordinates when EXIF GPS is missing', function () {
    $photo = Photo::factory()->withoutExif()->create();

    expect($photo->coordinates)->toBeNull()
        ->and($photo->taken_at)->toBeNull();
});

it('tracks the uploader', function () {
    $photo = Photo::factory()->create();

    expect($photo->uploader)->not->toBeNull()
        ->and($photo->uploader->id)->toBe($photo->uploaded_by_user_id);
});
