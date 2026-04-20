<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['phone' => '+9647000000001'],
            [
                'name'     => 'Super Admin',
                'is_admin' => true,
                'password' => Hash::make('admin@tafwela'),
            ]
        );
    }
}
