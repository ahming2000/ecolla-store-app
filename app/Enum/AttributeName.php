<?php

namespace App\Enum;

class AttributeName
{
    public static int $CREATED_AT = 0;
    public static int $SOLD = 1;
    public static int $VIEW_COUNT = 2;
    public static int $NAME = 3;
    public static int $NAME_EN = 4;

    public static function all(): array
    {
        return [self::$CREATED_AT, self::$SOLD, self::$VIEW_COUNT, self::$NAME, self::$NAME_EN];
    }

    public static function getName(int $attribute): string
    {
        return match ($attribute) {
            self::$SOLD => 'sold',
            self::$VIEW_COUNT => 'view_count',
            self::$NAME => 'name',
            self::$NAME_EN => 'name_en',
            default => 'created_at'
        };
    }

    public static function getLabel(int $attribute, bool $isEn = false): string
    {
        if ($isEn) {
            return match ($attribute) {
                self::$SOLD => 'Sort By Sold Count',
                self::$VIEW_COUNT => 'Sort By View Count',
                self::$NAME => 'Sort By Name (Chinese)',
                self::$NAME_EN => 'Sort By Name (English)',
                default => 'Sort By Creation Time'
            };
        } else {
            return match ($attribute) {
                self::$SOLD => '以销售量编排',
                self::$VIEW_COUNT => '以查看次数编排',
                self::$NAME => '以名称编排',
                self::$NAME_EN => '以名称编排（英文）',
                default => '已创建时间编排'
            };
        }
    }
}
