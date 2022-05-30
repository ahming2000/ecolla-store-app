<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\SystemConfig;

class DefaultValueHandler {
    public static function import()
    {
        self::paymentMethod();
        self::systemConfig();
    }

    private static function paymentMethod() {
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

    private static function systemConfig() {
        $list[] = new SystemConfig(
            [
                'name' => 'shipping_fee',
                'value' => '3',
                'desc' => '运输费用',
            ]
        );

        $list[] = new SystemConfig(
            [
                'name' => 'freeShipping_isActivated',
                'value' => '1',
                'desc' => '订单免运费开关',
            ]
        );

        $list[] = new SystemConfig(
            [
                'name' => 'freeShipping_threshold',
                'value' => '50',
                'desc' => '订单免运费触发价格',
            ]
        );

        $list[] = new SystemConfig(
            [
                'name' => 'freeShipping_desc',
                'value' => '满RM50包邮',
                'desc' => '订单免运费详情',
            ]
        );

        foreach($list as $config) {
            $config->save();
        }
    }
}
