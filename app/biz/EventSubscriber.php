<?php

namespace app\biz;

use app\biz\common\ServiceKernel;

class EventSubscriber
{
    protected function createService($alias)
    {
        return $this->getKernel()->createService($alias);
    }

    protected function createDao($alias)
    {
        return $this->getKernel()->createDao($alias);
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
