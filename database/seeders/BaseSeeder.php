<?php

namespace Database\Seeders;

use App\Enums\Language;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class BaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedSetting();
        $this->seedPaymentMethod();
        $this->seedDefaultAdmin();
    }

    private function seedDefaultAdmin(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'lang' => Language::ZH->value,
            'timezone' => 'Asia/Kuala_Lumpur',
            'access_level' => 3,
        ]);
    }

    private function seedPaymentMethod(): void
    {
        $names = [
            'Touch \'n Go',
            'Boost Pay',
            'Online Banking',
            'Maybank QR Pay',
            'Quin Pay',
        ];

        $icons = [
            'tng',
            'boost',
            'online-banking',
            'maybank-qr-pay',
            'quin-pay',
        ];

        $qrCode = [
            'tng',
            'boost',
            'online-banking',
            'maybank-qr-pay',
            'quin-pay',
        ];

        for ($i = 0; $i < count($names); $i++) {
            $paymentMethod = new PaymentMethod(
                [
                    'name' => $names[$i],
                    'icon_img_path' => $icons[$i],
                    'qr_code_img_path' => $qrCode[$i],
                    'is_enabled' => true,
                ]
            );
            $paymentMethod->save();
        }
    }

    private function seedSetting(): void
    {
        $list[] = new Setting(
            [
                'name' => 'shipping_fee',
                'value' => '3',
                'desc' => '运输费用',
            ]
        );

        $list[] = new Setting(
            [
                'name' => 'freeShipping_isActivated',
                'value' => '1',
                'desc' => '订单免运费开关',
            ]
        );

        $list[] = new Setting(
            [
                'name' => 'freeShipping_threshold',
                'value' => '50',
                'desc' => '订单免运费触发价格',
            ]
        );

        $list[] = new Setting(
            [
                'name' => 'freeShipping_desc',
                'value' => '满RM50包邮',
                'desc' => '订单免运费详情',
            ]
        );

        foreach ($list as $config) {
            $config->save();
        }
    }
}
