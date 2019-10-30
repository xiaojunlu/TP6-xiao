<?php

/**
 * Redis配置
 */
return [
    'cache_enabled' => false,
    'options'       => array(
        //地址
        'host'              => '127.0.0.1:6379',
        //密码
        'password'          => '',
        //持久连接
        'pconnect'          => true,
        //超时
        'timeout'           => 1,
        'reserved'          => null,
        //连接断开后每隔几秒后重试
        'retry_interval'    => 100,
        //key前缀
        'key_prefix' => '',
    )
];
