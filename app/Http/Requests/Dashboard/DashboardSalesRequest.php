<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardSalesRequest extends FormRequest
{
    private const FALLBACK_TIMEZONE = 'Asia/Kuala_Lumpur';

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'period' => ['required', Rule::enum(DashboardPeriod::class)],
            'date' => ['required', Rule::date()->format('Y-m-d')],
        ];
    }

    public function period(): DashboardPeriod
    {
        return DashboardPeriod::from($this->string('period')->toString());
    }

    public function selectedDate(): CarbonImmutable
    {
        return CarbonImmutable::parse(
            $this->string('date')->toString(),
            $this->timezone(),
        )->startOfDay();
    }

    public function timezone(): string
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'period' => $this->input('period', DashboardPeriod::DAILY->value),
            'date' => $this->input(
                'date',
                CarbonImmutable::now($this->timezone())->format('Y-m-d'),
            ),
        ]);
    }
}
