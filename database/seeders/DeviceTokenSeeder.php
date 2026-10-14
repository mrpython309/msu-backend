<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Support\Str;

class DeviceTokenSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id');

        foreach ($users as $userId) {

            DeviceToken::updateOrCreate(
                ['user_id' => $userId],
                [
                    'device_token' => Str::random(160), // random token
                    'type' => 'android'
                ]
            );

        }
    }
}