<?php

namespace App\Enums;

enum AccessLevel: int
{
    case VIEWER = 0;
    case EDITOR = 1;
    case SUPERVISOR = 2;
    case ADMIN = 3;

    /**
     * @return list<self>
     */
    public static function getAccessLevelOptions(): array
    {
        return [self::VIEWER, self::EDITOR, self::SUPERVISOR];
    }

    public static function getLabel(self $accessLevel, ?Language $language): string
    {
        if ($language == Language::EN) {
            return match ($accessLevel) {
                self::VIEWER => 'Viewer',
                self::EDITOR => 'Editor',
                self::SUPERVISOR => 'Supervisor',
                self::ADMIN => 'Admin',
            };
        } else {
            return match ($accessLevel) {
                self::VIEWER => '观察员',
                self::EDITOR => '編輯员',
                self::SUPERVISOR => '监督员',
                self::ADMIN => '管理员',
            };
        }
    }
}
