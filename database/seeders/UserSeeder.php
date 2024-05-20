<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();
        $user->create([
            'name'=>'admin',
            'user_name'=>'admin',
            'email'=>'mr319242@gmail.com',
            'phone'=>'11111111',
            'password'=>Hash::make('11111111'),
            'account_type'=>'admin'
        ]);
    }
}
