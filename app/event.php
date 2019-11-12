<?php
// 事件定义文件
return [
    //事件绑定
    'bind'      => [],

    //事件监听
    'listen'    => [
        'AppInit'  => [
            'app\\AppKernel'
        ],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    //定义注册事件订阅者
    'subscribe' => [
        'app\biz\user\event\UserEventSubscriber'
    ],
];
