<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    //Todo　テスト作成
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テストユーザー',
                'password' => Hash::make('password123'),
                'postal_code' => '1500001',
                'address' => '東京都渋谷区神宮前1-1-1',
                'building_name' => 'テストマンション101',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'taro@example.com'],
            [
                'name' => 'coachtech太郎',
                'password' => Hash::make('password123'),
                'postal_code' => '5300001',
                'address' => '大阪府大阪市北区梅田2-2-2',
                'building_name' => 'coachtechビル3F',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'hanako@example.com'],
            [
                'name' => 'サンプル花子',
                'password' => Hash::make('password123'),
                'postal_code' => '4600008',
                'address' => '愛知県名古屋市中区栄3-3-3',
                'building_name' => 'サンプルハイツ205',
                'email_verified_at' => now(),
            ]
        );
    }
}