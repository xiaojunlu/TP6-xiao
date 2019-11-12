<?php

namespace app\biz\user\event;

use think\Event;
use app\biz\EventSubscriber;

class UserEventSubscriber extends EventSubscriber
{
    //自定义订阅方式
    public function subscribe(Event $event)
    {
        $event->listen('user.login', [$this, 'onUserLogin']);
    }

    public function onUserLogin($user)
    {
        // UserLogin事件响应处理
        dump($user);
        echo "hello userLogin";
    }
}
