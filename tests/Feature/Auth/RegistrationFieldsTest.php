<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function (): void {
    $this->skipUnlessFortifyHas(Features::registration());
});

it('stores an optional display name at registration', function () {
    $this->post(route('register.store'), [
        'name' => 'Jane Ranger',
        'display_name' => 'Wanderer',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'display_name' => 'Wanderer',
    ]);
});

it('allows registration without a display name', function () {
    $this->post(route('register.store'), [
        'name' => 'Plain Jane',
        'email' => 'plain@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    expect(User::whereEmail('plain@example.com')->value('display_name'))->toBeNull();
});

it('blocks registration when the honeypot field is filled', function () {
    config(['honeypot.randomize_name_field_name' => false]);

    $this->post(route('register.store'), [
        'name' => 'Spam Bot',
        'email' => 'bot@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'my_name' => 'http://spam.example',
    ]);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
});

it('allows registration when the honeypot field is left untouched', function () {
    config([
        'honeypot.randomize_name_field_name' => false,
        'honeypot.valid_from_timestamp' => false,
    ]);

    $this->post(route('register.store'), [
        'name' => 'Real Person',
        'email' => 'real@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'my_name' => '',
    ])->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'real@example.com']);
});
