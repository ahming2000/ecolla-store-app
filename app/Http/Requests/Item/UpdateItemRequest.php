<?php

namespace App\Http\Requests\Item;

use App\Models\Category;
use App\Models\Item;
use App\Models\Origin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('item');

        return $item instanceof Item
            && $this->user()?->can('update', $item) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:255'],
            'name_en' => ['bail', 'nullable', 'string', 'max:255'],
            'desc' => ['bail', 'nullable', 'string'],
            'origin_id' => [
                'bail',
                'nullable',
                'integer',
                Rule::exists(Origin::class, 'id')->whereNull('deleted_at'),
            ],
            'category_ids' => ['present', 'array'],
            'category_ids.*' => [
                'bail',
                'integer',
                'distinct',
                Rule::exists(Category::class, 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Preserve the legacy behavior of assigning uncategorized when no
     * category is selected and removing it when specific categories exist.
     *
     * @return list<int>
     */
    public function categoryIds(): array
    {
        $categoryIds = array_values(array_map(
            static fn (int|string $categoryId): int => (int) $categoryId,
            $this->validated('category_ids', []),
        ));

        if ($categoryIds === []) {
            return [Category::DEFAULT_CATEGORY_ID];
        }

        if (count($categoryIds) === 1) {
            return $categoryIds;
        }

        return array_values(array_filter(
            $categoryIds,
            static fn (int $categoryId): bool => $categoryId !== Category::DEFAULT_CATEGORY_ID,
        ));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->trimmedInput('name'),
            'name_en' => $this->nullableTrimmedInput('name_en'),
            'desc' => $this->nullableTrimmedInput('desc'),
        ]);
    }

    private function trimmedInput(string $key): mixed
    {
        $value = $this->input($key);

        return is_string($value) ? trim($value) : $value;
    }

    private function nullableTrimmedInput(string $key): mixed
    {
        $value = $this->trimmedInput($key);

        return $value === '' ? null : $value;
    }
}
