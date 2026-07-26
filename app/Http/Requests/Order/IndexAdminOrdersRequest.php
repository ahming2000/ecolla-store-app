<?php

namespace App\Http\Requests\Order;

use App\Enums\DeliveryMode;
use App\Models\Order;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAdminOrdersRequest extends FormRequest
{
    private const DEFAULT_PER_PAGE = 50;

    private const FALLBACK_TIMEZONE = 'Asia/Kuala_Lumpur';

    private const PER_PAGE_OPTIONS = [50, 100, 150, 200];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Order::class) === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_date' => [
                'nullable',
                Rule::date()->format('Y-m-d'),
            ],
            'delivery_mode' => [
                'nullable',
                Rule::enum(DeliveryMode::class),
            ],
            'page' => ['required', 'integer', 'min:1'],
            'per_page' => [
                'required',
                'integer',
                Rule::in(self::PER_PAGE_OPTIONS),
            ],
        ];
    }

    public function orderDate(): ?CarbonImmutable
    {
        $orderDate = $this->validated('order_date');

        if (! is_string($orderDate)) {
            return null;
        }

        return CarbonImmutable::parse($orderDate, $this->timezone())
            ->startOfDay();
    }

    public function deliveryMode(): ?DeliveryMode
    {
        $deliveryMode = $this->validated('delivery_mode');

        return is_string($deliveryMode)
            ? DeliveryMode::from($deliveryMode)
            : null;
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
        $this->merge([
            'order_date' => $this->input('order_date') ?: null,
            'delivery_mode' => $this->input('delivery_mode') ?: null,
            'page' => $this->input('page', 1),
            'per_page' => $this->input(
                'per_page',
                self::DEFAULT_PER_PAGE,
            ),
        ]);
    }

    private function timezone(): string
    {
        $timezone = $this->user()?->timezone;

        if (
            ! is_string($timezone)
            || ! in_array($timezone, DateTimeZone::listIdentifiers(), true)
        ) {
            return self::FALLBACK_TIMEZONE;
        }

        return $timezone;
    }
}
