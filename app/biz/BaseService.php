<?php

namespace app\biz;

use think\facade\Db;
use think\facade\Event;
use app\biz\common\ServiceKernel;
use app\common\exception\AbstractException;

abstract class BaseService
{
    /**
     * @return CurrentUser
     */
    public function getCurrentUser()
    {
        return app('user');
    }

    /**
     * 触发事件
     *
     * @param [type] $eventName  事件标识
     * @param array $arguments   参数
     * @return void
     */
    protected function dispatchEvent($eventName, $arguments = array())
    {
        Event::trigger($eventName, $arguments);
    }

    /**
     * HTML 过滤器
     * @param [type] $html
     * @param boolean $trusted 是否信任
     * @return HTMLHelper
     */
    protected function purifyHtml($html, $trusted = false)
    {
        $htmlHelper =  app('html_helper');

        return $htmlHelper->purify($html, $trusted);
    }

    /**
     * 开启事务
     * @return void
     */
    protected function beginTransaction()
    {
        Db::startTrans();
    }

    /**
     * 事务提交
     * @return void
     */
    protected function commit()
    {
        Db::commit();
    }

    /**
     * 事务回滚
     * @return void
     */
    protected function rollback()
    {
        Db::rollback();
    }

    /**
     * @return Lock
     */
    protected function getLock()
    {
        return app('lock');
    }

    /**
     * 抛出异常
     *
     * @param [type] $e
     * @return void
     */
    protected function createException($e)
    {
        if ($e instanceof AbstractException) {
            throw $e;
        }

        throw new \Exception();
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
