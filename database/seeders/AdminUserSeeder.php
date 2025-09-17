<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'a55135@yahoo.com.tw'], // 管理員帳號
            [
                'name' => 'Administrator',
                'password' => Hash::make('01234567'),
                'role' => 'admin',
                'email_verified_at' => Carbon::now(),
            ]
        );
    }
}
