<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DefaultValueHandler::import();

        $user = new User(
            [
                'username' => 'admin',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'access_level' => 3,
                'is_enabled' => true,
                'remember_token' => Str::random(10),
            ]
        );

        $user->save();
    }
}
