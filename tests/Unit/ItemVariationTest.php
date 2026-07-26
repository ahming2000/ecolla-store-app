<?php

namespace Tests\Unit;

use App\Models\ItemVariation;
use PHPUnit\Framework\TestCase;

class ItemVariationTest extends TestCase
{
    public function test_regular_price_is_used_when_there_is_no_sale_price(): void
    {
        $variation = new ItemVariation([
            'price' => 18.9,
            'sale_price' => null,
            'weight' => 0.25,
        ]);

        $this->assertSame('RM 18.90', $variation->price_text);
        $this->assertSame('', $variation->sale_price_text);
        $this->assertSame(18.9, $variation->final_price);
        $this->assertSame('RM 18.90', $variation->final_price_text);
        $this->assertSame('0.250 kg', $variation->weight_text);
    }

    public function test_sale_price_takes_precedence_over_regular_price(): void
    {
        $variation = new ItemVariation([
            'price' => 18.9,
            'sale_price' => 15.5,
        ]);

        $this->assertSame('RM 15.50', $variation->sale_price_text);
        $this->assertSame(15.5, $variation->final_price);
        $this->assertSame('RM 15.50', $variation->final_price_text);
    }

    public function test_zero_is_treated_as_a_valid_sale_price(): void
    {
        $variation = new ItemVariation([
            'price' => 18.9,
            'sale_price' => 0,
        ]);

        $this->assertSame(0.0, $variation->final_price);
        $this->assertSame('RM 0.00', $variation->final_price_text);
    }
}
