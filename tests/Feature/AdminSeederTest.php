<?php

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;

test('admin seeder creates the admin account', function () {
    $this->seed(AdminSeeder::class);

    $admin = User::sole();

    expect($admin->email)->toBe('admin@gerejabampel.test')
        ->and(Hash::check('password', $admin->password))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});

test('running the admin seeder again keeps a changed password and name', function () {
    $this->seed(AdminSeeder::class);
    User::sole()->update(['name' => 'Sekretariat Paroki', 'password' => 'rahasia-baru']);

    $this->seed(AdminSeeder::class);

    $admin = User::sole();

    expect($admin->name)->toBe('Sekretariat Paroki')
        ->and(Hash::check('rahasia-baru', $admin->password))->toBeTrue();
});
