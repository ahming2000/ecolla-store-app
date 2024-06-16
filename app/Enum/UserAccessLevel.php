<?php

namespace App\Enum;

class UserAccessLevel
{
    /*
     * 0: can view items and orders
     * 1: can update items and orders, include update items listing setting
     * 2: can create items and update website settings
     * 3: admin
     */
    public static int $VIEW_ONLY = 0;
    public static int $UPDATE_ONLY = 1;
    public static int $UPDATE_WEBSITE_SETTINGS = 2;
    public static int $ADMIN = 3;
}
