<?php

namespace App\Http\Resources;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use UnexpectedValueException;

abstract class AppJsonResource extends JsonResource
{
    private const FALLBACK_TIMEZONE = 'Asia/Kuala_Lumpur';

    /**
     * @param  array<string, mixed>|Arrayable<array-key, mixed>|JsonSerializable  $data
     * @return array<string, mixed>
     */
    protected function serializeDatesForUser(
        Request $request,
        array|Arrayable|JsonSerializable $data,
    ): array {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        if (! is_array($data)) {
            throw new UnexpectedValueException('Resource data must serialize to an array.');
        }

        if (! $this->resource instanceof Model) {
            return $data;
        }

        foreach ($this->resource->getDates() as $attribute) {
            if (! array_key_exists($attribute, $data)) {
                continue;
            }

            $data[$attribute] = $this->dateTimeForUser(
                $request,
                $this->resource->getAttribute($attribute),
            )?->toIso8601String();
        }

        return $data;
    }

    private function dateTimeForUser(
        Request $request,
        ?DateTimeInterface $dateTime,
    ): ?CarbonImmutable {
        if ($dateTime === null) {
            return null;
        }

        $timezone = $request->user()?->timezone;

        if (! $this->isValidTimezone($timezone)) {
            $timezone = self::FALLBACK_TIMEZONE;
        }

        return CarbonImmutable::instance($dateTime)->setTimezone($timezone);
    }

    private function isValidTimezone(mixed $timezone): bool
    {
        return is_string($timezone)
            && in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }
}
