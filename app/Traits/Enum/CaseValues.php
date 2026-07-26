<?php

namespace App\Traits\Enum;

use BackedEnum;

trait CaseValues
{
    /**
     * @return list<string>
     */
    public static function caseValues(): array
    {
        return array_map(
            static fn (BackedEnum $case): string => (string) $case->value,
            static::cases(),
        );
    }
}
