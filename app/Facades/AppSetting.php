<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AppSetting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'app_setting';
    }
}
