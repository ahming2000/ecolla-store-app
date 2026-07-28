<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Origin;
use Illuminate\Database\Seeder;

class PredefinedCatalogSeeder extends Seeder
{
    /**
     * @var list<array{name: string, name_en: string}>
     */
    private const ORIGINS = [
        ['name' => '中国', 'name_en' => 'China'],
        ['name' => '台湾', 'name_en' => 'Taiwan'],
        ['name' => '日本', 'name_en' => 'Japan'],
        ['name' => '韩国', 'name_en' => 'Korea'],
        ['name' => '马来西亚', 'name_en' => 'Malaysia'],
        ['name' => '泰国', 'name_en' => 'Thailand'],
    ];

    /**
     * @var list<array{name: string, name_en: string}>
     */
    private const CATEGORIES = [
        ['name' => '未分类', 'name_en' => 'Uncategorized'],
        ['name' => '热卖', 'name_en' => 'Hot Selling'],
        ['name' => '新品', 'name_en' => 'New Product'],
        ['name' => '推荐', 'name_en' => 'Recommended'],
        ['name' => '包点与点心', 'name_en' => 'Buns'],
        ['name' => '果冻', 'name_en' => 'Jelly'],
        ['name' => '酸奶饮料', 'name_en' => 'Yogurt'],
        ['name' => '零食', 'name_en' => 'Snack'],
        ['name' => '酱料＆火锅底料', 'name_en' => 'Sauces & Hotpot Base'],
        ['name' => '火锅料', 'name_en' => 'Hotpot'],
        ['name' => '麦片', 'name_en' => 'Oatmeal'],
        ['name' => '腌制品', 'name_en' => 'Preserved food'],
        ['name' => '面类', 'name_en' => 'Noodles'],
        ['name' => '饮料', 'name_en' => 'Beverage'],
        ['name' => '酒类', 'name_en' => 'Alcohol'],
        ['name' => '冰淇淋', 'name_en' => 'Ice cream'],
        ['name' => '罐头', 'name_en' => 'Canned food'],
        ['name' => '糖果', 'name_en' => 'Candy'],
        ['name' => '用品', 'name_en' => 'Articles'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ORIGINS as $origin) {
            Origin::query()->create($origin);
        }

        foreach (self::CATEGORIES as $category) {
            Category::query()->create($category);
        }
    }
}
