<?php

namespace app\biz\scheduler;

use think\Service;
use app\biz\common\HTMLHelper;

class SchedulerServiceProvider extends Service
{
    public function register()
    {
        $this->app->bind('scheduler.options',  function () {
            return array(
                'max_process_exec_time' => 600,
                'max_num' => 10,
                'timeout' => 120,
                'max_retry_num' => 5,
            );
        });
    }
}
