<?php

declare(strict_types=1);

use App\Models\User;

it('updates the display name and share preference from the profile page', function () {
    $user = User::factory()->create([
        'display_name' => null,
        'share_enabled' => false,
    ]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => 'Trail Boss',
            'share_enabled' => '1',
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->display_name)->toBe('Trail Boss');
    expect($user->share_enabled)->toBeTrue();
});

it('can disable sharing again', function () {
    $user = User::factory()->create(['share_enabled' => true]);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'share_enabled' => '0',
        ])
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->share_enabled)->toBeFalse();
});

it('rejects a non-boolean share preference', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'share_enabled' => 'maybe',
        ])
        ->assertSessionHasErrors('share_enabled');
});
