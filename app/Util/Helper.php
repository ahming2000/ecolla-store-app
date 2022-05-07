<?php

namespace App\Util;

class Helper
{
    public static function param(string $param, $default = "", bool $isNum = false)
    {
        if (empty($_GET[$param])) return $default;

        if ($isNum){
            return is_numeric($_GET[$param]) ? $_GET[$param] : $default;
        } else {
            return $_GET[$param];
        }
    }

    public static function paramSelected(string $param, $value): string
    {
        if (!empty($_GET[$param])){
            return $_GET[$param] == $value ? "selected" : "";
        } else {
            return "";
        }
    }
}
