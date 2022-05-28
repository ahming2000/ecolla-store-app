<?php

namespace Database\Seeders;

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
        DB::table('system_configs')
            ->insert(
                [
                    'name' => 'shipping_fee',
                    'value' => '3',
                    'desc' => '运输费用',
                ]
            );

        DB::table('system_configs')
            ->insert(
                [
                    'name' => 'shipping_discount_is_activated',
                    'value' => '1',
                    'desc' => '订单免运费开关',
                ]
            );

        DB::table('system_configs')
            ->insert(
                [
                    'name' => 'shipping_discount_threshold',
                    'value' => '50',
                    'desc' => '订单免运费触发价格',
                ]
            );

        DB::table('system_configs')
            ->insert(
                [
                    'name' => 'shipping_discount_desc',
                    'value' => '满RM50包邮',
                    'desc' => '订单免运费详情',
                ]
            );

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
