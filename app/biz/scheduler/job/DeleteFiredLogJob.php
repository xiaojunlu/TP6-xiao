<?php

namespace app\biz\scheduler\job;

use app\biz\scheduler\AbstractJob;

class CloseExpiredOrdersJob extends AbstractJob
{
    public function execute()
    {
        $keepDays = 15; //biz_scheduler_job_fired 只保留15天的日志
        $this->getSchedulerService()->deleteUnacquiredJobFired($keepDays);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('scheduler.SchedulerService');
    }
}
