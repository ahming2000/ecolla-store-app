<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Origin;
use Database\Seeders\ProdSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProdSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_production_seeder_includes_predefined_origins_and_categories(): void
    {
        $this->seed(ProdSeeder::class);

        $this->assertSame(
            ['China', 'Japan', 'Korea', 'Malaysia', 'Taiwan', 'Thailand'],
            Origin::query()->orderBy('name_en')->pluck('name_en')->all(),
        );
        $this->assertSame(19, Category::query()->count());
        $this->assertSame(
            '未分类',
            Category::query()
                ->where('name_en', 'Uncategorized')
                ->value('name'),
        );
        $this->assertSame(
            ['Alcohol', 'Beverage', 'Candy', 'Snack'],
            Category::query()
                ->whereIn('name_en', [
                    'Alcohol',
                    'Beverage',
                    'Candy',
                    'Snack',
                ])
                ->orderBy('name_en')
                ->pluck('name_en')
                ->all(),
        );
    }
}
