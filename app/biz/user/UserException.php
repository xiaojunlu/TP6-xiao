<?php

namespace app\biz\user;

use app\common\exception\AbstractException;

class UserException extends AbstractException
{
    const EXCEPTION_MODUAL = 01; //模块

    const NOTFOUND_USER = 4040101;

    const UN_LOGIN = 4040102;

    const NOTFOUND_USERBIND = 4040103;

    const USERNAME_INVALID = 5000104;

    const USERNAME_EXISTED = 5000105;

    const CLIENT_TYPE_INVALID = 5000106;

    const LOGIN_FAIL = 5000107;

    const GENDER_INVALID = 5000108;

    const MOBILE_INVALID = 5000109;

    const LOCK_SELF_DENIED =  4030110;

    const LOCK_DENIED = 4030111;

    const PASSWORD_INVALID = 5000112;

    const MOBILE_EXISTED = 5000113;

    const ROLES_INVALID = 5000114;

    const EMAIL_INVALID = 5000115;

    const EMAIL_EXISTED = 5000116;

    const PASSWORD_ERROR = 5000117;

    const NOTFOUND_USER_PROFILE = 5000118;

    const APPROVAL_EXISTED = 5000119;

    const IDCARD_INVALID = 5000120;

    const TRUENAME_INVALID = 5000121;

    const EDU_APPROVAL_EXISTED = 5000122;

    const ERROR_MOBILE_REGISTERED = 4030123;

    const LOCKED_USER = 4030124;

    const NOTFOUND_TOKEN = 4040125;

    const PERMISSION_DENIED = 4030126;

    const MOBILE_BIND_UNFINISHED = 5000127;

    const APPROVAL_UNFINISHED = 5000128;

    const EDU_APPROVAL_UNFINISHED = 5000129;

    const UPDATE_USERNAME_ERROR = 5000130;

    public $messages = array(
        4040101 => '找不到该用户！',
        4040102 => '未登陆，请登陆后操作!',
        4040103 => '绑定的用户不存在，请重新绑定。',
        5000104 => '用户名格式错误',
        5000105 => '用户名已存在',
        5000106 => '第三方类型不正确，绑定失败',
        5000107 => '登录失败，请重试！',
        5000108 => '性别不正确',
        5000109 => '手机不正确，更新用户失败',
        4030110 => '您不能封禁自己',
        4030111 => '没有封禁该角色的权限',
        5000112 => '密码校验失败',
        5000113 => '手机号已被注册',
        5000114 => '角色不正确',
        5000115 => '邮箱地址不正确',
        5000116 => '邮箱地址已被注册',
        5000117 => '用户名密码错误',
        5000118 => '找不到该用户的基本资料！',
        5000119 => '该用户已经提交过实名认证了！',
        5000120 => '身份证号错误',
        5000121 => '真实姓名错误',
        5000122 => '该用户已经提交过学历认证了！',
        4030123 => '该手机号码已被注册！',
        4030124 => '账户被封禁，请联系管理员',
        4040125 => 'token不存在',
        4030126 => '暂无权限访问',
        5000127 => '未绑定手机号码',
        5000128 => '还没有完成实名认证',
        5000129 => '还没有完成学历认证',
        5000130 => '昵称修改异常',
    );
}
