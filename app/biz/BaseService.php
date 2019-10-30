<?php

namespace app\biz;

use app\biz\common\ServiceKernel;

abstract class BaseService
{
    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('user.UserService');
    }

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
