<?php

namespace app\biz\scheduler\job;

use app\biz\scheduler\AbstractJob;

class MarkExecutingTimeoutJob extends AbstractJob
{
    public function execute()
    {
        $this->getSchedulerService()->markTimeoutJobs();
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('scheduler.SchedulerService');
    }
}
