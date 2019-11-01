<?php

namespace app\common;

class OrderToolkit
{
    /**
     * 去除不需要的日志
     *
     * @param [type] $orderLogs
     * @return void
     */
    public static function removeUnneededLogs($orderLogs)
    {
        $result = array();
        foreach ($orderLogs as $key => $value) {
            if ('order.success' != $value['status']) {
                $result[] = $value;
            }
        }

        return $result;
    }
}
