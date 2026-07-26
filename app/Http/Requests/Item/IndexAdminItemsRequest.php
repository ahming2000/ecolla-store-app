<?php

namespace App\Http\Requests\Item;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAdminItemsRequest extends FormRequest
{
    private const DEFAULT_PER_PAGE = 50;

    private const PER_PAGE_OPTIONS = [50, 100, 150, 200];

    private const SORT_COLUMNS = [
        'created_at',
        'sold_count',
        'view_count',
        'name',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'category_ids' => ['array'],
            'category_ids.*' => [
                'bail',
                'integer',
                'distinct',
                Rule::exists(Category::class, 'id')->whereNull('deleted_at'),
            ],
            'out_of_stock' => ['boolean'],
            'not_listed' => ['boolean'],
            'sort_by' => ['required', Rule::in(self::SORT_COLUMNS)],
            'sort_direction' => ['required', Rule::in(['asc', 'desc'])],
            'page' => ['required', 'integer', 'min:1'],
            'per_page' => [
                'required',
                'integer',
                Rule::in(self::PER_PAGE_OPTIONS),
            ],
        ];
    }

    public function keyword(): ?string
    {
        $keyword = $this->validated('keyword');

        return is_string($keyword) ? $keyword : null;
    }

    /**
     * @return list<int>
     */
    public function categoryIds(): array
    {
        return array_values(array_map(
            static fn (int|string $categoryId): int => (int) $categoryId,
            $this->validated('category_ids', []),
        ));
    }

    public function outOfStock(): bool
    {
        return $this->boolean('out_of_stock');
    }

    public function notListed(): bool
    {
        return $this->boolean('not_listed');
    }

    public function sortBy(): string
    {
        return (string) $this->validated('sort_by');
    }

    public function sortDirection(): string
    {
        return (string) $this->validated('sort_direction');
    }

    public function page(): int
    {
        return (int) $this->validated('page');
    }

    public function perPage(): int
    {
        return (int) $this->validated('per_page');
    }

    protected function prepareForValidation(): void
    {
        $keyword = $this->input('keyword');
        $keyword = is_string($keyword) ? trim($keyword) : $keyword;

        $this->merge([
            'keyword' => $keyword === '' ? null : $keyword,
            'category_ids' => $this->input('category_ids', []),
            'out_of_stock' => $this->input('out_of_stock', false),
            'not_listed' => $this->input('not_listed', false),
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_direction' => $this->input('sort_direction', 'desc'),
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', self::DEFAULT_PER_PAGE),
        ]);
    }
}
