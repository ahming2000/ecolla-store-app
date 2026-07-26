<?php

namespace App\Rules\Item;

use App\Models\Item;
use App\Models\ItemVariation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CanListItemRule implements ValidationRule
{
    public function __construct(private readonly Item $item) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== true) {
            return;
        }

        $this->item->loadMissing(['origin', 'variations']);

        if (! $this->hasCompleteItemDetails() || ! $this->hasCompleteVariations()) {
            $fail(
                'An item can only be listed after its Chinese and English names, description, origin, and at least one variation with a barcode and Chinese and English names are complete.',
            );
        }
    }

    private function hasCompleteItemDetails(): bool
    {
        $origin = $this->item->origin;

        return filled($this->item->name)
            && filled($this->item->name_en)
            && filled($this->item->desc)
            && $origin !== null
            && filled($origin->name)
            && filled($origin->name_en);
    }

    private function hasCompleteVariations(): bool
    {
        return $this->item->variations->isNotEmpty()
            && $this->item->variations->every(
                fn (ItemVariation $variation): bool => filled($variation->barcode)
                    && filled($variation->name)
                    && filled($variation->name_en),
            );
    }
}
