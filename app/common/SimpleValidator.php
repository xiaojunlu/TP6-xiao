<?php

namespace app\common;

/**
 * 一个简单的验证类
 */
class SimpleValidator
{
    /**
     * 验证邮箱
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function email($value)
    {
        $value = (string)$value;
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL);
        return $valid !== false;
    }

    /**
     * 验证用户名 中、英文均可，最长18个英文或9个汉字
     *
     * @param [type] $value
     * @param array $option
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function nickname($value, array $option = array())
    {
        $option = array_merge(
            array('minLength' => 4, 'maxLength' => 18),
            $option
        );

        $len = (strlen($value) + mb_strlen($value, 'utf-8')) / 2;

        if ($len > $option['maxLength'] || $len < $option['minLength']) {
            return false;
        }

        if (preg_match('/^1\d{10}$/', $value)) {
            return false;
        }

        return !!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9_.·]+$/u', $value);
    }

    /**
     * 验证用户名 中、英文均可，最长18个英文或9个汉字
     *
     * @param [type] $value
     * @param array $option
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function username($value, array $option = array())
    {
        $option = array_merge(
            array('minLength' => 4, 'maxLength' => 18),
            $option
        );

        $len = (strlen($value) + mb_strlen($value, 'utf-8')) / 2;

        if ($len > $option['maxLength'] || $len < $option['minLength']) {
            return false;
        }

        if (preg_match('/^1\d{10}$/', $value)) {
            return false;
        }

        return !!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9_.·]+$/u', $value);
    }


    /**
     * 验证密码 5-20位英文、数字、符号，区分大小写
     *
     * @param [type] $value
     * @param array $option
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function password($value, array $option = array())
    {
        return !!preg_match('/^[\S]{5,20}$/u', $value);
    }

    //真实姓名改成和nickname一样 中、英文均可，最长18个英文或9个汉字
    public static function truename($value, array $option = array())
    {
        $option = array_merge(
            array('minLength' => 4, 'maxLength' => 18),
            $option
        );

        $len = (strlen($value) + mb_strlen($value, 'utf-8')) / 2;

        if ($len > $option['maxLength'] || $len < $option['minLength']) {
            return false;
        }

        if (preg_match('/^1\d{10}$/', $value)) {
            return false;
        }

        return !!preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z_.·]+$/u', $value);
    }

    /**
     * 验证身份证
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function idcard($value)
    {
        return !!preg_match('/^\d{17}[0-9xX]$/', $value);
    }

    /**
     * 验证银行卡号
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function bankCardId($value)
    {
        return !!preg_match('/^(\d{16}|\d{19})$/', $value);
    }

    /**
     * 验证手机
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function mobile($value)
    {
        return !!preg_match('/^1\d{10}$/', $value);
    }

    /**
     * 验证number
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function numbers($value)
    {
        return !!preg_match('/^(\d+,?)*\d+$/', $value);
    }

    public static function phone($value)
    {
        return !!preg_match('/^(\d{4}-|\d{3}-)?(\d{8}|\d{7})$/', $value);
    }

    /**
     * 验证日期
     *
     * @param [type] $value 时间戳
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function date($value)
    {
        return !!preg_match('/^(\d{4}|\d{2})-((0?([1-9]))|(1[0-2]))-((0?[1-9])|([12]([0-9]))|(3[0|1]))$/', $value);
    }

    /**
     * 验证qq
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function qq($value)
    {
        return !!preg_match('/^[1-9]\d{4,}$/', $value);
    }

    /**
     * 验证整数
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function integer($value)
    {
        return !!preg_match('/^[+-]?\d{1,9}$/', $value);
    }

    /**
     * 验证浮点数
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function float($value)
    {
        return !!preg_match('/^(([+-]?[1-9]{1}\d*)|([+-]?[0]{1}))(\.(\d){1,2})?$/i', $value);
    }

    /**
     * 验证时间 日期格式
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function dateTime($value)
    {
        return !!preg_match('/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/', $value);
    }

    /**
     * 验证网址
     *
     * @param [type] $value
     * @return void
     * @Description
     * @example
     * @author luxiaojun
     * @since
     */
    public static function site($value)
    {
        return !!preg_match('/^(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/', $value);
    }

    /**
     * 中文或者含有字母和数字的
     *
     * @param [type] $value
     * @return void
     */
    public static function chineseAndAlphanumeric($value)
    {
        return (bool)preg_match('/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9_.·])*$/u', $value);
    }

}