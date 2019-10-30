<?php

namespace app\biz;

use think\Service;
use app\biz\common\HTMLHelper;

class DefaultServiceProvider extends Service
{
    // 系统服务注册的时候，执行register方法
    public function register()
    {
        // 将绑定标识到对应的类
        $this->app->bind('html_helper',  function () {
            return new HTMLHelper();
        });
    }

    // 系统服务注册之后，执行boot方法
    public function boot()
    { }
}
