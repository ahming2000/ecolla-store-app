<?php

namespace App\Traits;

use DateTimeInterface;

trait FormatDateToSerialize
{
    /**
     * Override function to cast datetime to current timezone.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}
