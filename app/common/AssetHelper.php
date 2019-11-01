<?php

namespace app\common;

use think\Container;

class AssetHelper
{
    public static function getFurl($path, $defaultKey = false)
    {
        return get_file_url($path, $defaultKey);
    }

    public static function uriForPath($path)
    {
        return Container::get('request')->domain() . $path;
    }

    public static function getScheme()
    {
        return Container::get('request')->scheme();
    }
}
