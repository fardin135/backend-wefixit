<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->firstOrFail();
        $userRole = Role::where('name', 'User')->firstOrFail();

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('12345678'),
            ]
        );

        $admin->roles()->syncWithoutDetaching([
            $adminRole->id,
        ]);

        $user = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'user',
                'password' => Hash::make('12345678'),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $userRole->id,
        ]);
    }
}
