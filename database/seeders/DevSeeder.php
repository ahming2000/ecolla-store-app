<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Origin;
use App\Models\PaymentMethod;
use App\Models\SystemConfig;
use App\Models\User;
use App\Models\Variation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DevSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->category();
        $this->paymentMethod();
        $this->origin();
        $this->user();
        $this->item();
        $this->itemImage();
        $this->variation();
        $this->order();
        $this->customer();
        $this->orderItem();
        $this->systemConfig();
    }

    private function systemConfig()
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
    }

    private function orderItem()
    {
        OrderItem::factory(50)->create();
    }

    private function customer()
    {
        Customer::factory(10)->create();
    }

    private function order()
    {
        Order::factory(10)->create();
    }

    private function variation()
    {
        Variation::factory(50)->create();

        $items = Item::all();
        foreach ($items as $item) {
            if (!empty($item->variations)) {
                Item::query()
                    ->where('id', '=', $item->id)
                    ->update(['is_listed' => true]);
            }
        }
    }

    private function itemImage()
    {
        ItemImage::factory(30)->create();
    }

    private function item()
    {
        Item::factory(10)->create();

        $category_ids = array_column(Category::all('id')->toArray(), 'id');
        $item_ids = array_column(Item::all('id')->toArray(), 'id');

        foreach ($item_ids as $item_id) {
            $count = rand(1, 3); // Random count for adding multiple categories to each item
            $added = [];

            for ($i = 0; $i < $count; $i++) {
                $category_id = $category_ids[rand() % sizeof($category_ids)];

                // Make sure the category id will not duplicate
                while (true) {
                    $found = false;

                    foreach ($added as $a) {
                        if ($category_id == $a) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        $category_id = $category_ids[rand() % sizeof($category_ids)];
                    } else {
                        break;
                    }
                }

                DB::table('category_item')->insert(['item_id' => $item_id, 'category_id' => $category_id]);
                $added[] = $category_id; // Add to list for recording added category
            }
        }


    }

    private function origin()
    {
        $names = ['中国', '台湾', '日本', '韩国', '马来西亚', '泰国'];
        $name_ens = ['China', 'Taiwan', 'Japan', 'Korea', 'Malaysia', 'Thailand'];

        for ($i = 0; $i < sizeof($names); $i++) {
            $origin = new Origin(
                [
                    'name' => $names[$i],
                    'name_en' => $name_ens[$i]
                ]
            );
            $origin->save();
        }
    }

    private function user()
    {
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

        User::factory(10)->create();
    }

    private function category()
    {
        $names = ['未分类', '热卖', '新品', '推荐', '包点与点心', '果冻', '酸奶饮料', '零食', '酱料＆火锅底料', '火锅料', '麦片', '腌制品', '面类', '饮料', '酒类', '冰淇淋', '罐头', '糖果', '用品'];
        $name_ens = ['Uncategorized', 'Hot Selling', 'New Product', 'Recommended', 'Buns', 'Jelly', 'Yogurt', 'Snack', 'Sauces & Hotpot Base', 'Hotpot', 'Oatmeal', 'Preserved food', 'Noodles', 'Beverage', 'Alcohol', 'Ice cream', 'Canned food', 'Candy', 'Articles'];

        for ($i = 0; $i < sizeof($names); $i++) {
            $category = new Category(
                [
                    'name' => $names[$i],
                    'name_en' => $name_ens[$i],
                ]
            );
            $category->save();
        }
    }

    private function paymentMethod()
    {
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
