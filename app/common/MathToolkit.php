<?php

namespace app\common;

/**
 * 数学计算工具
 *
 */

class MathToolkit
{
    /**
     * 乘法计算
     *
     * @param [type] $data  包含需处理的数据 如array('price_amount' => 100,'pay_amount' => 50)
     * @param [type] $fields 乘数1 如 array('price_amount', 'pay_amount')
     * @param [type] $multiplicator 乘数2 如100
     * @return void
     */
    public static function multiply($data, $fields, $multiplicator)
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] *= $multiplicator;
            }
        }

        return $data;
    }

    /**
     * 简单乘法计算
     *
     * @param [type] $number 乘数1
     * @param [type] $multiplicator 乘数2
     * @return void
     */
    public static function simple($number, $multiplicator)
    {
        return $number * $multiplicator;
    }

    /**
     * 等值判断 误差小于 0.00001
     *
     * @param [type] $number1
     * @param [type] $number2
     * @return boolean
     */
    public static function isEqual($number1, $number2)
    {
        return abs($number1 - $number2) < 0.00001;
    }

    public static function uniqid($prefix = 'ES')
    {
        return md5(uniqid($prefix, rand(0, 10000)));
    }
}
