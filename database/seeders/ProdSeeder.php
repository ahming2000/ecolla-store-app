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
        $list[] = new SystemConfig(
            [
                'name' => 'shipping_fee',
                'value' => '3',
                'desc' => '运输费用',
            ]
        );

        $list[] = new SystemConfig(
            [
                'name' => 'shipping_discount_is_activated',
                'value' => '1',
                'desc' => '订单免运费开关',
            ]
        );

        $list[] = new SystemConfig(
            [
                'name' => 'shipping_discount_threshold',
                'value' => '50',
                'desc' => '订单免运费触发价格',
            ]
        );

        $list[] = new SystemConfig(
            [
                'name' => 'shipping_discount_desc',
                'value' => '满RM50包邮',
                'desc' => '订单免运费详情',
            ]
        );

        foreach($list as $config) {
            $config->save();
        }

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

        $names = [
            'Touch \'n Go',
            'Boost Pay',
            'Online Banking',
            'Maybank QR Pay',
            'Quin Pay'
        ];

        $icons = [
            '/images/tng.png',
            '/images/boost.png',
            '/images/online-banking.png',
            '/images/maybank-qr-pay.png',
            '/images/quin-pay.png',
        ];

        $qrCode = [
            '/images/tng-qr-code.jpeg',
            '/images/boost-qr-code.jpeg',
            '/images/online-banking-qr-code.jpeg',
            '/images/maybank-qr-pay-qr-code.jpeg',
            '/images/quin-pay-qr-code.jpeg',
        ];

        for ($i = 0; $i < sizeof($names); $i++) {
            $paymentMethod = new PaymentMethod(
                [
                    'name' => $names[$i],
                    'icon' => $icons[$i],
                    'qr_code' => $qrCode[$i],
                ]
            );
            $paymentMethod->save();
        }
    }
}
