<?php

namespace App\Rules\Variation;

use App\Models\ItemVariation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidItemVariationRule implements ValidationRule
{
    public function __construct(
        private readonly ?ItemVariation $ignoredVariation = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = ItemVariation::query()->where('barcode', $value);

        if ($this->ignoredVariation !== null) {
            $query->whereKeyNot($this->ignoredVariation->getKey());
        }

        if ($query->exists()) {
            $fail('validation.unique')->translate();
        }
    }
}
