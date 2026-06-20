<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'          => 'admin',
            'user_name'     => 'admin',
            'email'         => 'admin@admin.com',
            'phone'         => '01111111111',
            'password'      => Hash::make('admin'),
            'account_type'  => 'admin',
            'status'        => 1,
            'block_status'  => 1,
        ]);
    }
}
