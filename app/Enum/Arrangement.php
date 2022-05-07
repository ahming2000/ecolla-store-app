<?php

namespace App\Enum;

class Arrangement
{
    public static int $DESC = 0;
    public static int $ASC = 1;

    public static function getArrangement(int $type): string
    {
        return match ($type) {
            self::$ASC => 'ASC',
            default => 'DESC'
        };
    }
}
