<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use App\Models\Order;
use App\Models\OrderedItem;
use App\Models\Origin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DevSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        parent::run();

        $this->seedUser();
        $this->seedOrigin();
        $this->seedCategory();
        $this->seedItem();
        $this->seedImage();
        $this->seedItemVariation();
        $this->seedItemImage();
        $this->seedOrder();
        $this->seedOrderedItem();
    }

    private function seedOrderedItem(): void
    {
        OrderedItem::factory(50)->create();
    }

    private function seedOrder(): void
    {
        Order::factory(10)->create();
    }

    private function seedItemVariation(): void
    {
        ItemVariation::factory(50)->create();

        $items = Item::all();

        foreach ($items as $item) {
            if (! empty($item->variations)) {
                Item::query()
                    ->where('id', '=', $item->id)
                    ->update(['is_listed' => true]);
            }
        }
    }

    private function seedItemImage(): void
    {
        $itemIds = Item::query()->pluck('id')->all();
        $imageIds = Image::query()->pluck('id')->all();

        foreach ($itemIds as $itemId) {
            $count = rand(3, 6); // Random count for adding multiple images to each item
            $added = [];

            for ($i = 0; $i < $count; $i++) {
                $imageId = $imageIds[rand() % count($imageIds)];

                // Make sure the category id will not duplicate
                while (true) {
                    $found = false;

                    foreach ($added as $a) {
                        if ($imageId == $a) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        $imageId = $imageIds[rand() % count($imageIds)];
                    } else {
                        break;
                    }
                }

                DB::table('item_images')->insert(['item_id' => $itemId, 'image_id' => $imageId]);
                $added[] = $imageId; // Add to a list for recording added category
            }
        }
    }

    private function seedItem(): void
    {
        Item::factory(10)->create();

        $itemIds = Item::query()->pluck('id')->all();
        $categoryIds = Category::query()->pluck('id')->all();

        foreach ($itemIds as $itemId) {
            $count = rand(1, 3); // Random count for adding multiple categories to each item
            $added = [];

            for ($i = 0; $i < $count; $i++) {
                $categoryId = $categoryIds[rand() % count($categoryIds)];

                // Make sure the category id will not duplicate
                while (true) {
                    $found = false;

                    foreach ($added as $a) {
                        if ($categoryId == $a) {
                            $found = true;
                            break;
                        }
                    }

                    if ($found) {
                        $categoryId = $categoryIds[rand() % count($categoryIds)];
                    } else {
                        break;
                    }
                }

                DB::table('item_categories')->insert(['item_id' => $itemId, 'category_id' => $categoryId]);
                $added[] = $categoryId; // Add to a list for recording added category
            }
        }
    }

    private function seedOrigin(): void
    {
        $names = ['中国', '台湾', '日本', '韩国', '马来西亚', '泰国'];
        $nameEns = ['China', 'Taiwan', 'Japan', 'Korea', 'Malaysia', 'Thailand'];

        for ($i = 0; $i < count($names); $i++) {
            $origin = new Origin(
                [
                    'name' => $names[$i],
                    'name_en' => $nameEns[$i],
                ]
            );

            $origin->save();
        }
    }

    private function seedUser(): void
    {
        User::factory(10)->create();
    }

    private function seedCategory(): void
    {
        $names = ['未分类', '热卖', '新品', '推荐', '包点与点心', '果冻', '酸奶饮料', '零食', '酱料＆火锅底料', '火锅料', '麦片', '腌制品', '面类', '饮料', '酒类', '冰淇淋', '罐头', '糖果', '用品'];
        $nameEns = ['Uncategorized', 'Hot Selling', 'New Product', 'Recommended', 'Buns', 'Jelly', 'Yogurt', 'Snack', 'Sauces & Hotpot Base', 'Hotpot', 'Oatmeal', 'Preserved food', 'Noodles', 'Beverage', 'Alcohol', 'Ice cream', 'Canned food', 'Candy', 'Articles'];

        for ($i = 0; $i < count($names); $i++) {
            $category = new Category(
                [
                    'name' => $names[$i],
                    'name_en' => $nameEns[$i],
                ]
            );
            $category->save();
        }
    }

    private function seedImage(): void
    {
        Image::factory(100)->create();
    }
}
