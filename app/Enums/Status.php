<?php

namespace App\Enums;

enum Status: string
{
    case PENDING = '处理中';
    case READY = '准备就绪';
    case COMPLETED = '已完成';
    case REFUNDED = '已退款';
    case CANCELED = '已取消';

    public static function getLabel(self $status, ?Language $language): string
    {
        if ($language == Language::EN) {
            return match ($status) {
                self::PENDING => 'Pending',
                self::READY => 'Ready',
                self::COMPLETED => 'Completed',
                self::REFUNDED => 'Refunded',
                self::CANCELED => 'Canceled',
            };
        } else {
            return $status->value;
        }
    }
}
