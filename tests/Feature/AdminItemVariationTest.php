<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Models\Image;
use App\Models\Item;
use App\Models\ItemVariation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminItemVariationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_editor_can_create_a_valid_variation(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);

        $response = $this->actingAs($this->user(AccessLevel::EDITOR))
            ->postJson(
                route('admin.ajax.item.variation.store', $item),
                $this->validVariationData(),
            );

        $response
            ->assertCreated()
            ->assertJsonPath('item_id', $item->id)
            ->assertJsonPath('barcode', 'TEA-001')
            ->assertJsonPath('name', '茶包');

        $this->assertDatabaseHas('item_variations', [
            'item_id' => $item->id,
            'barcode' => 'TEA-001',
            'name' => '茶包',
            'name_en' => 'Tea bag',
            'price' => 12.5,
            'sale_price' => 10,
            'weight' => 0.25,
            'stock' => 8,
        ]);
    }

    public function test_editor_can_update_a_variation_without_changing_its_barcode(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $variation = $this->variation($item);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->putJson(
                route('admin.ajax.item.variation.update', [$item, $variation]),
                [
                    ...$this->validVariationData(),
                    'name' => '绿茶',
                    'name_en' => 'Green tea',
                ],
            )
            ->assertOk()
            ->assertJsonPath('id', $variation->id)
            ->assertJsonPath('barcode', $variation->barcode)
            ->assertJsonPath('name', '绿茶');

        $this->assertDatabaseHas('item_variations', [
            'id' => $variation->id,
            'barcode' => $variation->barcode,
            'name' => '绿茶',
            'name_en' => 'Green tea',
        ]);
    }

    public function test_editor_can_attach_and_replace_a_variation_photo(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $variation = $this->variation($item);
        $previousImage = Image::factory()->create();
        $replacementImage = Image::factory()->create();

        $variation->image()->associate($previousImage);
        $variation->save();

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->postJson(
                route('admin.ajax.item.variation.image.store', [
                    $item,
                    $variation,
                ]),
                ['image_id' => $replacementImage->id],
            )
            ->assertOk()
            ->assertJsonPath('id', $variation->id)
            ->assertJsonPath('image_id', $replacementImage->id)
            ->assertJsonPath('image.id', $replacementImage->id);

        $this->assertSame(
            $replacementImage->id,
            $variation->refresh()->image_id,
        );
        $this->assertModelMissing($previousImage);
        $this->assertModelExists($replacementImage);
    }

    public function test_replacing_a_shared_variation_photo_keeps_the_image(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $otherItem = Item::query()->create(['name' => 'Coffee']);
        $variation = $this->variation($item);
        $sharedImage = Image::factory()->create();
        $replacementImage = Image::factory()->create();

        $variation->image()->associate($sharedImage);
        $variation->save();
        $otherItem->images()->attach($sharedImage);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->postJson(
                route('admin.ajax.item.variation.image.store', [
                    $item,
                    $variation,
                ]),
                ['image_id' => $replacementImage->id],
            )
            ->assertOk();

        $this->assertModelExists($sharedImage);
        $this->assertTrue(
            $otherItem->images()->whereKey($sharedImage->id)->exists(),
        );
    }

    public function test_editor_can_remove_a_variation_photo(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $variation = $this->variation($item);
        $image = Image::factory()->create();

        $variation->image()->associate($image);
        $variation->save();

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->deleteJson(
                route('admin.ajax.item.variation.image.destroy', [
                    $item,
                    $variation,
                ]),
            )
            ->assertOk()
            ->assertJsonPath('id', $variation->id)
            ->assertJsonPath('image_id', null)
            ->assertJsonPath('image', null);

        $this->assertNull($variation->refresh()->image_id);
        $this->assertModelMissing($image);
    }

    public function test_variation_photo_routes_are_authorized_validated_and_scoped(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $otherItem = Item::query()->create(['name' => 'Coffee']);
        $variation = $this->variation($item);
        $image = Image::factory()->create();

        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->postJson(
                route('admin.ajax.item.variation.image.store', [
                    $item,
                    $variation,
                ]),
                ['image_id' => $image->id],
            )
            ->assertForbidden();
        $this->actingAs($this->user(AccessLevel::VIEWER))
            ->deleteJson(
                route('admin.ajax.item.variation.image.destroy', [
                    $item,
                    $variation,
                ]),
            )
            ->assertForbidden();

        Auth::logout();

        $this->postJson(
            route('admin.ajax.item.variation.image.store', [
                $item,
                $variation,
            ]),
            ['image_id' => $image->id],
        )->assertUnauthorized();
        $this->deleteJson(
            route('admin.ajax.item.variation.image.destroy', [
                $item,
                $variation,
            ]),
        )->assertUnauthorized();

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->postJson(
                route('admin.ajax.item.variation.image.store', [
                    $item,
                    $variation,
                ]),
                ['image_id' => 999_999],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image_id');

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->postJson(
                route('admin.ajax.item.variation.image.store', [
                    $otherItem,
                    $variation,
                ]),
                ['image_id' => $image->id],
            )
            ->assertNotFound();

        $this->assertNull($variation->refresh()->image_id);
    }

    public function test_barcode_must_be_unique_across_active_variations(): void
    {
        $firstItem = Item::query()->create(['name' => 'Tea']);
        $existingVariation = $this->variation($firstItem);
        $secondItem = Item::query()->create(['name' => 'Coffee']);
        $secondVariation = $this->variation($secondItem, [
            'barcode' => 'COFFEE-001',
        ]);
        $editor = $this->user(AccessLevel::EDITOR);

        $this->actingAs($editor)
            ->postJson(
                route('admin.ajax.item.variation.store', $secondItem),
                [
                    ...$this->validVariationData(),
                    'barcode' => $existingVariation->barcode,
                ],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('barcode');

        $this->actingAs($editor)
            ->putJson(
                route('admin.ajax.item.variation.update', [
                    $secondItem,
                    $secondVariation,
                ]),
                [
                    ...$this->validVariationData(),
                    'barcode' => $existingVariation->barcode,
                ],
            )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('barcode');
    }

    public function test_active_barcode_unique_index_rejects_duplicates(): void
    {
        $firstItem = Item::query()->create(['name' => 'Tea']);
        $this->variation($firstItem);
        $secondItem = Item::query()->create(['name' => 'Coffee']);

        $this->expectException(QueryException::class);

        $this->variation($secondItem);
    }

    public function test_barcode_can_be_reused_after_variation_is_soft_deleted(): void
    {
        $firstItem = Item::query()->create(['name' => 'Tea']);
        $deletedVariation = $this->variation($firstItem);
        $deletedVariation->delete();
        $secondItem = Item::query()->create(['name' => 'Coffee']);

        $activeVariation = $this->variation($secondItem);

        $this->assertSame($deletedVariation->barcode, $activeVariation->barcode);
        $this->assertSame(
            2,
            ItemVariation::withTrashed()
                ->where('barcode', $activeVariation->barcode)
                ->count(),
        );
        $this->assertSame(
            1,
            ItemVariation::query()
                ->where('barcode', $activeVariation->barcode)
                ->count(),
        );
    }

    public function test_variation_data_must_follow_the_legacy_save_rules(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $editor = $this->user(AccessLevel::EDITOR);
        $invalidInputs = [
            'barcode' => ['barcode' => '   '],
            'name' => ['name' => '   '],
            'name_en' => ['name_en' => '   '],
            'price' => ['price' => 0],
            'sale_price' => ['sale_price' => 12.51],
            'weight' => ['weight' => -0.001],
            'stock' => ['stock' => -1],
            'stock integer' => ['stock' => 1.5],
        ];

        foreach ($invalidInputs as $expectedField => $invalidInput) {
            $expectedField = str_contains($expectedField, ' ')
                ? str($expectedField)->before(' ')->toString()
                : $expectedField;

            $this->actingAs($editor)
                ->postJson(
                    route('admin.ajax.item.variation.store', $item),
                    [...$this->validVariationData(), ...$invalidInput],
                )
                ->assertUnprocessable()
                ->assertJsonValidationErrors($expectedField);
        }
    }

    public function test_variation_routes_require_an_editor(): void
    {
        $item = Item::query()->create(['name' => 'Tea']);
        $variation = $this->variation($item);
        $viewer = $this->user(AccessLevel::VIEWER);

        $this->actingAs($viewer)
            ->postJson(
                route('admin.ajax.item.variation.store', $item),
                $this->validVariationData(),
            )
            ->assertForbidden();
        $this->actingAs($viewer)
            ->putJson(
                route('admin.ajax.item.variation.update', [$item, $variation]),
                $this->validVariationData(),
            )
            ->assertForbidden();
        $this->actingAs($viewer)
            ->deleteJson(
                route('admin.ajax.item.variation.destroy', [$item, $variation]),
            )
            ->assertForbidden();

        Auth::logout();

        $this->postJson(
            route('admin.ajax.item.variation.store', $item),
            $this->validVariationData(),
        )->assertUnauthorized();
        $this->putJson(
            route('admin.ajax.item.variation.update', [$item, $variation]),
            $this->validVariationData(),
        )->assertUnauthorized();
        $this->deleteJson(
            route('admin.ajax.item.variation.destroy', [$item, $variation]),
        )->assertUnauthorized();
    }

    public function test_variation_must_belong_to_the_item_in_the_route(): void
    {
        $firstItem = Item::query()->create(['name' => 'Tea']);
        $secondItem = Item::query()->create(['name' => 'Coffee']);
        $variation = $this->variation($secondItem);
        $editor = $this->user(AccessLevel::EDITOR);

        $this->actingAs($editor)
            ->putJson(
                route('admin.ajax.item.variation.update', [
                    $firstItem,
                    $variation,
                ]),
                $this->validVariationData(),
            )
            ->assertNotFound();
        $this->actingAs($editor)
            ->deleteJson(
                route('admin.ajax.item.variation.destroy', [
                    $firstItem,
                    $variation,
                ]),
            )
            ->assertNotFound();
    }

    public function test_deleting_the_last_variation_unlists_the_item(): void
    {
        $item = Item::query()->create([
            'name' => 'Tea',
            'is_listed' => true,
        ]);
        $variation = $this->variation($item);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->deleteJson(
                route('admin.ajax.item.variation.destroy', [$item, $variation]),
            )
            ->assertNoContent();

        $this->assertSoftDeleted($variation);
        $this->assertFalse($item->refresh()->is_listed);
    }

    public function test_deleting_one_of_multiple_variations_keeps_the_item_listed(): void
    {
        $item = Item::query()->create([
            'name' => 'Tea',
            'is_listed' => true,
        ]);
        $firstVariation = $this->variation($item);
        $this->variation($item, ['barcode' => 'TEA-002']);

        $this->actingAs($this->user(AccessLevel::EDITOR))
            ->deleteJson(
                route('admin.ajax.item.variation.destroy', [
                    $item,
                    $firstVariation,
                ]),
            )
            ->assertNoContent();

        $this->assertTrue($item->refresh()->is_listed);
        $this->assertSame(1, $item->variations()->count());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function variation(
        Item $item,
        array $attributes = [],
    ): ItemVariation {
        return $item->variations()->create([
            ...$this->validVariationData(),
            ...$attributes,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validVariationData(): array
    {
        return [
            'barcode' => 'TEA-001',
            'name' => ' 茶包 ',
            'name_en' => ' Tea bag ',
            'price' => 12.5,
            'sale_price' => 10,
            'weight' => 0.25,
            'stock' => 8,
        ];
    }

    private function user(AccessLevel $accessLevel): User
    {
        return User::factory()->create([
            'access_level' => $accessLevel->value,
        ]);
    }
}
