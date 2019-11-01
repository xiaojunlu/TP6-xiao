<?php

namespace app\common;

class Converter
{
    public static function timestampToDate(&$timestamp, $format = 'c')
    {
        if ($timestamp) {
            $timestamp = date('Y-m-d H:i', $timestamp);
        } else {
            $timestamp = '0';
        }
    }
}
