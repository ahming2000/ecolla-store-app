<?php

namespace App\Enum;

class Language
{
    public static int $CH = 0;
    public static int $EN = 1;

    public static function getLabel(int $lang): string
    {
        return match ($lang) {
            self::$EN => 'en',
            default => 'ch',
        };
    }
}
